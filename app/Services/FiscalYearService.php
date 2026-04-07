<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\Account;
use DoubleE\Models\AuditLog;
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

    /**
     * Close a fiscal year with closing journal entries.
     *
     * Creates a closing journal entry that zeros out all revenue and expense
     * accounts, posting the net income (or loss) to Retained Earnings (3100).
     * All fiscal periods are closed and the year status is set to 'closed'.
     *
     * @param int $yearId The fiscal year to close
     * @param int $userId The user performing the close
     *
     * @return int The closing journal entry ID
     *
     * @throws \RuntimeException If the fiscal year is not open or has no closable accounts
     */
    public function closeYearWithEntries(int $yearId, int $userId): int
    {
        bcscale(2);

        $accountModel = new Account();
        $ledgerService = new LedgerService();
        $journalService = new JournalEntryService();

        $entryId = (int) $this->db->transaction(function () use (
            $yearId,
            $userId,
            $accountModel,
            $ledgerService,
            $journalService,
        ) {
            // (a) Load the fiscal year and verify it is open
            $year = $this->fiscalYearModel->find($yearId);
            if ($year === null) {
                throw new \RuntimeException("Fiscal year #{$yearId} not found.");
            }
            if ($year['status'] !== 'open') {
                throw new \RuntimeException(
                    "Fiscal year '{$year['name']}' is not open. Current status: {$year['status']}."
                );
            }

            // (b) Get the year's end date
            $endDate = $year['end_date'];

            // (c) Find the last fiscal period of the year (needed for the JE)
            $periods = $this->fiscalYearModel->getPeriods($yearId);
            if (empty($periods)) {
                throw new \RuntimeException('Fiscal year has no periods.');
            }

            // Ensure the last period is open so the closing entry can be posted
            $lastPeriod = end($periods);
            if ($lastPeriod['status'] === 'closed') {
                throw new \RuntimeException(
                    "The last fiscal period '{$lastPeriod['name']}' is already closed. Cannot create closing entry."
                );
            }

            // (d) Get all REVENUE accounts (account_type_id = 4)
            $revenueAccounts = $accountModel->getByType(4, false);
            $revenueLines = [];
            $totalRevenue = '0.00';

            foreach ($revenueAccounts as $account) {
                if ((int) ($account['is_header'] ?? 0) === 1) {
                    continue;
                }
                $balance = $ledgerService->getAccountBalance((int) $account['id'], $endDate);
                if (bccomp($balance, '0.00') !== 0) {
                    // Revenue accounts have credit-normal balances; debit to zero them out
                    $revenueLines[] = [
                        'account_id'  => (int) $account['id'],
                        'debit'       => $balance,
                        'credit'      => '0.00',
                        'description' => 'Close revenue to Retained Earnings',
                    ];
                    $totalRevenue = bcadd($totalRevenue, $balance);
                }
            }

            // (e) Get all EXPENSE accounts (account_type_id = 5)
            $expenseAccounts = $accountModel->getByType(5, false);
            $expenseLines = [];
            $totalExpenses = '0.00';

            foreach ($expenseAccounts as $account) {
                if ((int) ($account['is_header'] ?? 0) === 1) {
                    continue;
                }
                $balance = $ledgerService->getAccountBalance((int) $account['id'], $endDate);
                if (bccomp($balance, '0.00') !== 0) {
                    // Expense accounts have debit-normal balances; credit to zero them out
                    $expenseLines[] = [
                        'account_id'  => (int) $account['id'],
                        'debit'       => '0.00',
                        'credit'      => $balance,
                        'description' => 'Close expense to Retained Earnings',
                    ];
                    $totalExpenses = bcadd($totalExpenses, $balance);
                }
            }

            // (f) Build the Retained Earnings line
            $netIncome = bcsub($totalRevenue, $totalExpenses);
            $retainedEarnings = $accountModel->findByNumber('3100');
            if ($retainedEarnings === null) {
                throw new \RuntimeException('Retained Earnings account (3100) not found.');
            }

            $reLine = [
                'account_id'  => (int) $retainedEarnings['id'],
                'debit'       => '0.00',
                'credit'      => '0.00',
                'description' => 'Net income to Retained Earnings',
            ];

            if (bccomp($netIncome, '0.00') > 0) {
                // Positive net income: credit Retained Earnings
                $reLine['credit'] = $netIncome;
            } elseif (bccomp($netIncome, '0.00') < 0) {
                // Net loss: debit Retained Earnings
                $reLine['debit'] = bcmul($netIncome, '-1');
            }

            // Combine all lines
            $allLines = array_merge($revenueLines, $expenseLines);
            if (bccomp($netIncome, '0.00') !== 0) {
                $allLines[] = $reLine;
            }

            if (empty($allLines)) {
                throw new \RuntimeException(
                    'No revenue or expense balances to close for this fiscal year.'
                );
            }

            // (g) Create and post the closing journal entry
            $header = [
                'entry_date'   => $endDate,
                'description'  => "Year-end closing entry — {$year['name']}",
                'reference'    => null,
                'source_type'  => 'year_end_close',
            ];

            $entryId = $journalService->createAndPost($header, $allLines, $userId);

            // (h) Close all fiscal periods
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

            // (i) Close the fiscal year
            $this->fiscalYearModel->update($yearId, [
                'status'    => 'closed',
                'closed_by' => $userId,
                'closed_at' => $now,
            ]);

            return $entryId;
        });

        // (j) Audit log
        AuditLog::log(
            'fiscal_year.closed',
            'fiscal_year',
            $yearId,
            ['status' => 'open'],
            [
                'status'            => 'closed',
                'closing_entry_id'  => $entryId,
                'closed_by'         => $userId,
            ]
        );

        // (k) Return the closing journal entry ID
        return $entryId;
    }
}
