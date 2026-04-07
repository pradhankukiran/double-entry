<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\FiscalYear;
use DoubleE\Models\FiscalPeriod;

class FiscalYearService
{
    private Database $db;
    private FiscalYear $fiscalYearModel;
    private FiscalPeriod $fiscalPeriodModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->fiscalYearModel = new FiscalYear();
        $this->fiscalPeriodModel = new FiscalPeriod();
    }

    /**
     * Create a new fiscal year and auto-generate 12 monthly periods.
     *
     * @return int The newly created fiscal year ID
     */
    public function create(string $name, string $startDate, string $endDate): int
    {
        return (int) $this->db->transaction(function () use ($name, $startDate, $endDate) {
            $yearId = $this->fiscalYearModel->create([
                'name'       => $name,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'status'     => 'open',
            ]);

            // Generate 12 monthly periods
            $periodStart = new \DateTimeImmutable($startDate);

            for ($i = 1; $i <= 12; $i++) {
                $periodEnd = $periodStart->modify('last day of this month');

                // Ensure the final period doesn't exceed the fiscal year end date
                $fiscalEnd = new \DateTimeImmutable($endDate);
                if ($periodEnd > $fiscalEnd) {
                    $periodEnd = $fiscalEnd;
                }

                $this->fiscalPeriodModel->create([
                    'fiscal_year_id' => $yearId,
                    'period_number'  => $i,
                    'name'           => $periodStart->format('F Y'),
                    'start_date'     => $periodStart->format('Y-m-d'),
                    'end_date'       => $periodEnd->format('Y-m-d'),
                    'status'         => 'open',
                ]);

                // Move to the first day of the next month
                $periodStart = $periodEnd->modify('+1 day');

                // Stop if we've passed the fiscal year end date
                if ($periodStart > $fiscalEnd) {
                    break;
                }
            }

            return $yearId;
        });
    }

    /**
     * Lock a fiscal period so no further entries can be posted.
     */
    public function closePeriod(int $periodId, int $userId): void
    {
        $this->fiscalPeriodModel->update($periodId, [
            'status'    => 'locked',
            'locked_by' => $userId,
            'locked_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Close an entire fiscal year: lock all its periods and set year status to closed.
     */
    public function closeYear(int $yearId, int $userId): void
    {
        $this->db->transaction(function () use ($yearId, $userId) {
            // Close all periods belonging to this fiscal year
            $periods = $this->fiscalYearModel->getPeriods($yearId);
            $now = date('Y-m-d H:i:s');

            foreach ($periods as $period) {
                if ($period['status'] !== 'closed') {
                    $this->fiscalPeriodModel->update((int) $period['id'], [
                        'status'    => 'closed',
                        'locked_by' => $userId,
                        'locked_at' => $now,
                    ]);
                }
            }

            // Close the fiscal year itself
            $this->fiscalYearModel->update($yearId, [
                'status' => 'closed',
            ]);
        });
    }
}
