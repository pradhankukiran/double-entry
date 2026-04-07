<?php

declare(strict_types=1);

namespace DoubleE\Models;

class BankAccount extends BaseModel
{
    protected string $table = 'bank_accounts';

    /**
     * Get all bank accounts with their linked GL account details.
     *
     * @param bool $activeOnly Only return active bank accounts
     */
    public function getAll(bool $activeOnly = true): array
    {
        $sql = "SELECT ba.*, a.account_number, a.name AS gl_account_name
                FROM {$this->table} ba
                INNER JOIN accounts a ON a.id = ba.account_id
                WHERE 1=1";

        if ($activeOnly) {
            $sql .= " AND ba.is_active = 1";
        }

        $sql .= " ORDER BY a.account_number";

        return $this->db->query($sql);
    }

    /**
     * Get a single bank account with its linked GL account details.
     */
    public function getWithGLAccount(int $id): ?array
    {
        $sql = "SELECT ba.*, a.account_number, a.name AS gl_account_name,
                       at.name AS account_type_name
                FROM {$this->table} ba
                INNER JOIN accounts a ON a.id = ba.account_id
                INNER JOIN account_types at ON at.id = a.account_type_id
                WHERE ba.id = ?";

        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Find the bank account linked to a given GL account.
     */
    public function getByGLAccount(int $accountId): ?array
    {
        $sql = "SELECT ba.*, a.account_number, a.name AS gl_account_name
                FROM {$this->table} ba
                INNER JOIN accounts a ON a.id = ba.account_id
                WHERE ba.account_id = ?";

        return $this->db->queryOne($sql, [$accountId]);
    }
}
