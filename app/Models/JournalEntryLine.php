<?php

declare(strict_types=1);

namespace DoubleE\Models;

class JournalEntryLine extends BaseModel
{
    protected string $table = 'journal_entry_lines';

    /**
     * Get all lines for a journal entry with account details.
     */
    public function getByEntry(int $entryId): array
    {
        $sql = "SELECT jel.*, a.account_number, a.name AS account_name, at.name AS account_type
                FROM {$this->table} jel
                INNER JOIN accounts a ON a.id = jel.account_id
                INNER JOIN account_types at ON at.id = a.account_type_id
                WHERE jel.journal_entry_id = ?
                ORDER BY jel.line_order, jel.id";

        return $this->db->query($sql, [$entryId]);
    }

    /**
     * Get all lines for a specific account, with journal entry details.
     * Only returns lines from posted entries.
     */
    public function getByAccount(int $accountId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $sql = "SELECT jel.*, je.entry_date, je.entry_number, je.description AS entry_description, je.status
                FROM {$this->table} jel
                INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                WHERE jel.account_id = ?
                  AND je.status = 'posted'";
        $params = [$accountId];

        if ($fromDate !== null) {
            $sql .= " AND je.entry_date >= ?";
            $params[] = $fromDate;
        }

        if ($toDate !== null) {
            $sql .= " AND je.entry_date <= ?";
            $params[] = $toDate;
        }

        $sql .= " ORDER BY je.entry_date, je.id";

        return $this->db->query($sql, $params);
    }

    /**
     * Bulk insert lines for a journal entry.
     *
     * @param int   $journalEntryId The parent journal entry ID
     * @param array $lines          Array of line data, each with: account_id, debit, credit, description, line_order
     */
    public function createMany(int $journalEntryId, array $lines): void
    {
        $sql = "INSERT INTO {$this->table}
                    (journal_entry_id, account_id, description, debit, credit, line_order)
                VALUES (?, ?, ?, ?, ?, ?)";

        foreach ($lines as $index => $line) {
            $this->db->exec($sql, [
                $journalEntryId,
                $line['account_id'],
                $line['description'] ?? null,
                $line['debit'] ?? '0.00',
                $line['credit'] ?? '0.00',
                $line['line_order'] ?? $index,
            ]);
        }
    }
}
