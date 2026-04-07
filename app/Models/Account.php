<?php

declare(strict_types=1);

namespace DoubleE\Models;

class Account extends BaseModel
{
    protected string $table = 'accounts';

    /**
     * Find an account by its account number.
     */
    public function findByNumber(string $accountNumber): ?array
    {
        return $this->findBy('account_number', $accountNumber);
    }

    /**
     * Get all accounts with their account type name, ordered by account_number.
     */
    public function getAll(bool $activeOnly = true): array
    {
        $sql = "SELECT a.*, at.name AS type_name
                FROM {$this->table} a
                INNER JOIN account_types at ON at.id = a.account_type_id
                WHERE 1=1";

        if ($activeOnly) {
            $sql .= " AND a.is_active = 1";
        }

        $sql .= " ORDER BY a.account_number";

        return $this->db->query($sql);
    }

    /**
     * Get all accounts of a specific type.
     */
    public function getByType(int $typeId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE account_type_id = ?";
        $params = [$typeId];

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY account_number";

        return $this->db->query($sql, $params);
    }

    /**
     * Get all direct children of a parent account.
     */
    public function getChildren(int $parentId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE parent_id = ?
                ORDER BY account_number";

        return $this->db->query($sql, [$parentId]);
    }

    /**
     * Get a flat list of all accounts with type and subtype names.
     * The controller/view will handle tree rendering based on parent_id.
     */
    public function getHierarchy(bool $activeOnly = true): array
    {
        $sql = "SELECT a.*, at.name AS type_name, ast.name AS subtype_name
                FROM {$this->table} a
                INNER JOIN account_types at ON at.id = a.account_type_id
                LEFT JOIN account_subtypes ast ON ast.id = a.account_subtype_id
                WHERE 1=1";

        if ($activeOnly) {
            $sql .= " AND a.is_active = 1";
        }

        $sql .= " ORDER BY a.account_number";

        return $this->db->query($sql);
    }

    /**
     * Get only leaf (non-header) accounts for journal entry dropdowns.
     */
    public function getLeafAccounts(bool $activeOnly = true): array
    {
        $sql = "SELECT a.*, at.name AS type_name
                FROM {$this->table} a
                INNER JOIN account_types at ON at.id = a.account_type_id
                WHERE a.is_header = 0";

        if ($activeOnly) {
            $sql .= " AND a.is_active = 1";
        }

        $sql .= " ORDER BY a.account_number";

        return $this->db->query($sql);
    }

    /**
     * Check whether any journal entry lines reference this account.
     * Returns false for now; will be implemented when journal tables exist.
     */
    public function hasTransactions(int $accountId): bool
    {
        // TODO: Implement when journal_entry_lines table exists
        // $sql = "SELECT COUNT(*) FROM journal_entry_lines WHERE account_id = ?";
        // return (int) $this->db->queryScalar($sql, [$accountId]) > 0;
        return false;
    }

    /**
     * Toggle the is_active flag between 0 and 1.
     */
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE {$this->table}
                SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END
                WHERE {$this->primaryKey} = ?";

        return $this->db->exec($sql, [$id]) > 0;
    }
}
