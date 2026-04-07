<?php

declare(strict_types=1);

namespace DoubleE\Models;

class FiscalPeriod extends BaseModel
{
    protected string $table = 'fiscal_periods';

    /**
     * Find the fiscal period that contains a given date.
     */
    public function findByDate(string $date): ?array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE start_date <= ? AND end_date >= ?
                LIMIT 1";

        return $this->db->queryOne($sql, [$date, $date]);
    }

    /**
     * Get all open fiscal periods.
     */
    public function getOpen(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'open'
                ORDER BY start_date";

        return $this->db->query($sql);
    }
}
