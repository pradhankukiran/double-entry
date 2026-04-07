<?php

declare(strict_types=1);

namespace DoubleE\Models;

class TaxRate extends BaseModel
{
    protected string $table = 'tax_rates';

    /**
     * Get all tax rates, optionally filtered to active only.
     *
     * @param bool $activeOnly When true, return only active tax rates
     *
     * @return array List of tax rate records
     */
    public function getAll(bool $activeOnly = true): array
    {
        $sql = "SELECT tr.*, a.name AS account_name, a.account_number
                FROM {$this->table} tr
                INNER JOIN accounts a ON a.id = tr.tax_account_id";

        if ($activeOnly) {
            $sql .= " WHERE tr.is_active = 1";
        }

        $sql .= " ORDER BY tr.name ASC";

        return $this->db->query($sql);
    }

    /**
     * Find a tax rate by its unique code.
     *
     * @param string $code The tax rate code (e.g. 'GST', 'VAT')
     *
     * @return array|null The tax rate record or null if not found
     */
    public function findByCode(string $code): ?array
    {
        $sql = "SELECT tr.*, a.name AS account_name, a.account_number
                FROM {$this->table} tr
                INNER JOIN accounts a ON a.id = tr.tax_account_id
                WHERE tr.code = ?
                LIMIT 1";

        return $this->db->queryOne($sql, [$code]);
    }
}
