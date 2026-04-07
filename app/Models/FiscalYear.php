<?php

declare(strict_types=1);

namespace DoubleE\Models;

class FiscalYear extends BaseModel
{
    protected string $table = 'fiscal_years';

    /**
     * Get the current (most recent open) fiscal year.
     */
    public function getCurrent(): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'open'
                ORDER BY start_date DESC
                LIMIT 1";

        return $this->db->queryOne($sql);
    }

    /**
     * Get all fiscal years with their periods attached.
     */
    public function getAllWithPeriods(): array
    {
        $years = $this->findAll([], 'start_date DESC');

        foreach ($years as &$year) {
            $year['periods'] = $this->getPeriods((int) $year['id']);
        }

        return $years;
    }

    /**
     * Get all fiscal periods for a given fiscal year.
     */
    public function getPeriods(int $yearId): array
    {
        $sql = "SELECT * FROM fiscal_periods
                WHERE fiscal_year_id = ?
                ORDER BY period_number";

        return $this->db->query($sql, [$yearId]);
    }
}
