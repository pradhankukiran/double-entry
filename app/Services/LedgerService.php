<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\Account;

class LedgerService
{
    private Database $db;
    private Account $accountModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->accountModel = new Account();
    }

    /**
     * Get all posted ledger entries for a single account with a running balance.
     *
     * Each row includes the journal entry context (entry_number, entry_date,
     * description, status) and a calculated running_balance that respects the
     * account's normal balance direction.
     *
     * @param int         $accountId The account to query
     * @param string|null $fromDate  Start of date range (inclusive), or null for no lower bound
     * @param string|null $toDate    End of date range (inclusive), or null for no upper bound
     *
     * @return array List of ledger rows with running_balance attached
     */
    public function getAccountLedger(int $accountId, ?string $fromDate = null, ?string $toDate = null): array
    {
        bcscale(2);

        // Determine the account's normal balance direction
        $normalBalance = $this->getNormalBalance($accountId);
        $openingBalance = $this->getOpeningBalance($accountId);

        // Build the query with optional date filters
        $sql = "SELECT jel.id, jel.account_id, jel.debit, jel.credit, jel.description,
                       jel.line_number,
                       je.id AS journal_entry_id, je.entry_number, je.entry_date,
                       je.description AS entry_description, je.status
                FROM journal_entry_lines jel
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

        $sql .= " ORDER BY je.entry_date ASC, je.id ASC";

        $rows = $this->db->query($sql, $params);

        // If a from-date is set, calculate the balance carried forward up to that date
        if ($fromDate !== null) {
            $openingBalance = $this->getAccountBalance($accountId, date('Y-m-d', strtotime($fromDate) - 86400));
        }

        // Attach a running balance to each row
        $runningBalance = $openingBalance;

        foreach ($rows as &$row) {
            if ($normalBalance === 'debit') {
                $runningBalance = bcsub(bcadd($runningBalance, $row['debit']), $row['credit']);
            } else {
                $runningBalance = bcsub(bcadd($runningBalance, $row['credit']), $row['debit']);
            }

            $row['running_balance'] = $runningBalance;
        }

        return $rows;
    }

    /**
     * Get all posted journal entries with their lines in a date range.
     * Results are grouped by journal entry, each containing a 'lines' array.
     *
     * @param string|null $fromDate Start of date range (inclusive)
     * @param string|null $toDate   End of date range (inclusive)
     *
     * @return array List of journal entries, each with nested 'lines'
     */
    public function getGeneralLedger(?string $fromDate = null, ?string $toDate = null): array
    {
        $sql = "SELECT je.id, je.entry_number, je.entry_date, je.description,
                       je.reference, je.status, je.posted_at,
                       jel.id AS line_id, jel.account_id, jel.debit, jel.credit,
                       jel.description AS line_description, jel.line_number,
                       a.account_number, a.name AS account_name
                FROM journal_entries je
                INNER JOIN journal_entry_lines jel ON jel.journal_entry_id = je.id
                INNER JOIN accounts a ON a.id = jel.account_id
                WHERE je.status = 'posted'";

        $params = [];

        if ($fromDate !== null) {
            $sql .= " AND je.entry_date >= ?";
            $params[] = $fromDate;
        }

        if ($toDate !== null) {
            $sql .= " AND je.entry_date <= ?";
            $params[] = $toDate;
        }

        $sql .= " ORDER BY je.entry_date ASC, je.id ASC, jel.line_number ASC";

        $rows = $this->db->query($sql, $params);

        // Group flat rows into entries with nested lines
        $entries = [];
        $index = [];

        foreach ($rows as $row) {
            $entryId = (int) $row['id'];

            if (!isset($index[$entryId])) {
                $index[$entryId] = count($entries);
                $entries[] = [
                    'id'           => $entryId,
                    'entry_number' => $row['entry_number'],
                    'entry_date'   => $row['entry_date'],
                    'description'  => $row['description'],
                    'reference'    => $row['reference'],
                    'status'       => $row['status'],
                    'posted_at'    => $row['posted_at'],
                    'lines'        => [],
                ];
            }

            $entries[$index[$entryId]]['lines'][] = [
                'id'             => (int) $row['line_id'],
                'account_id'     => (int) $row['account_id'],
                'account_number' => $row['account_number'],
                'account_name'   => $row['account_name'],
                'debit'          => $row['debit'],
                'credit'         => $row['credit'],
                'description'    => $row['line_description'],
                'line_number'    => (int) $row['line_number'],
            ];
        }

        return $entries;
    }

    /**
     * Calculate the balance for a single account as of a given date.
     *
     * For debit-normal accounts: SUM(debit) - SUM(credit) + opening_balance
     * For credit-normal accounts: SUM(credit) - SUM(debit) + opening_balance
     *
     * @param int         $accountId The account to calculate
     * @param string|null $asOfDate  Calculate balance as of this date (inclusive), null for all time
     *
     * @return string The account balance with bcmath precision
     */
    public function getAccountBalance(int $accountId, ?string $asOfDate = null): string
    {
        bcscale(2);

        $normalBalance = $this->getNormalBalance($accountId);
        $openingBalance = $this->getOpeningBalance($accountId);

        $sql = "SELECT COALESCE(SUM(jel.debit), 0) AS total_debit,
                       COALESCE(SUM(jel.credit), 0) AS total_credit
                FROM journal_entry_lines jel
                INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                WHERE jel.account_id = ?
                  AND je.status = 'posted'";

        $params = [$accountId];

        if ($asOfDate !== null) {
            $sql .= " AND je.entry_date <= ?";
            $params[] = $asOfDate;
        }

        $totals = $this->db->queryOne($sql, $params);

        $totalDebit = $totals['total_debit'] ?? '0.00';
        $totalCredit = $totals['total_credit'] ?? '0.00';

        if ($normalBalance === 'debit') {
            return bcadd(bcsub($totalDebit, $totalCredit), $openingBalance);
        }

        return bcadd(bcsub($totalCredit, $totalDebit), $openingBalance);
    }

    /**
     * Build trial balance data: each account with its debit or credit balance.
     *
     * Returns an array of accounts. Each account has a 'debit_balance' or
     * 'credit_balance' populated (the other is '0.00') based on its normal
     * balance direction.
     *
     * @param string|null $asOfDate Calculate balances as of this date, null for all time
     *
     * @return array List of accounts with balance columns
     */
    public function getTrialBalanceData(?string $asOfDate = null): array
    {
        bcscale(2);

        $sql = "SELECT a.id, a.account_number, a.name, a.opening_balance,
                       at.name AS type_name, at.normal_balance,
                       COALESCE(SUM(jel.debit), 0) AS total_debit,
                       COALESCE(SUM(jel.credit), 0) AS total_credit
                FROM accounts a
                INNER JOIN account_types at ON at.id = a.account_type_id
                LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
                LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
                                            AND je.status = 'posted'";

        $params = [];

        if ($asOfDate !== null) {
            $sql .= " AND je.entry_date <= ?";
            $params[] = $asOfDate;
        }

        $sql .= " WHERE a.is_active = 1 AND a.is_header = 0
                   GROUP BY a.id, a.account_number, a.name, a.opening_balance,
                            at.name, at.normal_balance
                   ORDER BY a.account_number ASC";

        $rows = $this->db->query($sql, $params);

        $trialBalance = [];

        foreach ($rows as $row) {
            $openingBalance = $row['opening_balance'] ?? '0.00';
            $totalDebit = $row['total_debit'];
            $totalCredit = $row['total_credit'];

            if ($row['normal_balance'] === 'debit') {
                $balance = bcadd(bcsub($totalDebit, $totalCredit), $openingBalance);
                // A debit-normal account with a positive balance shows in the debit column
                if (bccomp($balance, '0.00') >= 0) {
                    $debitBalance = $balance;
                    $creditBalance = '0.00';
                } else {
                    // Negative balance on a debit-normal account goes to credit column
                    $debitBalance = '0.00';
                    $creditBalance = bcmul($balance, '-1');
                }
            } else {
                $balance = bcadd(bcsub($totalCredit, $totalDebit), $openingBalance);
                if (bccomp($balance, '0.00') >= 0) {
                    $debitBalance = '0.00';
                    $creditBalance = $balance;
                } else {
                    $debitBalance = bcmul($balance, '-1');
                    $creditBalance = '0.00';
                }
            }

            $trialBalance[] = [
                'id'             => (int) $row['id'],
                'account_number' => $row['account_number'],
                'name'           => $row['name'],
                'type_name'      => $row['type_name'],
                'debit_balance'  => $debitBalance,
                'credit_balance' => $creditBalance,
            ];
        }

        return $trialBalance;
    }

    /**
     * Get the normal balance direction ('debit' or 'credit') for an account.
     */
    private function getNormalBalance(int $accountId): string
    {
        $row = $this->db->queryOne(
            "SELECT at.normal_balance
             FROM accounts a
             INNER JOIN account_types at ON at.id = a.account_type_id
             WHERE a.id = ?",
            [$accountId]
        );

        if ($row === null) {
            throw new \RuntimeException("Account #{$accountId} not found.");
        }

        return $row['normal_balance'];
    }

    /**
     * Get the opening balance for an account, defaulting to '0.00'.
     */
    private function getOpeningBalance(int $accountId): string
    {
        $account = $this->accountModel->find($accountId);
        if ($account === null) {
            throw new \RuntimeException("Account #{$accountId} not found.");
        }

        return $account['opening_balance'] ?? '0.00';
    }
}
