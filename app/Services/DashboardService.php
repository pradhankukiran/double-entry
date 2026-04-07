<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\AccountType;

class DashboardService
{
    private Database $db;
    private AccountType $accountTypeModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->accountTypeModel = new AccountType();
    }

    /**
     * Get key performance indicators for the dashboard.
     *
     * Returns:
     * - total_revenue:    SUM of revenue account balances for the current fiscal year
     * - total_expenses:   SUM of expense account balances for the current fiscal year
     * - net_income:       revenue minus expenses
     * - ar_outstanding:   total balance_due on unpaid/non-voided invoices
     * - ap_outstanding:   total balance_due on unpaid/non-voided bills
     * - cash_balance:     total balance across all bank/cash accounts
     *
     * @return array Associative array of KPI values
     */
    public function getKpis(): array
    {
        bcscale(2);

        // Determine fiscal year boundaries (current calendar year as fallback)
        $fiscalYear = $this->getCurrentFiscalYear();
        $fromDate = $fiscalYear['start_date'];
        $toDate = $fiscalYear['end_date'];

        // Revenue: SUM(credit - debit) for revenue accounts in the fiscal year
        $revenueType = $this->accountTypeModel->findByCode('REVENUE');
        $totalRevenue = $this->getAccountTypeBalance(
            (int) $revenueType['id'],
            'credit',
            $fromDate,
            $toDate
        );

        // Expenses: SUM(debit - credit) for expense accounts in the fiscal year
        $expenseType = $this->accountTypeModel->findByCode('EXPENSE');
        $totalExpenses = $this->getAccountTypeBalance(
            (int) $expenseType['id'],
            'debit',
            $fromDate,
            $toDate
        );

        $netIncome = bcsub($totalRevenue, $totalExpenses);

        // AR outstanding: unpaid/non-voided invoices
        $arOutstanding = $this->getOutstandingBalance('invoice');

        // AP outstanding: unpaid/non-voided bills
        $apOutstanding = $this->getOutstandingBalance('bill');

        // Cash balance: all bank accounts
        $cashBalance = $this->getCashBalance();

        return [
            'total_revenue'  => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income'     => $netIncome,
            'ar_outstanding' => $arOutstanding,
            'ap_outstanding' => $apOutstanding,
            'cash_balance'   => $cashBalance,
            'fiscal_year'    => $fiscalYear['name'] ?? "{$fromDate} to {$toDate}",
        ];
    }

    /**
     * Get monthly revenue and expense totals for Chart.js display.
     *
     * Returns the last N months of data with labels suitable for chart rendering.
     *
     * @param int $months Number of months to include (default 6)
     *
     * @return array ['labels' => [...], 'revenue' => [...], 'expenses' => [...]]
     */
    public function getRevenueExpenseChart(int $months = 6): array
    {
        $revenueType = $this->accountTypeModel->findByCode('REVENUE');
        $expenseType = $this->accountTypeModel->findByCode('EXPENSE');

        $labels = [];
        $revenue = [];
        $expenses = [];

        // Build each month going backwards from the current month
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new \DateTimeImmutable("first day of -{$i} months");
            $monthStart = $date->format('Y-m-01');
            $monthEnd = $date->format('Y-m-t');
            $labels[] = $date->format('M Y');

            // Revenue for this month
            $revSql = "SELECT COALESCE(SUM(jel.credit) - SUM(jel.debit), 0) AS total
                       FROM journal_entry_lines jel
                       INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                       INNER JOIN accounts a ON a.id = jel.account_id
                       WHERE a.account_type_id = ?
                         AND je.status = 'posted'
                         AND je.entry_date >= ?
                         AND je.entry_date <= ?";

            $revTotal = $this->db->queryScalar($revSql, [
                $revenueType['id'],
                $monthStart,
                $monthEnd,
            ]);
            $revenue[] = number_format((float) ($revTotal ?: 0), 2, '.', '');

            // Expenses for this month
            $expSql = "SELECT COALESCE(SUM(jel.debit) - SUM(jel.credit), 0) AS total
                       FROM journal_entry_lines jel
                       INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                       INNER JOIN accounts a ON a.id = jel.account_id
                       WHERE a.account_type_id = ?
                         AND je.status = 'posted'
                         AND je.entry_date >= ?
                         AND je.entry_date <= ?";

            $expTotal = $this->db->queryScalar($expSql, [
                $expenseType['id'],
                $monthStart,
                $monthEnd,
            ]);
            $expenses[] = number_format((float) ($expTotal ?: 0), 2, '.', '');
        }

        return [
            'labels'   => $labels,
            'revenue'  => $revenue,
            'expenses' => $expenses,
        ];
    }

    /**
     * Get the most recent journal entries for dashboard activity feed.
     *
     * @param int $limit Maximum number of entries to return (default 10)
     *
     * @return array List of recent journal entries with creator name
     */
    public function getRecentActivity(int $limit = 10): array
    {
        $sql = "SELECT je.id, je.entry_number, je.entry_date, je.description,
                       je.status, je.created_at,
                       CONCAT(u.first_name, ' ', u.last_name) AS created_by_name,
                       COALESCE(SUM(jel.debit), 0) AS total_amount
                FROM journal_entries je
                INNER JOIN users u ON u.id = je.created_by
                LEFT JOIN journal_entry_lines jel ON jel.journal_entry_id = je.id
                GROUP BY je.id, je.entry_number, je.entry_date, je.description,
                         je.status, je.created_at, u.first_name, u.last_name
                ORDER BY je.created_at DESC
                LIMIT ?";

        return $this->db->query($sql, [$limit]);
    }

    /**
     * Get a rich activity feed from the audit log for the dashboard.
     *
     * Pulls from audit_log joined with users, resolves entity display numbers,
     * and builds human-readable descriptions with relative timestamps.
     *
     * @param int $limit Maximum number of entries to return (default 15)
     *
     * @return array List of activity items with description, action, entity info, and time_ago
     */
    public function getActivityFeed(int $limit = 15): array
    {
        $sql = "SELECT al.id, al.action, al.entity_type, al.entity_id, al.created_at,
                       CONCAT(u.first_name, ' ', u.last_name) AS user_name
                FROM audit_log al
                LEFT JOIN users u ON u.id = al.user_id
                ORDER BY al.created_at DESC
                LIMIT ?";

        $rows = $this->db->query($sql, [$limit]);

        $feed = [];

        foreach ($rows as $row) {
            $action     = $row['action'] ?? '';
            $entityType = $row['entity_type'] ?? '';
            $entityId   = $row['entity_id'] ?? null;
            $userName   = $row['user_name'] ?? 'System';
            $createdAt  = $row['created_at'] ?? '';

            // Resolve entity display number
            $entityNumber = $this->resolveEntityNumber($entityType, $entityId);

            // Build human-readable description
            $description = $this->buildActivityDescription($action, $entityType, $userName, $entityNumber);

            // Build entity URL
            $entityUrl = $this->buildEntityUrl($entityType, $entityId);

            $feed[] = [
                'description' => $description,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_url'  => $entityUrl,
                'user_name'   => $userName,
                'time_ago'    => $this->timeAgo($createdAt),
                'created_at'  => $createdAt,
            ];
        }

        return $feed;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve a display number for an entity from its source table.
     */
    private function resolveEntityNumber(string $entityType, ?int $entityId): ?string
    {
        if ($entityId === null) {
            return null;
        }

        $lookups = [
            'journal_entry' => ['table' => 'journal_entries', 'column' => 'entry_number'],
            'invoice'       => ['table' => 'invoices',        'column' => 'document_number'],
            'payment'       => ['table' => 'payments',        'column' => 'payment_number'],
        ];

        if (!isset($lookups[$entityType])) {
            return null;
        }

        $lookup = $lookups[$entityType];
        $sql = "SELECT {$lookup['column']} FROM {$lookup['table']} WHERE id = ?";

        $result = $this->db->queryScalar($sql, [$entityId]);

        return $result !== null ? (string) $result : null;
    }

    /**
     * Build a human-readable description for an audit log entry.
     */
    private function buildActivityDescription(string $action, string $entityType, string $userName, ?string $entityNumber): string
    {
        if ($action === 'login') {
            return "{$userName} logged in";
        }

        $actionVerbs = [
            'create' => 'created',
            'update' => 'updated',
            'delete' => 'deleted',
            'post'   => 'posted',
            'void'   => 'voided',
        ];

        $entityLabels = [
            'journal_entry' => 'journal entry',
            'invoice'       => 'invoice',
            'payment'       => 'payment',
            'account'       => 'account',
            'contact'       => 'contact',
            'bill'          => 'bill',
            'user'          => 'user',
        ];

        $verb  = $actionVerbs[$action] ?? $action;
        $label = $entityLabels[$entityType] ?? str_replace('_', ' ', $entityType);
        $ref   = $entityNumber !== null ? " {$entityNumber}" : '';

        return "{$userName} {$verb} {$label}{$ref}";
    }

    /**
     * Build a URL for an entity, or null if not linkable.
     */
    private function buildEntityUrl(string $entityType, ?int $entityId): ?string
    {
        if ($entityId === null) {
            return null;
        }

        $routes = [
            'journal_entry' => '/journal-entries/',
            'invoice'       => '/invoices/',
            'payment'       => '/payments/',
            'account'       => '/accounts/',
            'contact'       => '/contacts/',
            'bill'          => '/bills/',
        ];

        if (!isset($routes[$entityType])) {
            return null;
        }

        return $routes[$entityType] . $entityId;
    }

    /**
     * Calculate a human-readable relative time string.
     */
    private function timeAgo(string $datetime): string
    {
        if (empty($datetime)) {
            return '';
        }

        $now  = new \DateTimeImmutable('now');
        $then = new \DateTimeImmutable($datetime);
        $diff = $now->getTimestamp() - $then->getTimestamp();

        if ($diff < 60) {
            return 'just now';
        }

        $minutes = (int) floor($diff / 60);
        if ($minutes < 60) {
            return $minutes === 1 ? '1 minute ago' : "{$minutes} minutes ago";
        }

        $hours = (int) floor($minutes / 60);
        if ($hours < 24) {
            return $hours === 1 ? '1 hour ago' : "{$hours} hours ago";
        }

        $days = (int) floor($hours / 24);
        if ($days < 30) {
            return $days === 1 ? '1 day ago' : "{$days} days ago";
        }

        $months = (int) floor($days / 30);
        if ($months < 12) {
            return $months === 1 ? '1 month ago' : "{$months} months ago";
        }

        $years = (int) floor($months / 12);
        return $years === 1 ? '1 year ago' : "{$years} years ago";
    }

    /**
     * Get the current fiscal year record, falling back to calendar year dates.
     *
     * @return array Fiscal year record or synthetic array with date boundaries
     */
    private function getCurrentFiscalYear(): array
    {
        $today = date('Y-m-d');

        $sql = "SELECT * FROM fiscal_years
                WHERE start_date <= ? AND end_date >= ?
                ORDER BY start_date DESC
                LIMIT 1";

        $fiscalYear = $this->db->queryOne($sql, [$today, $today]);

        if ($fiscalYear !== null) {
            return $fiscalYear;
        }

        // Fallback: use calendar year
        $year = date('Y');
        return [
            'name'       => "Calendar Year {$year}",
            'start_date' => "{$year}-01-01",
            'end_date'   => "{$year}-12-31",
        ];
    }

    /**
     * Get the total balance for accounts of a given type within a date range.
     *
     * @param int    $typeId        The account type ID
     * @param string $normalBalance 'debit' for expenses, 'credit' for revenue
     * @param string $fromDate      Period start (inclusive)
     * @param string $toDate        Period end (inclusive)
     *
     * @return string The total balance as a decimal string
     */
    private function getAccountTypeBalance(int $typeId, string $normalBalance, string $fromDate, string $toDate): string
    {
        $sql = "SELECT COALESCE(SUM(jel.debit), 0) AS total_debit,
                       COALESCE(SUM(jel.credit), 0) AS total_credit
                FROM journal_entry_lines jel
                INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                INNER JOIN accounts a ON a.id = jel.account_id
                WHERE a.account_type_id = ?
                  AND a.is_active = 1
                  AND a.is_header = 0
                  AND je.status = 'posted'
                  AND je.entry_date >= ?
                  AND je.entry_date <= ?";

        $row = $this->db->queryOne($sql, [$typeId, $fromDate, $toDate]);

        if ($row === null) {
            return '0.00';
        }

        if ($normalBalance === 'credit') {
            return bcsub($row['total_credit'], $row['total_debit']);
        }

        return bcsub($row['total_debit'], $row['total_credit']);
    }

    /**
     * Get the total outstanding balance for invoices or bills.
     *
     * @param string $documentType 'invoice' for AR, 'bill' for AP
     *
     * @return string Total outstanding balance_due
     */
    private function getOutstandingBalance(string $documentType): string
    {
        $sql = "SELECT COALESCE(SUM(balance_due), 0)
                FROM invoices
                WHERE document_type = ?
                  AND status NOT IN ('paid', 'voided')";

        $result = $this->db->queryScalar($sql, [$documentType]);

        return number_format((float) ($result ?: 0), 2, '.', '');
    }

    /**
     * Get the total cash balance across all bank/cash accounts.
     *
     * Bank accounts are identified by the is_bank_account flag. These are
     * asset (debit-normal) accounts, so balance = debits - credits + opening_balance.
     *
     * @return string Total cash balance
     */
    private function getCashBalance(): string
    {
        $sql = "SELECT a.id, a.opening_balance,
                       COALESCE(SUM(jel.debit), 0) AS total_debit,
                       COALESCE(SUM(jel.credit), 0) AS total_credit
                FROM accounts a
                LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
                LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
                                            AND je.status = 'posted'
                WHERE a.is_bank_account = 1
                  AND a.is_active = 1
                  AND a.is_header = 0
                GROUP BY a.id, a.opening_balance";

        $rows = $this->db->query($sql);

        $totalCash = '0.00';

        foreach ($rows as $row) {
            $openingBalance = $row['opening_balance'] ?? '0.00';
            $balance = bcadd(bcsub($row['total_debit'], $row['total_credit']), $openingBalance);
            $totalCash = bcadd($totalCash, $balance);
        }

        return $totalCash;
    }
}
