<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\AccountType;

class ReportService
{
    private Database $db;
    private LedgerService $ledgerService;
    private AccountType $accountTypeModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ledgerService = new LedgerService();
        $this->accountTypeModel = new AccountType();
    }

    /**
     * Generate a trial balance report as of a given date.
     *
     * @param string|null $asOfDate Calculate balances as of this date (inclusive), null for all time
     *
     * @return array Trial balance data with totals and balanced flag
     */
    public function generateTrialBalance(?string $asOfDate = null): array
    {
        bcscale(2);

        $accounts = $this->ledgerService->getTrialBalanceData($asOfDate);

        $totalDebits = '0.00';
        $totalCredits = '0.00';

        foreach ($accounts as $account) {
            $totalDebits = bcadd($totalDebits, $account['debit_balance']);
            $totalCredits = bcadd($totalCredits, $account['credit_balance']);
        }

        return [
            'accounts'      => $accounts,
            'total_debits'  => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced'   => bccomp($totalDebits, $totalCredits) === 0,
            'as_of_date'    => $asOfDate ?? date('Y-m-d'),
        ];
    }

    /**
     * Generate a balance sheet report as of a given date.
     *
     * Assets = Liabilities + Equity (including net income from current period).
     *
     * @param string $asOfDate Report date (inclusive)
     *
     * @return array Balance sheet data grouped by section with totals
     */
    public function generateBalanceSheet(string $asOfDate): array
    {
        bcscale(2);

        $assetType     = $this->accountTypeModel->findByCode('ASSET');
        $liabilityType = $this->accountTypeModel->findByCode('LIABILITY');
        $equityType    = $this->accountTypeModel->findByCode('EQUITY');

        // Fetch balances for each section
        $assets      = $this->getBalanceSheetSection($assetType['id'], 'debit', $asOfDate);
        $liabilities = $this->getBalanceSheetSection($liabilityType['id'], 'credit', $asOfDate);
        $equity      = $this->getBalanceSheetSection($equityType['id'], 'credit', $asOfDate);

        // Calculate net income (Revenue - Expenses) for the fiscal year up to asOfDate.
        // Use Jan 1 of the asOfDate year as the start of the fiscal year.
        $fiscalYearStart = date('Y', strtotime($asOfDate)) . '-01-01';
        $incomeStatement = $this->generateIncomeStatement($fiscalYearStart, $asOfDate);
        $netIncome = $incomeStatement['net_income'];

        // Add net income as a virtual line item in the equity section
        if (bccomp($netIncome, '0.00') !== 0) {
            $equity['accounts'][] = [
                'id'              => 0,
                'account_number'  => '',
                'name'            => 'Net Income (Current Period)',
                'subtype_name'    => 'Retained Earnings',
                'balance'         => $netIncome,
                'is_virtual'      => true,
            ];
            $equity['total'] = bcadd($equity['total'], $netIncome);
        }

        $totalLiabilitiesEquity = bcadd($liabilities['total'], $equity['total']);

        return [
            'assets'                   => $assets,
            'liabilities'              => $liabilities,
            'equity'                   => $equity,
            'total_liabilities_equity' => $totalLiabilitiesEquity,
            'is_balanced'              => bccomp($assets['total'], $totalLiabilitiesEquity) === 0,
            'net_income'               => $netIncome,
            'as_of_date'               => $asOfDate,
        ];
    }

    /**
     * Generate an income statement for a date range.
     *
     * Net Income = Total Revenue - Total Expenses.
     *
     * @param string $fromDate Start of period (inclusive)
     * @param string $toDate   End of period (inclusive)
     *
     * @return array Income statement data with revenue, expenses, and net income
     */
    public function generateIncomeStatement(string $fromDate, string $toDate): array
    {
        bcscale(2);

        $revenueType = $this->accountTypeModel->findByCode('REVENUE');
        $expenseType = $this->accountTypeModel->findByCode('EXPENSE');

        $revenue  = $this->getIncomeStatementSection($revenueType['id'], 'credit', $fromDate, $toDate);
        $expenses = $this->getIncomeStatementSection($expenseType['id'], 'debit', $fromDate, $toDate);

        $netIncome = bcsub($revenue['total'], $expenses['total']);

        return [
            'revenue'    => $revenue,
            'expenses'   => $expenses,
            'net_income' => $netIncome,
            'from_date'  => $fromDate,
            'to_date'    => $toDate,
        ];
    }

    /**
     * Generate a simplified cash flow statement (direct method).
     *
     * Classifies cash movements into operating, investing, and financing activities
     * based on the contra-account type in each journal entry.
     *
     * @param string $fromDate Start of period (inclusive)
     * @param string $toDate   End of period (inclusive)
     *
     * @return array Cash flow data with three sections plus beginning/ending balances
     */
    public function generateCashFlowStatement(string $fromDate, string $toDate): array
    {
        bcscale(2);

        // Get beginning cash balance (day before fromDate)
        $dayBefore = date('Y-m-d', strtotime($fromDate) - 86400);
        $beginningCash = $this->getCashBalance($dayBefore);
        $endingCash = $this->getCashBalance($toDate);

        // Get all journal entry lines that hit cash/bank accounts within the date range.
        // For each cash-side line, look at the contra-account to classify the activity.
        $cashLines = $this->getCashFlowEntries($fromDate, $toDate);

        $operating = ['items' => [], 'total' => '0.00'];
        $investing = ['items' => [], 'total' => '0.00'];
        $financing = ['items' => [], 'total' => '0.00'];

        foreach ($cashLines as $line) {
            $contraTypeCode = $line['contra_type_code'];
            $contraSubtypeCode = $line['contra_subtype_code'] ?? '';

            // Cash amount: debit increases cash, credit decreases cash
            $cashAmount = bcsub($line['cash_debit'], $line['cash_credit']);

            $item = [
                'description'    => $line['entry_description'],
                'contra_account' => $line['contra_account_name'],
                'amount'         => $cashAmount,
                'entry_date'     => $line['entry_date'],
            ];

            // Classify based on the contra-account type
            if ($this->isInvestingActivity($contraTypeCode, $contraSubtypeCode)) {
                $investing['items'][] = $item;
                $investing['total'] = bcadd($investing['total'], $cashAmount);
            } elseif ($this->isFinancingActivity($contraTypeCode, $contraSubtypeCode)) {
                $financing['items'][] = $item;
                $financing['total'] = bcadd($financing['total'], $cashAmount);
            } else {
                // Default: operating (revenue, expenses, current assets, current liabilities)
                $operating['items'][] = $item;
                $operating['total'] = bcadd($operating['total'], $cashAmount);
            }
        }

        $netChange = bcsub($endingCash, $beginningCash);

        return [
            'operating'      => $operating,
            'investing'      => $investing,
            'financing'      => $financing,
            'net_change'     => $netChange,
            'beginning_cash' => $beginningCash,
            'ending_cash'    => $endingCash,
            'from_date'      => $fromDate,
            'to_date'        => $toDate,
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Get balance sheet section accounts with balances grouped by subtype.
     *
     * @param int    $typeId        Account type ID
     * @param string $normalBalance 'debit' or 'credit'
     * @param string $asOfDate      Report date
     *
     * @return array ['accounts' => [...], 'total' => string]
     */
    private function getBalanceSheetSection(int $typeId, string $normalBalance, string $asOfDate): array
    {
        $sql = "SELECT a.id, a.account_number, a.name, a.opening_balance,
                       ast.name AS subtype_name, ast.display_order AS subtype_order,
                       COALESCE(SUM(jel.debit), 0) AS total_debit,
                       COALESCE(SUM(jel.credit), 0) AS total_credit
                FROM accounts a
                LEFT JOIN account_subtypes ast ON ast.id = a.account_subtype_id
                LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
                LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
                                            AND je.status = 'posted'
                                            AND je.entry_date <= ?
                WHERE a.account_type_id = ?
                  AND a.is_active = 1
                  AND a.is_header = 0
                GROUP BY a.id, a.account_number, a.name, a.opening_balance,
                         ast.name, ast.display_order
                ORDER BY ast.display_order ASC, a.account_number ASC";

        $rows = $this->db->query($sql, [$asOfDate, $typeId]);

        $accounts = [];
        $sectionTotal = '0.00';

        foreach ($rows as $row) {
            $openingBalance = $row['opening_balance'] ?? '0.00';

            if ($normalBalance === 'debit') {
                $balance = bcadd(bcsub($row['total_debit'], $row['total_credit']), $openingBalance);
            } else {
                $balance = bcadd(bcsub($row['total_credit'], $row['total_debit']), $openingBalance);
            }

            // Skip zero-balance accounts
            if (bccomp($balance, '0.00') === 0) {
                continue;
            }

            $accounts[] = [
                'id'             => (int) $row['id'],
                'account_number' => $row['account_number'],
                'name'           => $row['name'],
                'subtype_name'   => $row['subtype_name'] ?? 'Uncategorized',
                'balance'        => $balance,
                'is_virtual'     => false,
            ];

            $sectionTotal = bcadd($sectionTotal, $balance);
        }

        return [
            'accounts' => $accounts,
            'total'    => $sectionTotal,
        ];
    }

    /**
     * Get income statement section (revenue or expenses) for a date range, grouped by subtype.
     *
     * Revenue: SUM(credit - debit) per account in the date range.
     * Expense: SUM(debit - credit) per account in the date range.
     *
     * @param int    $typeId        Account type ID
     * @param string $normalBalance 'debit' for expenses, 'credit' for revenue
     * @param string $fromDate      Period start (inclusive)
     * @param string $toDate        Period end (inclusive)
     *
     * @return array ['accounts' => [...], 'total' => string]
     */
    private function getIncomeStatementSection(int $typeId, string $normalBalance, string $fromDate, string $toDate): array
    {
        $sql = "SELECT a.id, a.account_number, a.name,
                       ast.name AS subtype_name, ast.display_order AS subtype_order,
                       COALESCE(SUM(jel.debit), 0) AS total_debit,
                       COALESCE(SUM(jel.credit), 0) AS total_credit
                FROM accounts a
                LEFT JOIN account_subtypes ast ON ast.id = a.account_subtype_id
                LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
                LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
                                            AND je.status = 'posted'
                                            AND je.entry_date >= ?
                                            AND je.entry_date <= ?
                WHERE a.account_type_id = ?
                  AND a.is_active = 1
                  AND a.is_header = 0
                GROUP BY a.id, a.account_number, a.name,
                         ast.name, ast.display_order
                ORDER BY ast.display_order ASC, a.account_number ASC";

        $rows = $this->db->query($sql, [$fromDate, $toDate, $typeId]);

        $accounts = [];
        $sectionTotal = '0.00';

        foreach ($rows as $row) {
            if ($normalBalance === 'debit') {
                // Expense accounts: debit - credit
                $amount = bcsub($row['total_debit'], $row['total_credit']);
            } else {
                // Revenue accounts: credit - debit
                $amount = bcsub($row['total_credit'], $row['total_debit']);
            }

            // Skip accounts with no activity
            if (bccomp($amount, '0.00') === 0) {
                continue;
            }

            $accounts[] = [
                'id'             => (int) $row['id'],
                'account_number' => $row['account_number'],
                'name'           => $row['name'],
                'subtype_name'   => $row['subtype_name'] ?? 'Uncategorized',
                'amount'         => $amount,
            ];

            $sectionTotal = bcadd($sectionTotal, $amount);
        }

        return [
            'accounts' => $accounts,
            'total'    => $sectionTotal,
        ];
    }

    /**
     * Get the total cash balance across all bank/cash accounts as of a date.
     *
     * Uses the is_bank_account flag on the accounts table to identify cash accounts.
     *
     * @param string $asOfDate Balance date (inclusive)
     *
     * @return string Total cash balance
     */
    private function getCashBalance(string $asOfDate): string
    {
        $sql = "SELECT a.id, a.opening_balance,
                       COALESCE(SUM(jel.debit), 0) AS total_debit,
                       COALESCE(SUM(jel.credit), 0) AS total_credit
                FROM accounts a
                LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
                LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
                                            AND je.status = 'posted'
                                            AND je.entry_date <= ?
                WHERE a.is_bank_account = 1
                  AND a.is_active = 1
                  AND a.is_header = 0
                GROUP BY a.id, a.opening_balance";

        $rows = $this->db->query($sql, [$asOfDate]);

        $totalCash = '0.00';

        foreach ($rows as $row) {
            $openingBalance = $row['opening_balance'] ?? '0.00';
            // Cash accounts are debit-normal (assets)
            $balance = bcadd(bcsub($row['total_debit'], $row['total_credit']), $openingBalance);
            $totalCash = bcadd($totalCash, $balance);
        }

        return $totalCash;
    }

    /**
     * Get cash flow entries: for every posted journal entry line that hits a cash/bank
     * account in the date range, pair it with the contra-account information.
     *
     * Uses a self-join on journal_entry_lines to find the contra side of each cash entry.
     *
     * @param string $fromDate Period start (inclusive)
     * @param string $toDate   Period end (inclusive)
     *
     * @return array Rows with cash amounts and contra-account classification data
     */
    private function getCashFlowEntries(string $fromDate, string $toDate): array
    {
        $sql = "SELECT
                    cash_line.debit AS cash_debit,
                    cash_line.credit AS cash_credit,
                    je.entry_date,
                    je.description AS entry_description,
                    contra_acct.name AS contra_account_name,
                    contra_type.code AS contra_type_code,
                    contra_sub.code AS contra_subtype_code
                FROM journal_entry_lines cash_line
                INNER JOIN journal_entries je ON je.id = cash_line.journal_entry_id
                INNER JOIN accounts cash_acct ON cash_acct.id = cash_line.account_id
                INNER JOIN journal_entry_lines contra_line
                    ON contra_line.journal_entry_id = cash_line.journal_entry_id
                    AND contra_line.id != cash_line.id
                INNER JOIN accounts contra_acct ON contra_acct.id = contra_line.account_id
                INNER JOIN account_types contra_type ON contra_type.id = contra_acct.account_type_id
                LEFT JOIN account_subtypes contra_sub ON contra_sub.id = contra_acct.account_subtype_id
                WHERE cash_acct.is_bank_account = 1
                  AND je.status = 'posted'
                  AND je.entry_date >= ?
                  AND je.entry_date <= ?
                ORDER BY je.entry_date ASC, je.id ASC";

        return $this->db->query($sql, [$fromDate, $toDate]);
    }

    /**
     * Determine if a contra-account type/subtype represents an investing activity.
     * Investing = fixed assets and other long-term assets.
     */
    private function isInvestingActivity(string $typeCode, string $subtypeCode): bool
    {
        if ($typeCode === 'ASSET' && in_array($subtypeCode, ['FIXED_ASSET', 'OTHER_ASSET'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a contra-account type/subtype represents a financing activity.
     * Financing = equity accounts and long-term liabilities.
     */
    private function isFinancingActivity(string $typeCode, string $subtypeCode): bool
    {
        if ($typeCode === 'EQUITY') {
            return true;
        }

        if ($typeCode === 'LIABILITY' && $subtypeCode === 'LONG_TERM_LIABILITY') {
            return true;
        }

        return false;
    }
}
