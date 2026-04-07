<?php

declare(strict_types=1);

namespace DoubleE\Models;

class Contact extends BaseModel
{
    protected string $table = 'contacts';

    /**
     * Get all customers, optionally filtering to active-only.
     */
    public function getCustomers(bool $activeOnly = true): array
    {
        return $this->getAll($activeOnly, 'customer');
    }

    /**
     * Get all vendors, optionally filtering to active-only.
     */
    public function getVendors(bool $activeOnly = true): array
    {
        return $this->getAll($activeOnly, 'vendor');
    }

    /**
     * Get all contacts with optional active and type filters.
     */
    public function getAll(bool $activeOnly = true, ?string $type = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        if ($type !== null) {
            $sql .= " AND (type = ? OR type = 'both')";
            $params[] = $type;
        }

        $sql .= " ORDER BY display_name";

        return $this->db->query($sql, $params);
    }

    /**
     * Get all addresses for a contact.
     */
    public function getAddresses(int $contactId): array
    {
        $sql = "SELECT * FROM contact_addresses
                WHERE contact_id = ?
                ORDER BY is_default DESC, type";

        return $this->db->query($sql, [$contactId]);
    }

    /**
     * Get the default address for a contact by address type.
     */
    public function getDefaultAddress(int $contactId, string $type = 'billing'): ?array
    {
        $sql = "SELECT * FROM contact_addresses
                WHERE contact_id = ? AND type = ?
                ORDER BY is_default DESC
                LIMIT 1";

        return $this->db->queryOne($sql, [$contactId, $type]);
    }

    /**
     * Get the total outstanding balance (sum of balance_due) for a contact
     * across all non-paid, non-voided invoices.
     */
    public function getOutstandingBalance(int $contactId): string
    {
        $sql = "SELECT COALESCE(SUM(balance_due), 0)
                FROM invoices
                WHERE contact_id = ?
                  AND status NOT IN ('paid','voided')";

        $result = $this->db->queryScalar($sql, [$contactId]);

        return (string) ($result ?? '0.00');
    }
}
