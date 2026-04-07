<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\Account;
use DoubleE\Models\AuditLog;

class InvoiceService
{
    private Database $db;
    private JournalEntryService $journalEntryService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->journalEntryService = new JournalEntryService();
    }

    /**
     * Create a new invoice or bill with lines in a single transaction.
     *
     * Steps performed atomically:
     * 1. Generate sequential document_number based on type
     * 2. Calculate line totals, subtotal, tax, total, balance_due
     * 3. INSERT invoice header
     * 4. INSERT invoice lines via bulk insert
     * 5. Write audit log
     *
     * @param array $data  Invoice header data (document_type, contact_id, issue_date, due_date, etc.)
     * @param array $lines Array of line items (description, account_id, quantity, unit_price, tax_amount)
     * @param int   $userId ID of the user creating the invoice
     *
     * @return int The newly created invoice ID
     */
    public function create(array $data, array $lines, int $userId): int
    {
        $lines = $this->calculateLineTotals($lines);

        $invoiceId = (int) $this->db->transaction(function () use ($data, $lines, $userId) {
            $type = $data['document_type'] ?? 'invoice';

            // Generate the next sequential document number
            $documentNumber = $this->getNextNumber($type);

            // Calculate totals from lines
            bcscale(2);
            $subtotal = '0.00';
            $taxAmount = '0.00';

            foreach ($lines as $line) {
                $subtotal = bcadd($subtotal, $line['line_total']);
                $taxAmount = bcadd($taxAmount, $line['tax_amount'] ?? '0.00');
            }

            $total = bcadd($subtotal, $taxAmount);
            $now = date('Y-m-d H:i:s');

            // Insert the invoice header
            $sql = "INSERT INTO invoices
                        (document_type, document_number, contact_id, issue_date, due_date,
                         subtotal, tax_amount, total, amount_paid, balance_due,
                         currency_code, status, terms, notes, ar_ap_account_id,
                         reference, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, 'draft', ?, ?, ?, ?, ?, ?)";

            $this->db->exec($sql, [
                $type,
                $documentNumber,
                (int) $data['contact_id'],
                $data['issue_date'],
                $data['due_date'],
                $subtotal,
                $taxAmount,
                $total,
                $total, // balance_due = total initially
                $data['currency_code'] ?? 'USD',
                $data['terms'] ?? null,
                $data['notes'] ?? null,
                (int) $data['ar_ap_account_id'],
                $data['reference'] ?? null,
                $userId,
                $now,
            ]);

            $invoiceId = $this->db->lastInsertId();

            // Bulk insert invoice lines
            $lineSql = "INSERT INTO invoice_lines
                            (invoice_id, description, account_id, quantity, unit_price,
                             tax_amount, line_total, line_order, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            foreach ($lines as $index => $line) {
                $this->db->exec($lineSql, [
                    $invoiceId,
                    $line['description'] ?? '',
                    (int) $line['account_id'],
                    $line['quantity'] ?? '1.0000',
                    $line['unit_price'] ?? '0.0000',
                    $line['tax_amount'] ?? '0.00',
                    $line['line_total'],
                    $line['line_order'] ?? $index,
                    $now,
                ]);
            }

            return $invoiceId;
        });

        AuditLog::log(
            'invoice.created',
            'invoice',
            $invoiceId,
            null,
            [
                'document_type' => $data['document_type'] ?? 'invoice',
                'contact_id'    => $data['contact_id'],
                'lines_count'   => count($lines),
            ]
        );

        return $invoiceId;
    }

    /**
     * Post a draft invoice, creating the corresponding journal entry.
     *
     * For invoices (AR):
     * - Debit:  AR account (ar_ap_account_id) for the full total
     * - Credit: each line's revenue/expense account for line_total
     * - Credit: tax liability account for tax_amount (if any)
     *
     * For bills (AP):
     * - Credit: AP account (ar_ap_account_id) for the full total
     * - Debit:  each line's expense/asset account for line_total
     * - Debit:  tax asset account for tax_amount (if any)
     *
     * @throws \RuntimeException If the invoice is not in draft status
     */
    public function post(int $invoiceId, int $userId): void
    {
        $journalEntryId = (int) $this->db->transaction(function () use ($invoiceId, $userId) {
            // Load invoice with lines
            $invoice = $this->getWithLines($invoiceId);
            if ($invoice === null) {
                throw new \RuntimeException("Invoice #{$invoiceId} not found.");
            }

            if ($invoice['status'] !== 'draft') {
                throw new \RuntimeException(
                    "Only draft invoices can be posted. Current status: {$invoice['status']}."
                );
            }

            $isAR = in_array($invoice['document_type'], ['invoice', 'credit_note'], true);
            $lines = $invoice['lines'];

            // Build journal entry lines
            $journalLines = [];
            $lineNumber = 1;

            if ($isAR) {
                // AR: Debit the receivable account for the total
                $journalLines[] = [
                    'account_id'  => (int) $invoice['ar_ap_account_id'],
                    'debit'       => $invoice['total'],
                    'credit'      => '0.00',
                    'description' => "AR - {$invoice['document_number']}",
                ];

                // Credit each line's revenue account
                foreach ($lines as $line) {
                    $journalLines[] = [
                        'account_id'  => (int) $line['account_id'],
                        'debit'       => '0.00',
                        'credit'      => $line['line_total'],
                        'description' => $line['description'] ?? '',
                    ];
                }

                // Credit tax liability if there is tax
                $taxAmount = (string) $invoice['tax_amount'];
                if (bccomp($taxAmount, '0.00', 2) > 0) {
                    $taxAccount = $this->findTaxLiabilityAccount();
                    $journalLines[] = [
                        'account_id'  => (int) $taxAccount['id'],
                        'debit'       => '0.00',
                        'credit'      => $taxAmount,
                        'description' => "Tax - {$invoice['document_number']}",
                    ];
                }
            } else {
                // AP: Credit the payable account for the total
                $journalLines[] = [
                    'account_id'  => (int) $invoice['ar_ap_account_id'],
                    'debit'       => '0.00',
                    'credit'      => $invoice['total'],
                    'description' => "AP - {$invoice['document_number']}",
                ];

                // Debit each line's expense/asset account
                foreach ($lines as $line) {
                    $journalLines[] = [
                        'account_id'  => (int) $line['account_id'],
                        'debit'       => $line['line_total'],
                        'credit'      => '0.00',
                        'description' => $line['description'] ?? '',
                    ];
                }

                // Debit tax asset if there is tax
                $taxAmount = (string) $invoice['tax_amount'];
                if (bccomp($taxAmount, '0.00', 2) > 0) {
                    $taxAccount = $this->findTaxAssetAccount();
                    $journalLines[] = [
                        'account_id'  => (int) $taxAccount['id'],
                        'debit'       => $taxAmount,
                        'credit'      => '0.00',
                        'description' => "Tax - {$invoice['document_number']}",
                    ];
                }
            }

            // Create and post the journal entry atomically
            $journalEntryId = $this->journalEntryService->createAndPost(
                [
                    'entry_date'  => $invoice['issue_date'],
                    'description' => "{$invoice['document_type']} {$invoice['document_number']}",
                    'reference'   => $invoice['document_number'],
                ],
                $journalLines,
                $userId
            );

            // Update invoice with journal entry link and posted status
            $newStatus = ($invoice['document_type'] === 'bill') ? 'posted' : 'sent';
            $this->db->exec(
                "UPDATE invoices SET journal_entry_id = ?, status = ?, updated_at = NOW() WHERE id = ?",
                [$journalEntryId, $newStatus, $invoiceId]
            );

            return $journalEntryId;
        });

        AuditLog::log(
            'invoice.posted',
            'invoice',
            $invoiceId,
            ['status' => 'draft'],
            ['status' => 'posted', 'journal_entry_id' => $journalEntryId]
        );
    }

    /**
     * Void an invoice, reversing its journal entry if one exists.
     *
     * @throws \RuntimeException If the invoice is already voided
     */
    public function void(int $invoiceId, int $userId, string $reason): void
    {
        $this->db->transaction(function () use ($invoiceId, $userId, $reason) {
            $invoice = $this->getWithLines($invoiceId);
            if ($invoice === null) {
                throw new \RuntimeException("Invoice #{$invoiceId} not found.");
            }

            if ($invoice['status'] === 'voided') {
                throw new \RuntimeException('This invoice has already been voided.');
            }

            // If a journal entry was created, void it (creates a reversing entry)
            if (!empty($invoice['journal_entry_id'])) {
                $this->journalEntryService->void(
                    (int) $invoice['journal_entry_id'],
                    $userId,
                    "Void {$invoice['document_number']}: {$reason}"
                );
            }

            // Mark invoice as voided
            $this->db->exec(
                "UPDATE invoices SET status = 'voided', updated_at = NOW() WHERE id = ?",
                [$invoiceId]
            );
        });

        AuditLog::log(
            'invoice.voided',
            'invoice',
            $invoiceId,
            ['status' => 'active'],
            ['status' => 'voided', 'void_reason' => $reason, 'voided_by' => $userId]
        );
    }

    /**
     * Calculate line totals for each invoice line.
     * line_total = round(quantity * unit_price, 2)
     *
     * @param array $lines Raw line data from the form
     * @return array Lines with calculated line_total values
     */
    public function calculateLineTotals(array $lines): array
    {
        bcscale(4); // Higher precision for intermediate calculation

        foreach ($lines as &$line) {
            $quantity  = (string) ($line['quantity'] ?? '1.0000');
            $unitPrice = (string) ($line['unit_price'] ?? '0.0000');

            $rawTotal = bcmul($quantity, $unitPrice, 4);
            $line['line_total'] = number_format((float) $rawTotal, 2, '.', '');
        }
        unset($line);

        return $lines;
    }

    /**
     * Get accounts receivable / payable aging report.
     *
     * Returns aging buckets: Current (not yet due), 1-30 days, 31-60, 61-90, 90+ days.
     * Each bucket contains its invoices and a bucket total.
     * A grand total is also returned.
     *
     * @param string $type 'invoice' for AR aging, 'bill' for AP aging
     * @return array Aging report with buckets and totals
     */
    public function getAgingReport(string $type = 'invoice'): array
    {
        $today = date('Y-m-d');

        $sql = "SELECT i.*, c.display_name AS contact_name
                FROM invoices i
                INNER JOIN contacts c ON c.id = i.contact_id
                WHERE i.document_type = ?
                  AND i.status IN ('sent', 'posted', 'partial', 'overdue')
                  AND i.balance_due > 0
                ORDER BY i.due_date ASC";

        $invoices = $this->db->query($sql, [$type]);

        $buckets = [
            'current' => ['label' => 'Current',    'invoices' => [], 'total' => '0.00'],
            '1_30'    => ['label' => '1-30 Days',   'invoices' => [], 'total' => '0.00'],
            '31_60'   => ['label' => '31-60 Days',  'invoices' => [], 'total' => '0.00'],
            '61_90'   => ['label' => '61-90 Days',  'invoices' => [], 'total' => '0.00'],
            '90_plus' => ['label' => '90+ Days',    'invoices' => [], 'total' => '0.00'],
        ];

        bcscale(2);
        $grandTotal = '0.00';

        foreach ($invoices as $invoice) {
            $dueDate = $invoice['due_date'];
            $daysOverdue = (int) ((strtotime($today) - strtotime($dueDate)) / 86400);
            $balance = (string) $invoice['balance_due'];

            if ($daysOverdue <= 0) {
                $bucket = 'current';
            } elseif ($daysOverdue <= 30) {
                $bucket = '1_30';
            } elseif ($daysOverdue <= 60) {
                $bucket = '31_60';
            } elseif ($daysOverdue <= 90) {
                $bucket = '61_90';
            } else {
                $bucket = '90_plus';
            }

            $invoice['days_overdue'] = max(0, $daysOverdue);
            $buckets[$bucket]['invoices'][] = $invoice;
            $buckets[$bucket]['total'] = bcadd($buckets[$bucket]['total'], $balance);
            $grandTotal = bcadd($grandTotal, $balance);
        }

        return [
            'type'        => $type,
            'as_of_date'  => $today,
            'buckets'     => $buckets,
            'grand_total' => $grandTotal,
        ];
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    /**
     * Load an invoice with its lines.
     */
    private function getWithLines(int $invoiceId): ?array
    {
        $invoice = $this->db->queryOne("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        if ($invoice === null) {
            return null;
        }

        $invoice['lines'] = $this->db->query(
            "SELECT * FROM invoice_lines WHERE invoice_id = ? ORDER BY line_order, id",
            [$invoiceId]
        );

        return $invoice;
    }

    /**
     * Generate the next sequential document number.
     * Invoices: INV-000001, Bills: BILL-000001, Credit notes: CN-000001
     */
    private function getNextNumber(string $type): string
    {
        $prefixes = [
            'invoice'     => 'INV',
            'bill'        => 'BILL',
            'credit_note' => 'CN',
        ];

        $prefix = $prefixes[$type] ?? 'INV';

        $sql = "SELECT MAX(document_number) FROM invoices WHERE document_type = ?";
        $max = $this->db->queryScalar($sql, [$type]);

        if ($max === null || $max === false) {
            return $prefix . '-000001';
        }

        // Extract the numeric portion after the prefix and dash
        $numeric = (int) substr((string) $max, strlen($prefix) + 1);
        $next = $numeric + 1;

        return $prefix . '-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Find the tax liability account (for AR invoices).
     * Looks for an account with "tax" and "payable" or "liability" in the name.
     *
     * @throws \RuntimeException If no tax liability account is configured
     */
    private function findTaxLiabilityAccount(): array
    {
        $sql = "SELECT a.* FROM accounts a
                INNER JOIN account_types at ON at.id = a.account_type_id
                WHERE at.name = 'Liability'
                  AND (LOWER(a.name) LIKE '%tax%payable%' OR LOWER(a.name) LIKE '%sales tax%')
                  AND a.is_active = 1
                LIMIT 1";

        $account = $this->db->queryOne($sql);
        if ($account === null) {
            throw new \RuntimeException(
                'No tax liability account found. Please configure a tax payable account.'
            );
        }

        return $account;
    }

    /**
     * Find the tax asset account (for AP bills).
     * Looks for an account with "tax" and "receivable" or "input" in the name.
     *
     * @throws \RuntimeException If no tax asset account is configured
     */
    private function findTaxAssetAccount(): array
    {
        $sql = "SELECT a.* FROM accounts a
                INNER JOIN account_types at ON at.id = a.account_type_id
                WHERE at.name = 'Asset'
                  AND (LOWER(a.name) LIKE '%tax%receivable%' OR LOWER(a.name) LIKE '%input tax%')
                  AND a.is_active = 1
                LIMIT 1";

        $account = $this->db->queryOne($sql);
        if ($account === null) {
            throw new \RuntimeException(
                'No tax asset account found. Please configure a tax receivable account.'
            );
        }

        return $account;
    }
}
