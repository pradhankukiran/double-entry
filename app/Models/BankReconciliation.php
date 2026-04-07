<?php

declare(strict_types=1);

namespace DoubleE\Models;

class BankReconciliation extends BaseModel
{
    protected string $table = 'bank_reconciliations';

    /**
     * Get all reconciliations for a bank account, most recent first.
     */
    public function getByBankAccount(int $bankAccountId): array
    {
        $sql = "SELECT br.*, u.full_name AS reconciled_by_name
                FROM {$this->table} br
                INNER JOIN users u ON u.id = br.reconciled_by
                WHERE br.bank_account_id = ?
                ORDER BY br.statement_date DESC, br.id DESC";

        return $this->db->query($sql, [$bankAccountId]);
    }

    /**
     * Get a reconciliation with all its cleared lines and journal entry details.
     */
    public function getWithLines(int $id): ?array
    {
        $reconciliation = $this->find($id);

        if ($reconciliation === null) {
            return null;
        }

        $sql = "SELECT brl.*,
                       jel.account_id, jel.debit, jel.credit, jel.description AS line_description,
                       je.id AS journal_entry_id, je.entry_number, je.entry_date, je.description AS entry_description,
                       a.account_number, a.name AS account_name
                FROM bank_reconciliation_lines brl
                INNER JOIN journal_entry_lines jel ON jel.id = brl.journal_entry_line_id
                INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                INNER JOIN accounts a ON a.id = jel.account_id
                WHERE brl.reconciliation_id = ?
                ORDER BY je.entry_date, je.id, jel.id";

        $reconciliation['lines'] = $this->db->query($sql, [$id]);

        return $reconciliation;
    }
}
