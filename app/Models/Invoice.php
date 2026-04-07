<?php

declare(strict_types=1);

namespace DoubleE\Models;

class Invoice extends BaseModel
{
    protected string $table = 'invoices';

    /**
     * Get all invoices with optional filters.
     *
     * Supports filters: document_type, status, contact_id, date_from, date_to.
     * JOINs contacts for display_name.
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT i.*, c.display_name AS contact_name
                FROM {$this->table} i
                INNER JOIN contacts c ON c.id = i.contact_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['document_type'])) {
            $sql .= " AND i.document_type = ?";
            $params[] = $filters['document_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND i.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['contact_id'])) {
            $sql .= " AND i.contact_id = ?";
            $params[] = $filters['contact_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND i.issue_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND i.issue_date <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY i.issue_date DESC, i.id DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Get an invoice with all its lines, including account names.
     */
    public function getWithLines(int $id): ?array
    {
        $invoice = $this->find($id);

        if ($invoice === null) {
            return null;
        }

        $sql = "SELECT il.*, a.account_number, a.name AS account_name
                FROM invoice_lines il
                INNER JOIN accounts a ON a.id = il.account_id
                WHERE il.invoice_id = ?
                ORDER BY il.line_order, il.id";

        $invoice['lines'] = $this->db->query($sql, [$id]);

        return $invoice;
    }

    /**
     * Get all invoices for a specific contact, optionally filtered by document type.
     */
    public function getByContact(int $contactId, ?string $type = null): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE contact_id = ?";
        $params = [$contactId];

        if ($type !== null) {
            $sql .= " AND document_type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY issue_date DESC, id DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Generate the next sequential document number based on type.
     *
     * Returns: INV-000001, BILL-000001, or CN-000001.
     */
    public function getNextNumber(string $type): string
    {
        $prefixes = [
            'invoice'     => 'INV-',
            'bill'        => 'BILL-',
            'credit_note' => 'CN-',
        ];

        $prefix = $prefixes[$type] ?? 'INV-';

        $sql = "SELECT MAX(document_number) FROM {$this->table}
                WHERE document_type = ?";
        $max = $this->db->queryScalar($sql, [$type]);

        if ($max === null || $max === false) {
            return $prefix . '000001';
        }

        $numeric = (int) substr((string) $max, strlen($prefix));
        $next = $numeric + 1;

        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get all overdue invoices (past due date and not paid/voided/draft).
     */
    public function getOverdue(): array
    {
        $sql = "SELECT i.*, c.display_name AS contact_name
                FROM {$this->table} i
                INNER JOIN contacts c ON c.id = i.contact_id
                WHERE i.due_date < CURDATE()
                  AND i.status NOT IN ('paid','voided','draft')
                ORDER BY i.due_date ASC";

        return $this->db->query($sql);
    }

    /**
     * Recalculate the payment status of an invoice from its payment allocations.
     *
     * Updates amount_paid, balance_due, and status (paid / partial / original status).
     */
    public function updatePaymentStatus(int $id): void
    {
        $invoice = $this->find($id);

        if ($invoice === null) {
            return;
        }

        $sql = "SELECT COALESCE(SUM(amount), 0)
                FROM payment_allocations
                WHERE invoice_id = ?";
        $amountPaid = (string) $this->db->queryScalar($sql, [$id]);

        $total = $invoice['total'];
        $balanceDue = bcsub((string) $total, $amountPaid, 2);

        if (bccomp($balanceDue, '0.00', 2) <= 0) {
            $status = 'paid';
            $balanceDue = '0.00';
        } elseif (bccomp($amountPaid, '0.00', 2) > 0) {
            $status = 'partial';
        } else {
            // No payments -- keep existing status unless it was 'paid' or 'partial'
            $status = in_array($invoice['status'], ['paid', 'partial'], true)
                ? 'sent'
                : $invoice['status'];
        }

        $this->update($id, [
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'status'      => $status,
        ]);
    }
}
