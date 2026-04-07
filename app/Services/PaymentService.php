<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\AuditLog;

class PaymentService
{
    private Database $db;
    private JournalEntryService $journalEntryService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->journalEntryService = new JournalEntryService();
    }

    /**
     * Create a payment, allocate it to invoices, and post the journal entry.
     *
     * Steps performed atomically:
     * 1. Generate sequential payment_number
     * 2. INSERT payment record
     * 3. INSERT allocations and update each invoice's payment status
     * 4. Build and post journal entry (debit/credit depends on payment type)
     * 5. Link journal_entry_id to payment, mark as posted
     *
     * For received payments (AR):
     * - Debit:  deposit_account_id (bank/cash) for the full amount
     * - Credit: AR account for the full amount
     *
     * For made payments (AP):
     * - Credit: deposit_account_id (bank/cash) for the full amount
     * - Debit:  AP account for the full amount
     *
     * @param array $data        Payment header (contact_id, type, payment_date, amount, payment_method, etc.)
     * @param array $allocations Array of ['invoice_id' => int, 'amount' => string]
     * @param int   $userId      ID of the user creating the payment
     *
     * @return int The newly created payment ID
     */
    public function create(array $data, array $allocations, int $userId): int
    {
        $paymentId = (int) $this->db->transaction(function () use ($data, $allocations, $userId) {
            $paymentNumber = $this->getNextNumber();
            $now = date('Y-m-d H:i:s');

            // Insert the payment record
            $sql = "INSERT INTO payments
                        (payment_number, contact_id, type, payment_date, amount,
                         payment_method, reference, deposit_account_id, notes,
                         status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)";

            $this->db->exec($sql, [
                $paymentNumber,
                (int) $data['contact_id'],
                $data['type'], // 'received' or 'made'
                $data['payment_date'],
                $data['amount'],
                $data['payment_method'],
                $data['reference'] ?? null,
                (int) $data['deposit_account_id'],
                $data['notes'] ?? null,
                $userId,
                $now,
            ]);

            $paymentId = $this->db->lastInsertId();

            // Insert allocations and update invoice balances
            $arApAccountId = null;
            foreach ($allocations as $allocation) {
                $invoiceId = (int) $allocation['invoice_id'];
                $allocAmount = (string) $allocation['amount'];

                // Insert the allocation record
                $this->db->exec(
                    "INSERT INTO payment_allocations (payment_id, invoice_id, amount, created_at)
                     VALUES (?, ?, ?, ?)",
                    [$paymentId, $invoiceId, $allocAmount, $now]
                );

                // Update the invoice's amount_paid and balance_due, then derive status
                $this->updateInvoicePaymentStatus($invoiceId, $allocAmount);

                // Capture the AR/AP account from the first allocated invoice
                if ($arApAccountId === null) {
                    $invoice = $this->db->queryOne("SELECT ar_ap_account_id FROM invoices WHERE id = ?", [$invoiceId]);
                    $arApAccountId = (int) $invoice['ar_ap_account_id'];
                }
            }

            // Build journal entry lines
            $journalLines = [];
            $type = $data['type'];
            $amount = (string) $data['amount'];

            if ($type === 'received') {
                // Debit bank/cash, credit AR
                $journalLines[] = [
                    'account_id'  => (int) $data['deposit_account_id'],
                    'debit'       => $amount,
                    'credit'      => '0.00',
                    'description' => "Payment received - {$paymentNumber}",
                ];
                $journalLines[] = [
                    'account_id'  => $arApAccountId,
                    'debit'       => '0.00',
                    'credit'      => $amount,
                    'description' => "AR payment - {$paymentNumber}",
                ];
            } else {
                // Credit bank/cash, debit AP
                $journalLines[] = [
                    'account_id'  => $arApAccountId,
                    'debit'       => $amount,
                    'credit'      => '0.00',
                    'description' => "AP payment - {$paymentNumber}",
                ];
                $journalLines[] = [
                    'account_id'  => (int) $data['deposit_account_id'],
                    'debit'       => '0.00',
                    'credit'      => $amount,
                    'description' => "Payment made - {$paymentNumber}",
                ];
            }

            // Create and post the journal entry
            $journalEntryId = $this->journalEntryService->createAndPost(
                [
                    'entry_date'  => $data['payment_date'],
                    'description' => "Payment {$paymentNumber}",
                    'reference'   => $paymentNumber,
                ],
                $journalLines,
                $userId
            );

            // Link journal entry and mark payment as posted
            $this->db->exec(
                "UPDATE payments SET journal_entry_id = ?, status = 'posted', updated_at = NOW() WHERE id = ?",
                [$journalEntryId, $paymentId]
            );

            return $paymentId;
        });

        AuditLog::log(
            'payment.created',
            'payment',
            $paymentId,
            null,
            [
                'type'             => $data['type'],
                'amount'           => $data['amount'],
                'contact_id'       => $data['contact_id'],
                'allocations_count' => count($allocations),
            ]
        );

        return $paymentId;
    }

    /**
     * Void a payment, reversing all invoice allocations and the journal entry.
     *
     * Steps:
     * 1. Load payment with its allocations
     * 2. Reverse each allocation (subtract from invoice, recalculate status)
     * 3. Void the journal entry (creates reversing entry)
     * 4. Mark payment as voided
     *
     * @throws \RuntimeException If the payment is not found or already voided
     */
    public function void(int $paymentId, int $userId): void
    {
        $this->db->transaction(function () use ($paymentId, $userId) {
            $payment = $this->db->queryOne("SELECT * FROM payments WHERE id = ?", [$paymentId]);
            if ($payment === null) {
                throw new \RuntimeException("Payment #{$paymentId} not found.");
            }

            if ($payment['status'] === 'voided') {
                throw new \RuntimeException('This payment has already been voided.');
            }

            // Load allocations
            $allocations = $this->db->query(
                "SELECT * FROM payment_allocations WHERE payment_id = ? ORDER BY id",
                [$paymentId]
            );

            // Reverse each allocation
            bcscale(2);
            foreach ($allocations as $allocation) {
                $invoiceId = (int) $allocation['invoice_id'];
                $allocAmount = (string) $allocation['amount'];

                // Subtract the allocation amount (negative adjustment)
                $negativeAmount = bcmul($allocAmount, '-1');
                $this->updateInvoicePaymentStatus($invoiceId, $negativeAmount);
            }

            // Void the journal entry if one exists
            if (!empty($payment['journal_entry_id'])) {
                $this->journalEntryService->void(
                    (int) $payment['journal_entry_id'],
                    $userId,
                    "Void payment {$payment['payment_number']}"
                );
            }

            // Mark payment as voided
            $this->db->exec(
                "UPDATE payments SET status = 'voided', updated_at = NOW() WHERE id = ?",
                [$paymentId]
            );
        });

        AuditLog::log(
            'payment.voided',
            'payment',
            $paymentId,
            ['status' => 'posted'],
            ['status' => 'voided', 'voided_by' => $userId]
        );
    }

    /**
     * Get unpaid invoices for a contact, for use in the payment allocation form.
     *
     * @param int    $contactId The contact to filter by
     * @param string $type      'invoice' for AR (received payments), 'bill' for AP (made payments)
     *
     * @return array List of invoices with balance_due > 0
     */
    public function getUnpaidInvoices(int $contactId, string $type): array
    {
        $sql = "SELECT i.id, i.document_number, i.issue_date, i.due_date,
                       i.total, i.amount_paid, i.balance_due, i.currency_code
                FROM invoices i
                WHERE i.contact_id = ?
                  AND i.document_type = ?
                  AND i.balance_due > 0
                  AND i.status IN ('sent', 'posted', 'partial', 'overdue')
                ORDER BY i.due_date ASC, i.id ASC";

        return $this->db->query($sql, [$contactId, $type]);
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    /**
     * Generate the next sequential payment number (PMT-000001, PMT-000002, ...).
     */
    private function getNextNumber(): string
    {
        $sql = "SELECT MAX(payment_number) FROM payments";
        $max = $this->db->queryScalar($sql);

        if ($max === null || $max === false) {
            return 'PMT-000001';
        }

        $numeric = (int) substr((string) $max, 4); // After "PMT-"
        $next = $numeric + 1;

        return 'PMT-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Update an invoice's amount_paid, balance_due, and status after a payment
     * or payment reversal.
     *
     * @param int    $invoiceId    The invoice to update
     * @param string $amountDelta  The amount to add (positive) or subtract (negative)
     */
    private function updateInvoicePaymentStatus(int $invoiceId, string $amountDelta): void
    {
        bcscale(2);

        $invoice = $this->db->queryOne("SELECT total, amount_paid, balance_due FROM invoices WHERE id = ?", [$invoiceId]);
        if ($invoice === null) {
            throw new \RuntimeException("Invoice #{$invoiceId} not found during payment allocation.");
        }

        $newAmountPaid = bcadd((string) $invoice['amount_paid'], $amountDelta);
        $newBalanceDue = bcsub((string) $invoice['total'], $newAmountPaid);

        // Derive status from balance
        if (bccomp($newBalanceDue, '0.00') <= 0) {
            $status = 'paid';
            $newBalanceDue = '0.00';
        } elseif (bccomp($newAmountPaid, '0.00') > 0) {
            $status = 'partial';
        } else {
            // Fully reversed back to unpaid -- restore to sent/posted
            $docType = $this->db->queryScalar("SELECT document_type FROM invoices WHERE id = ?", [$invoiceId]);
            $status = ($docType === 'bill') ? 'posted' : 'sent';
        }

        $this->db->exec(
            "UPDATE invoices SET amount_paid = ?, balance_due = ?, status = ?, updated_at = NOW() WHERE id = ?",
            [$newAmountPaid, $newBalanceDue, $status, $invoiceId]
        );
    }
}
