<?php

declare(strict_types=1);

namespace DoubleE\Models;

class JournalEntry extends BaseModel
{
    protected string $table = 'journal_entries';

    /**
     * Get all journal entries with optional filters.
     *
     * Supports filters: status, date_from, date_to, search.
     * Includes total_debit and total_credit from journal_entry_lines subquery.
     * JOINs users for created_by name.
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT je.*,
                    CONCAT(u.first_name, ' ', u.last_name) AS created_by_name,
                    COALESCE(totals.total_debit, 0) AS total_debit,
                    COALESCE(totals.total_credit, 0) AS total_credit
                FROM {$this->table} je
                INNER JOIN users u ON u.id = je.created_by
                LEFT JOIN (
                    SELECT journal_entry_id,
                           SUM(debit) AS total_debit,
                           SUM(credit) AS total_credit
                    FROM journal_entry_lines
                    GROUP BY journal_entry_id
                ) totals ON totals.journal_entry_id = je.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND je.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND je.entry_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND je.entry_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (je.entry_number LIKE ? OR je.description LIKE ? OR je.reference LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY je.entry_date DESC, je.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int) $filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . (int) $filters['offset'];
            }
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Count journal entries matching the given filters.
     */
    public function countAll(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} je WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND je.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND je.entry_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND je.entry_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (je.entry_number LIKE ? OR je.description LIKE ? OR je.reference LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        return (int) $this->db->queryScalar($sql, $params);
    }

    /**
     * Get a journal entry with all its lines, including account names.
     */
    public function getWithLines(int $id): ?array
    {
        $entry = $this->find($id);

        if ($entry === null) {
            return null;
        }

        $sql = "SELECT jel.*, a.account_number, a.name AS account_name, at.name AS account_type
                FROM journal_entry_lines jel
                INNER JOIN accounts a ON a.id = jel.account_id
                INNER JOIN account_types at ON at.id = a.account_type_id
                WHERE jel.journal_entry_id = ?
                ORDER BY jel.line_order, jel.id";

        $entry['lines'] = $this->db->query($sql, [$id]);

        return $entry;
    }

    /**
     * Generate the next sequential entry number (JE-000001, JE-000002, ...).
     */
    public function getNextEntryNumber(): string
    {
        $sql = "SELECT MAX(entry_number) FROM {$this->table}";
        $max = $this->db->queryScalar($sql);

        if ($max === null || $max === false) {
            return 'JE-000001';
        }

        // Extract the numeric portion after "JE-"
        $numeric = (int) substr((string) $max, 3);
        $next = $numeric + 1;

        return 'JE-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get all journal entries with a given status.
     */
    public function getByStatus(string $status): array
    {
        return $this->getAll(['status' => $status]);
    }

    /**
     * Get all journal entries within a date range.
     */
    public function getByDateRange(string $from, string $to): array
    {
        return $this->getAll(['date_from' => $from, 'date_to' => $to]);
    }
}
