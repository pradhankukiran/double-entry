<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\JournalEntry;
use DoubleE\Models\JournalEntryLine;
use DoubleE\Models\FiscalPeriod;
use DoubleE\Models\AuditLog;
use DoubleE\Validators\JournalEntryValidator;
use DoubleE\Exceptions\ValidationException;
use DoubleE\Exceptions\UnbalancedEntryException;

class JournalEntryService
{
    private Database $db;
    private JournalEntry $entryModel;
    private JournalEntryLine $lineModel;
    private FiscalPeriod $fiscalPeriodModel;
    private JournalEntryValidator $validator;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->entryModel = new JournalEntry();
        $this->lineModel = new JournalEntryLine();
        $this->fiscalPeriodModel = new FiscalPeriod();
        $this->validator = new JournalEntryValidator();
    }

    /**
     * Create a new journal entry with lines in a single transaction.
     *
     * Steps performed atomically:
     * 1. Validate header and lines via JournalEntryValidator
     * 2. Resolve the fiscal period for the entry date
     * 3. Generate the next sequential entry number
     * 4. INSERT journal entry header (status = 'draft')
     * 5. INSERT all journal entry lines
     * 6. Verify debits == credits; rollback if unbalanced
     * 7. Commit the transaction
     * 8. Write an audit log entry
     *
     * @param array $header Entry header data (entry_date, description, reference, etc.)
     * @param array $lines  Array of line items (account_id, debit, credit, description)
     * @param int   $userId ID of the user creating the entry
     *
     * @return int The newly created journal entry ID
     *
     * @throws ValidationException      If header or lines fail validation
     * @throws UnbalancedEntryException If total debits do not equal total credits
     * @throws \RuntimeException        If no fiscal period covers the entry date
     */
    public function create(array $header, array $lines, int $userId): int
    {
        // Validate before opening a transaction
        $errors = $this->validator->validate($header, $lines);
        if (!empty($errors)) {
            throw new ValidationException('Journal entry validation failed.', $errors);
        }

        $entryId = (int) $this->db->transaction(function () use ($header, $lines, $userId) {
            // Find the fiscal period for the entry date
            $period = $this->fiscalPeriodModel->findByDate($header['entry_date']);
            if ($period === null) {
                throw new \RuntimeException(
                    "No fiscal period found for date {$header['entry_date']}."
                );
            }

            if ($period['status'] !== 'open') {
                throw new \RuntimeException(
                    "Fiscal period '{$period['name']}' is not open for posting."
                );
            }

            // Generate the next sequential entry number
            $entryNumber = $this->entryModel->getNextEntryNumber();

            // Insert the journal entry header
            $entryId = $this->entryModel->create([
                'entry_number'     => $entryNumber,
                'entry_date'       => $header['entry_date'],
                'description'      => $header['description'] ?? '',
                'reference'        => $header['reference'] ?? null,
                'fiscal_period_id' => (int) $period['id'],
                'status'           => 'draft',
                'created_by'       => $userId,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            // Insert all journal entry lines
            $lineRecords = [];
            foreach ($lines as $index => $line) {
                $lineRecords[] = [
                    'journal_entry_id' => $entryId,
                    'account_id'       => (int) $line['account_id'],
                    'debit'            => $line['debit'] ?? '0.00',
                    'credit'           => $line['credit'] ?? '0.00',
                    'description'      => $line['description'] ?? '',
                    'line_number'      => $index + 1,
                ];
            }
            $this->lineModel->createMany($lineRecords);

            // Verify the entry balances: total debits must equal total credits
            bcscale(2);
            $balanceCheck = $this->db->queryOne(
                "SELECT SUM(debit) AS total_debit, SUM(credit) AS total_credit
                 FROM journal_entry_lines
                 WHERE journal_entry_id = ?",
                [$entryId]
            );

            $totalDebit = $balanceCheck['total_debit'] ?? '0.00';
            $totalCredit = $balanceCheck['total_credit'] ?? '0.00';

            if (bccomp($totalDebit, $totalCredit) !== 0) {
                throw new UnbalancedEntryException(
                    "Entry is unbalanced: debits ({$totalDebit}) do not equal credits ({$totalCredit})."
                );
            }

            return $entryId;
        });

        // Audit log after successful commit
        AuditLog::log(
            'journal_entry.created',
            'journal_entry',
            $entryId,
            null,
            array_merge($header, ['lines_count' => count($lines)])
        );

        return $entryId;
    }

    /**
     * Post a draft journal entry, making it part of the permanent ledger.
     *
     * @throws \RuntimeException If the entry is not in draft status or period is closed
     */
    public function post(int $entryId, int $userId): void
    {
        $this->db->transaction(function () use ($entryId, $userId) {
            $entry = $this->entryModel->find($entryId);
            if ($entry === null) {
                throw new \RuntimeException("Journal entry #{$entryId} not found.");
            }

            if ($entry['status'] !== 'draft') {
                throw new \RuntimeException(
                    "Only draft entries can be posted. Current status: {$entry['status']}."
                );
            }

            // Verify the fiscal period is still open
            $period = $this->fiscalPeriodModel->find((int) $entry['fiscal_period_id']);
            if ($period === null || $period['status'] !== 'open') {
                throw new \RuntimeException(
                    'Cannot post entry: the fiscal period is no longer open.'
                );
            }

            $now = date('Y-m-d H:i:s');
            $this->entryModel->update($entryId, [
                'status'    => 'posted',
                'posted_at' => $now,
                'posted_by' => $userId,
            ]);
        });

        AuditLog::log(
            'journal_entry.posted',
            'journal_entry',
            $entryId,
            ['status' => 'draft'],
            ['status' => 'posted', 'posted_by' => $userId]
        );
    }

    /**
     * Void a posted journal entry by creating a reversing entry.
     *
     * The reversing entry swaps debits and credits from the original and is
     * posted immediately. The original entry is marked as voided with a link
     * to the reversing entry.
     *
     * @param int    $entryId ID of the entry to void
     * @param int    $userId  ID of the user performing the void
     * @param string $reason  Reason for voiding
     *
     * @return int The ID of the reversing journal entry
     *
     * @throws \RuntimeException If the entry is not in posted status
     */
    public function void(int $entryId, int $userId, string $reason): int
    {
        $reversingEntryId = (int) $this->db->transaction(function () use ($entryId, $userId, $reason) {
            $entry = $this->entryModel->getWithLines($entryId);
            if ($entry === null) {
                throw new \RuntimeException("Journal entry #{$entryId} not found.");
            }

            if ($entry['status'] !== 'posted') {
                throw new \RuntimeException(
                    "Only posted entries can be voided. Current status: {$entry['status']}."
                );
            }

            // Fetch the original lines
            $originalLines = $this->lineModel->getByEntry($entryId);

            // Build reversing lines: swap debits and credits
            $reversingLines = [];
            foreach ($originalLines as $line) {
                $reversingLines[] = [
                    'account_id'  => (int) $line['account_id'],
                    'debit'       => $line['credit'],
                    'credit'      => $line['debit'],
                    'description' => $line['description'] ?? '',
                ];
            }

            // Find fiscal period for today's date (the reversal date)
            $reversalDate = date('Y-m-d');
            $period = $this->fiscalPeriodModel->findByDate($reversalDate);
            if ($period === null) {
                throw new \RuntimeException(
                    "No fiscal period found for reversal date {$reversalDate}."
                );
            }

            if ($period['status'] !== 'open') {
                throw new \RuntimeException(
                    "Fiscal period '{$period['name']}' is not open for the reversing entry."
                );
            }

            $reversingNumber = $this->entryModel->getNextEntryNumber();
            $now = date('Y-m-d H:i:s');

            // Create the reversing entry and post it immediately
            $reversingEntryId = $this->entryModel->create([
                'entry_number'     => $reversingNumber,
                'entry_date'       => $reversalDate,
                'description'      => "Reversal of {$entry['entry_number']}: {$reason}",
                'reference'        => $entry['reference'] ?? null,
                'fiscal_period_id' => (int) $period['id'],
                'status'           => 'posted',
                'created_by'       => $userId,
                'posted_at'        => $now,
                'posted_by'        => $userId,
                'created_at'       => $now,
            ]);

            // Insert the reversed lines
            $lineRecords = [];
            foreach ($reversingLines as $index => $line) {
                $lineRecords[] = [
                    'journal_entry_id' => $reversingEntryId,
                    'account_id'       => (int) $line['account_id'],
                    'debit'            => $line['debit'],
                    'credit'           => $line['credit'],
                    'description'      => $line['description'],
                    'line_number'      => $index + 1,
                ];
            }
            $this->lineModel->createMany($lineRecords);

            // Mark the original entry as voided
            $this->entryModel->update($entryId, [
                'status'             => 'voided',
                'voided_at'          => $now,
                'voided_by'          => $userId,
                'void_reason'        => $reason,
                'reversing_entry_id' => $reversingEntryId,
            ]);

            return $reversingEntryId;
        });

        AuditLog::log(
            'journal_entry.voided',
            'journal_entry',
            $entryId,
            ['status' => 'posted'],
            [
                'status'             => 'voided',
                'void_reason'        => $reason,
                'reversing_entry_id' => $reversingEntryId,
                'voided_by'          => $userId,
            ]
        );

        return $reversingEntryId;
    }

    /**
     * Create and immediately post a journal entry in one operation.
     * Used by invoice/payment systems that produce already-approved entries.
     *
     * @param array $header Entry header data (entry_date, description, reference, etc.)
     * @param array $lines  Array of line items (account_id, debit, credit, description)
     * @param int   $userId ID of the user creating the entry
     *
     * @return int The newly created and posted journal entry ID
     */
    public function createAndPost(array $header, array $lines, int $userId): int
    {
        $entryId = $this->create($header, $lines, $userId);
        $this->post($entryId, $userId);

        return $entryId;
    }
}
