<?php

declare(strict_types=1);

namespace DoubleE\Models;

class BankTransaction extends BaseModel
{
    protected string $table = 'bank_transactions';

    /**
     * Get bank transactions for an account with optional filters.
     *
     * Supported filters: status, date_from, date_to.
     */
    public function getByBankAccount(int $bankAccountId, array $filters = []): array
    {
        $sql = "SELECT bt.*, je.entry_number AS journal_entry_number
                FROM {$this->table} bt
                LEFT JOIN journal_entries je ON je.id = bt.journal_entry_id
                WHERE bt.bank_account_id = ?";
        $params = [$bankAccountId];

        if (!empty($filters['status'])) {
            $sql .= " AND bt.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND bt.transaction_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND bt.transaction_date <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY bt.transaction_date DESC, bt.id DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Get all unmatched transactions for a bank account.
     */
    public function getUnmatched(int $bankAccountId): array
    {
        return $this->getByBankAccount($bankAccountId, ['status' => 'unmatched']);
    }

    /**
     * Get all transactions belonging to a specific import batch.
     */
    public function getByBatch(int $batchId): array
    {
        $sql = "SELECT bt.*, je.entry_number AS journal_entry_number
                FROM {$this->table} bt
                LEFT JOIN journal_entries je ON je.id = bt.journal_entry_id
                WHERE bt.import_batch_id = ?
                ORDER BY bt.transaction_date, bt.id";

        return $this->db->query($sql, [$batchId]);
    }
}
