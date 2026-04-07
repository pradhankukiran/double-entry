<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\BankAccount;
use DoubleE\Models\BankReconciliation;
use DoubleE\Models\AuditLog;

class ReconciliationService
{
    private Database $db;
    private BankAccount $bankAccountModel;
    private BankReconciliation $reconciliationModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->bankAccountModel = new BankAccount();
        $this->reconciliationModel = new BankReconciliation();
    }

    /**
     * Start a new bank reconciliation.
     *
     * @param int    $bankAccountId   Bank account to reconcile
     * @param string $statementDate   Statement ending date (Y-m-d)
     * @param string $statementBalance Statement ending balance
     * @param int    $userId          ID of the user performing the reconciliation
     *
     * @return int The new reconciliation ID
     *
     * @throws \RuntimeException If there is already an in-progress reconciliation
     */
    public function start(int $bankAccountId, string $statementDate, string $statementBalance, int $userId): int
    {
        $bankAccount = $this->bankAccountModel->find($bankAccountId);
        if ($bankAccount === null) {
            throw new \RuntimeException("Bank account #{$bankAccountId} not found.");
        }

        // Check for an existing in-progress reconciliation
        $existing = $this->db->queryOne(
            "SELECT id FROM bank_reconciliations
             WHERE bank_account_id = ? AND status = 'in_progress'",
            [$bankAccountId]
        );

        if ($existing !== null) {
            throw new \RuntimeException(
                "Bank account already has an in-progress reconciliation (#{$existing['id']}). "
                . 'Complete or void it before starting a new one.'
            );
        }

        $reconciliationId = $this->reconciliationModel->create([
            'bank_account_id'  => $bankAccountId,
            'statement_date'   => $statementDate,
            'statement_balance' => $statementBalance,
            'reconciled_by'    => $userId,
        ]);

        AuditLog::log(
            'bank_reconciliation.started',
            'bank_reconciliation',
            $reconciliationId,
            null,
            [
                'bank_account_id'   => $bankAccountId,
                'statement_date'    => $statementDate,
                'statement_balance' => $statementBalance,
            ]
        );

        return $reconciliationId;
    }

    /**
     * Get all data needed for the reconciliation worksheet.
     *
     * Returns:
     * - reconciliation: the reconciliation record
     * - cleared: lines already marked as cleared in this reconciliation
     * - uncleared: journal entry lines for this bank account that are not yet reconciled
     *   (from posted entries on or before the statement date)
     *
     * @return array{reconciliation: array, cleared: array, uncleared: array}
     */
    public function getReconciliationData(int $reconciliationId): array
    {
        $reconciliation = $this->reconciliationModel->find($reconciliationId);
        if ($reconciliation === null) {
            throw new \RuntimeException("Reconciliation #{$reconciliationId} not found.");
        }

        $bankAccountId = (int) $reconciliation['bank_account_id'];
        $bankAccount = $this->bankAccountModel->find($bankAccountId);
        $glAccountId = (int) $bankAccount['account_id'];
        $statementDate = $reconciliation['statement_date'];

        // Get lines already cleared in this reconciliation
        $cleared = $this->db->query(
            "SELECT brl.*, jel.debit, jel.credit, jel.description AS line_description,
                    je.id AS journal_entry_id, je.entry_number, je.entry_date, je.description AS entry_description
             FROM bank_reconciliation_lines brl
             INNER JOIN journal_entry_lines jel ON jel.id = brl.journal_entry_line_id
             INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
             WHERE brl.reconciliation_id = ?
             ORDER BY je.entry_date, je.id",
            [$reconciliationId]
        );

        // Collect IDs of already-cleared lines to exclude them from the uncleared set
        $clearedJelIds = array_column($cleared, 'journal_entry_line_id');

        // Get all journal entry lines for this GL account that are:
        // - from posted entries on or before the statement date
        // - not already reconciled (not in a completed reconciliation)
        // - not already cleared in THIS reconciliation
        $sql = "SELECT jel.id AS journal_entry_line_id, jel.debit, jel.credit,
                       jel.description AS line_description,
                       je.id AS journal_entry_id, je.entry_number, je.entry_date,
                       je.description AS entry_description
                FROM journal_entry_lines jel
                INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                WHERE jel.account_id = ?
                  AND je.status = 'posted'
                  AND je.entry_date <= ?
                  AND jel.id NOT IN (
                      SELECT brl2.journal_entry_line_id
                      FROM bank_reconciliation_lines brl2
                      INNER JOIN bank_reconciliations br2 ON br2.id = brl2.reconciliation_id
                      WHERE br2.status = 'completed'
                  )";
        $params = [$glAccountId, $statementDate];

        // Also exclude items already cleared in this reconciliation
        if (!empty($clearedJelIds)) {
            $placeholders = implode(',', array_fill(0, count($clearedJelIds), '?'));
            $sql .= " AND jel.id NOT IN ({$placeholders})";
            $params = array_merge($params, $clearedJelIds);
        }

        $sql .= " ORDER BY je.entry_date, je.id, jel.id";
        $uncleared = $this->db->query($sql, $params);

        return [
            'reconciliation' => $reconciliation,
            'cleared'        => $cleared,
            'uncleared'      => $uncleared,
        ];
    }

    /**
     * Toggle a journal entry line as cleared or uncleared in a reconciliation.
     *
     * If the line is already cleared, it is removed. If not, it is added.
     *
     * @throws \RuntimeException If the reconciliation is not in_progress
     */
    public function toggleCleared(int $reconciliationId, int $jelId): void
    {
        $reconciliation = $this->reconciliationModel->find($reconciliationId);
        if ($reconciliation === null) {
            throw new \RuntimeException("Reconciliation #{$reconciliationId} not found.");
        }

        if ($reconciliation['status'] !== 'in_progress') {
            throw new \RuntimeException(
                'Cannot modify a reconciliation that is not in progress.'
            );
        }

        // Check if this line is already cleared
        $existing = $this->db->queryOne(
            "SELECT id FROM bank_reconciliation_lines
             WHERE reconciliation_id = ? AND journal_entry_line_id = ?",
            [$reconciliationId, $jelId]
        );

        if ($existing !== null) {
            // Remove it (unclear)
            $this->db->exec(
                "DELETE FROM bank_reconciliation_lines WHERE id = ?",
                [(int) $existing['id']]
            );
        } else {
            // Add it (clear)
            $this->db->exec(
                "INSERT INTO bank_reconciliation_lines (reconciliation_id, journal_entry_line_id, is_cleared)
                 VALUES (?, ?, 1)",
                [$reconciliationId, $jelId]
            );
        }

        // Recalculate the difference after toggling
        $difference = $this->calculateDifference($reconciliationId);
        $this->reconciliationModel->update($reconciliationId, [
            'difference' => $difference,
        ]);
    }

    /**
     * Complete a reconciliation.
     *
     * Verifies the difference is zero, marks the reconciliation as completed,
     * and updates the bank account's last_reconciled_at timestamp.
     *
     * @throws \RuntimeException If the difference is not zero
     */
    public function complete(int $reconciliationId, int $userId): void
    {
        $this->db->transaction(function () use ($reconciliationId, $userId) {
            $reconciliation = $this->reconciliationModel->find($reconciliationId);
            if ($reconciliation === null) {
                throw new \RuntimeException("Reconciliation #{$reconciliationId} not found.");
            }

            if ($reconciliation['status'] !== 'in_progress') {
                throw new \RuntimeException(
                    'Only in-progress reconciliations can be completed.'
                );
            }

            // Verify the difference is zero
            $difference = $this->calculateDifference($reconciliationId);

            if (bccomp($difference, '0.00', 2) !== 0) {
                throw new \RuntimeException(
                    "Cannot complete reconciliation: difference is {$difference}. It must be 0.00."
                );
            }

            // Calculate the reconciled balance (sum of cleared items)
            $reconciledBalance = $this->getClearedTotal($reconciliationId);

            $now = date('Y-m-d H:i:s');
            $this->reconciliationModel->update($reconciliationId, [
                'status'             => 'completed',
                'reconciled_balance' => $reconciledBalance,
                'difference'         => '0.00',
                'completed_at'       => $now,
            ]);

            // Mark all matched bank transactions for this account as reconciled
            $bankAccountId = (int) $reconciliation['bank_account_id'];
            $this->db->exec(
                "UPDATE bank_transactions
                 SET status = 'reconciled'
                 WHERE bank_account_id = ?
                   AND status = 'matched'
                   AND journal_entry_id IN (
                       SELECT je.id
                       FROM bank_reconciliation_lines brl
                       INNER JOIN journal_entry_lines jel ON jel.id = brl.journal_entry_line_id
                       INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                       WHERE brl.reconciliation_id = ?
                   )",
                [$bankAccountId, $reconciliationId]
            );

            // Update the bank account
            $this->db->exec(
                "UPDATE bank_accounts
                 SET last_reconciled_at = ?, current_balance = ?
                 WHERE id = ?",
                [$now, $reconciliation['statement_balance'], $bankAccountId]
            );
        });

        AuditLog::log(
            'bank_reconciliation.completed',
            'bank_reconciliation',
            $reconciliationId,
            ['status' => 'in_progress'],
            ['status' => 'completed', 'completed_by' => $userId]
        );
    }

    /**
     * Calculate the difference between the statement balance and the cleared items total.
     *
     * Formula: statement_balance - (beginning_balance + cleared_debits - cleared_credits)
     *
     * A result of 0.00 means the account is reconciled.
     */
    public function calculateDifference(int $reconciliationId): string
    {
        $reconciliation = $this->reconciliationModel->find($reconciliationId);
        if ($reconciliation === null) {
            throw new \RuntimeException("Reconciliation #{$reconciliationId} not found.");
        }

        $bankAccountId = (int) $reconciliation['bank_account_id'];
        $bankAccount = $this->bankAccountModel->find($bankAccountId);
        $glAccountId = (int) $bankAccount['account_id'];
        $statementBalance = $reconciliation['statement_balance'];

        // Get the beginning balance: sum of all items already reconciled in prior completed reconciliations
        // plus the account's opening balance
        $openingBalance = $this->db->queryScalar(
            "SELECT opening_balance FROM accounts WHERE id = ?",
            [$glAccountId]
        );
        $openingBalance = $openingBalance ?? '0.00';

        $priorReconciled = $this->db->queryOne(
            "SELECT COALESCE(SUM(jel.debit), 0) AS total_debit,
                    COALESCE(SUM(jel.credit), 0) AS total_credit
             FROM bank_reconciliation_lines brl
             INNER JOIN bank_reconciliations br ON br.id = brl.reconciliation_id
             INNER JOIN journal_entry_lines jel ON jel.id = brl.journal_entry_line_id
             WHERE br.bank_account_id = ?
               AND br.status = 'completed'",
            [$bankAccountId]
        );

        // Beginning balance = opening_balance + prior_debits - prior_credits
        $beginningBalance = bcadd(
            (string) $openingBalance,
            bcsub($priorReconciled['total_debit'], $priorReconciled['total_credit'], 2),
            2
        );

        // Cleared total from this reconciliation
        $clearedTotal = $this->getClearedTotal($reconciliationId);

        // GL balance through cleared items = beginning + cleared
        $glBalance = bcadd($beginningBalance, $clearedTotal, 2);

        // Difference = statement_balance - GL balance
        return bcsub((string) $statementBalance, $glBalance, 2);
    }

    /**
     * Get the net total of cleared items in a reconciliation (debits - credits).
     */
    private function getClearedTotal(int $reconciliationId): string
    {
        $result = $this->db->queryOne(
            "SELECT COALESCE(SUM(jel.debit), 0) AS total_debit,
                    COALESCE(SUM(jel.credit), 0) AS total_credit
             FROM bank_reconciliation_lines brl
             INNER JOIN journal_entry_lines jel ON jel.id = brl.journal_entry_line_id
             WHERE brl.reconciliation_id = ?",
            [$reconciliationId]
        );

        return bcsub($result['total_debit'], $result['total_credit'], 2);
    }
}
