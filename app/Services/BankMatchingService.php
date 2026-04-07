<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\BankAccount;
use DoubleE\Models\BankTransaction;
use DoubleE\Models\AuditLog;

class BankMatchingService
{
    private Database $db;
    private BankAccount $bankAccountModel;
    private BankTransaction $transactionModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->bankAccountModel = new BankAccount();
        $this->transactionModel = new BankTransaction();
    }

    /**
     * Automatically match unmatched bank transactions to existing journal entries.
     *
     * Matching criteria:
     * - The journal entry has a line on the bank's GL account
     * - The line amount matches the bank transaction amount (debit for positive, credit for negative)
     * - The entry date is within a configurable date window (default +/- 3 days)
     * - The journal entry is not already matched to another bank transaction
     *
     * @return int Number of transactions matched
     */
    public function autoMatch(int $bankAccountId): int
    {
        $bankAccount = $this->bankAccountModel->find($bankAccountId);
        if ($bankAccount === null) {
            throw new \RuntimeException("Bank account #{$bankAccountId} not found.");
        }

        $glAccountId = (int) $bankAccount['account_id'];
        $dateWindow = 3; // days

        // Get all unmatched bank transactions for this account
        $unmatched = $this->transactionModel->getUnmatched($bankAccountId);
        $matchedCount = 0;

        foreach ($unmatched as $txn) {
            $amount = $txn['amount'];
            $txnDate = $txn['transaction_date'];

            // Positive amount = money in (debit to bank account)
            // Negative amount = money out (credit to bank account)
            if (bccomp($amount, '0.00', 2) >= 0) {
                $amountColumn = 'jel.debit';
                $matchAmount = $amount;
            } else {
                $amountColumn = 'jel.credit';
                $matchAmount = bcmul($amount, '-1', 2);
            }

            // Find a matching journal entry line on this GL account
            // that is not already linked to a bank transaction
            $sql = "SELECT je.id AS journal_entry_id
                    FROM journal_entry_lines jel
                    INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                    WHERE jel.account_id = ?
                      AND {$amountColumn} = ?
                      AND je.status = 'posted'
                      AND je.entry_date BETWEEN DATE_SUB(?, INTERVAL ? DAY) AND DATE_ADD(?, INTERVAL ? DAY)
                      AND je.id NOT IN (
                          SELECT bt.journal_entry_id
                          FROM bank_transactions bt
                          WHERE bt.journal_entry_id IS NOT NULL
                      )
                    ORDER BY ABS(DATEDIFF(je.entry_date, ?)) ASC
                    LIMIT 1";

            $match = $this->db->queryOne($sql, [
                $glAccountId,
                $matchAmount,
                $txnDate,
                $dateWindow,
                $txnDate,
                $dateWindow,
                $txnDate,
            ]);

            if ($match !== null) {
                $now = date('Y-m-d H:i:s');
                $this->transactionModel->update((int) $txn['id'], [
                    'status'           => 'matched',
                    'journal_entry_id' => (int) $match['journal_entry_id'],
                    'matched_at'       => $now,
                ]);
                $matchedCount++;
            }
        }

        if ($matchedCount > 0) {
            AuditLog::log(
                'bank_matching.auto',
                'bank_account',
                $bankAccountId,
                null,
                ['matched_count' => $matchedCount]
            );
        }

        return $matchedCount;
    }

    /**
     * Manually link a bank transaction to an existing journal entry.
     *
     * @throws \RuntimeException If the transaction or journal entry is invalid
     */
    public function manualMatch(int $transactionId, int $journalEntryId, int $userId): void
    {
        $txn = $this->transactionModel->find($transactionId);
        if ($txn === null) {
            throw new \RuntimeException("Bank transaction #{$transactionId} not found.");
        }

        if ($txn['status'] !== 'unmatched') {
            throw new \RuntimeException(
                "Bank transaction #{$transactionId} cannot be matched. Current status: {$txn['status']}."
            );
        }

        // Verify the journal entry exists and is posted
        $je = $this->db->queryOne(
            "SELECT id, status FROM journal_entries WHERE id = ?",
            [$journalEntryId]
        );

        if ($je === null) {
            throw new \RuntimeException("Journal entry #{$journalEntryId} not found.");
        }

        if ($je['status'] !== 'posted') {
            throw new \RuntimeException(
                "Journal entry #{$journalEntryId} is not posted. Only posted entries can be matched."
            );
        }

        $now = date('Y-m-d H:i:s');
        $this->transactionModel->update($transactionId, [
            'status'           => 'matched',
            'journal_entry_id' => $journalEntryId,
            'matched_at'       => $now,
            'matched_by'       => $userId,
        ]);

        AuditLog::log(
            'bank_matching.manual',
            'bank_transaction',
            $transactionId,
            ['status' => 'unmatched'],
            ['status' => 'matched', 'journal_entry_id' => $journalEntryId, 'matched_by' => $userId]
        );
    }

    /**
     * Create a new journal entry from a bank transaction and link them.
     *
     * Uses the JournalEntryService to create and post the entry, then links
     * the bank transaction to it.
     *
     * @param int   $transactionId Bank transaction ID
     * @param array $jeData        Journal entry data: 'description', 'lines' (array of
     *                             account_id, debit, credit, description), 'reference' (optional)
     * @param int   $userId        ID of the user performing the action
     *
     * @throws \RuntimeException If the transaction is not in unmatched status
     */
    public function createAndMatch(int $transactionId, array $jeData, int $userId): void
    {
        $txn = $this->transactionModel->find($transactionId);
        if ($txn === null) {
            throw new \RuntimeException("Bank transaction #{$transactionId} not found.");
        }

        if ($txn['status'] !== 'unmatched') {
            throw new \RuntimeException(
                "Bank transaction #{$transactionId} cannot be matched. Current status: {$txn['status']}."
            );
        }

        $jeService = new JournalEntryService();

        $header = [
            'entry_date'  => $txn['transaction_date'],
            'description' => $jeData['description'] ?? $txn['description'],
            'reference'   => $jeData['reference'] ?? $txn['reference'],
        ];

        $journalEntryId = $jeService->createAndPost($header, $jeData['lines'], $userId);

        $now = date('Y-m-d H:i:s');
        $this->transactionModel->update($transactionId, [
            'status'           => 'matched',
            'journal_entry_id' => $journalEntryId,
            'matched_at'       => $now,
            'matched_by'       => $userId,
        ]);

        AuditLog::log(
            'bank_matching.create_and_match',
            'bank_transaction',
            $transactionId,
            ['status' => 'unmatched'],
            ['status' => 'matched', 'journal_entry_id' => $journalEntryId, 'matched_by' => $userId]
        );
    }

    /**
     * Mark a bank transaction as excluded (not expected to match any journal entry).
     */
    public function exclude(int $transactionId): void
    {
        $txn = $this->transactionModel->find($transactionId);
        if ($txn === null) {
            throw new \RuntimeException("Bank transaction #{$transactionId} not found.");
        }

        if ($txn['status'] !== 'unmatched') {
            throw new \RuntimeException(
                "Only unmatched transactions can be excluded. Current status: {$txn['status']}."
            );
        }

        $this->transactionModel->update($transactionId, [
            'status' => 'excluded',
        ]);

        AuditLog::log(
            'bank_matching.exclude',
            'bank_transaction',
            $transactionId,
            ['status' => 'unmatched'],
            ['status' => 'excluded']
        );
    }
}
