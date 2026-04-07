<?php

declare(strict_types=1);

namespace DoubleE\Models;

class TaxGroup extends BaseModel
{
    protected string $table = 'tax_groups';

    /**
     * Get all tax groups, optionally filtered to active only.
     *
     * @param bool $activeOnly When true, return only active tax groups
     *
     * @return array List of tax group records
     */
    public function getAll(bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }

        $sql .= " ORDER BY name ASC";

        return $this->db->query($sql);
    }

    /**
     * Get a tax group with all its associated tax rates.
     *
     * Returns the group record with a 'rates' key containing the ordered
     * list of tax rates in the group, including account information.
     *
     * @param int $id Tax group ID
     *
     * @return array|null The group with rates or null if not found
     */
    public function getWithRates(int $id): ?array
    {
        $group = $this->find($id);

        if ($group === null) {
            return null;
        }

        $sql = "SELECT tr.*, tgr.apply_order,
                       a.name AS account_name, a.account_number
                FROM tax_group_rates tgr
                INNER JOIN tax_rates tr ON tr.id = tgr.tax_rate_id
                INNER JOIN accounts a ON a.id = tr.tax_account_id
                WHERE tgr.tax_group_id = ?
                ORDER BY tgr.apply_order ASC, tr.name ASC";

        $group['rates'] = $this->db->query($sql, [$id]);

        return $group;
    }

    /**
     * Find a tax group by its unique code.
     *
     * @param string $code The tax group code
     *
     * @return array|null The tax group record or null if not found
     */
    public function findByCode(string $code): ?array
    {
        return $this->findBy('code', $code);
    }
}
