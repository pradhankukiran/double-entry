<?php

declare(strict_types=1);

namespace DoubleE\Models;

class Payment extends BaseModel
{
    protected string $table = 'payments';

    /**
     * Get all payments with optional filters.
     *
     * Supports filters: type, status, contact_id, date_from, date_to.
     * JOINs contacts for display_name.
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT p.*, c.display_name AS contact_name
                FROM {$this->table} p
                INNER JOIN contacts c ON c.id = p.contact_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND p.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['contact_id'])) {
            $sql .= " AND p.contact_id = ?";
            $params[] = $filters['contact_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND p.payment_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND p.payment_date <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY p.payment_date DESC, p.id DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Get a payment with all its allocations, including invoice document numbers.
     */
    public function getWithAllocations(int $id): ?array
    {
        $payment = $this->find($id);

        if ($payment === null) {
            return null;
        }

        $sql = "SELECT pa.*, i.document_number, i.document_type, i.total AS invoice_total, i.balance_due
                FROM payment_allocations pa
                INNER JOIN invoices i ON i.id = pa.invoice_id
                WHERE pa.payment_id = ?
                ORDER BY pa.id";

        $payment['allocations'] = $this->db->query($sql, [$id]);

        return $payment;
    }

    /**
     * Generate the next sequential payment number based on type.
     *
     * Returns: PMT-R-000001 for received, PMT-M-000001 for made.
     */
    public function getNextNumber(string $type): string
    {
        $prefix = $type === 'received' ? 'PMT-R-' : 'PMT-M-';

        $sql = "SELECT MAX(payment_number) FROM {$this->table}
                WHERE type = ?";
        $max = $this->db->queryScalar($sql, [$type]);

        if ($max === null || $max === false) {
            return $prefix . '000001';
        }

        $numeric = (int) substr((string) $max, strlen($prefix));
        $next = $numeric + 1;

        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get all payments for a specific contact.
     */
    public function getByContact(int $contactId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE contact_id = ?
                ORDER BY payment_date DESC, id DESC";

        return $this->db->query($sql, [$contactId]);
    }
}
