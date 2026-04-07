<?php

declare(strict_types=1);

namespace DoubleE\Models;

class RecurringTemplate extends BaseModel
{
    protected string $table = 'recurring_templates';

    /**
     * Get all recurring templates, optionally filtered to active only.
     *
     * Includes the creator's display name via a JOIN on users.
     *
     * @param bool $activeOnly When true, return only active templates
     *
     * @return array List of template records
     */
    public function getAll(bool $activeOnly = true): array
    {
        $sql = "SELECT rt.*, CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
                FROM {$this->table} rt
                INNER JOIN users u ON u.id = rt.created_by";

        if ($activeOnly) {
            $sql .= " WHERE rt.is_active = 1";
        }

        $sql .= " ORDER BY rt.next_run_date ASC, rt.name ASC";

        return $this->db->query($sql);
    }

    /**
     * Get a template with all its line items, including account details.
     *
     * @param int $id Template ID
     *
     * @return array|null The template with 'lines' key, or null if not found
     */
    public function getWithLines(int $id): ?array
    {
        $template = $this->find($id);

        if ($template === null) {
            return null;
        }

        $template['lines'] = $this->getLines($id);

        return $template;
    }

    /**
     * Get all templates that are due for processing.
     *
     * A template is due when it is active and its next_run_date is today or earlier.
     * Also respects total_occurrences limit if set.
     *
     * @return array List of due template records
     */
    public function getDue(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE is_active = 1
                  AND next_run_date <= CURDATE()
                  AND (total_occurrences IS NULL OR occurrences_created < total_occurrences)
                ORDER BY next_run_date ASC";

        return $this->db->query($sql);
    }

    /**
     * Get the line items for a specific template, including account details.
     *
     * @param int $templateId Template ID
     *
     * @return array List of template line records
     */
    public function getLines(int $templateId): array
    {
        $sql = "SELECT rtl.*, a.account_number, a.name AS account_name
                FROM recurring_template_lines rtl
                INNER JOIN accounts a ON a.id = rtl.account_id
                WHERE rtl.template_id = ?
                ORDER BY rtl.line_number ASC, rtl.id ASC";

        return $this->db->query($sql, [$templateId]);
    }
}
