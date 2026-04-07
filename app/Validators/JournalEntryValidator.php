<?php

declare(strict_types=1);

namespace DoubleE\Validators;

use DoubleE\Models\Account;
use DoubleE\Models\FiscalPeriod;

class JournalEntryValidator
{
    private Account $accountModel;
    private FiscalPeriod $fiscalPeriodModel;

    public function __construct()
    {
        $this->accountModel = new Account();
        $this->fiscalPeriodModel = new FiscalPeriod();
    }

    /**
     * Validate a journal entry header and its lines.
     *
     * @param array $header Entry header data (entry_date, description, etc.)
     * @param array $lines  Array of line items (account_id, debit, credit)
     * @return array List of error messages; empty if valid
     */
    public function validate(array $header, array $lines): array
    {
        $errors = [];

        // --- Header validations ---

        if (empty($header['entry_date'])) {
            $errors[] = 'Entry date is required.';
        } elseif (!$this->isValidDate($header['entry_date'])) {
            $errors[] = 'Entry date must be a valid date (YYYY-MM-DD).';
        }

        if (empty($header['description'])) {
            $errors[] = 'Description is required.';
        } elseif (mb_strlen($header['description']) > 500) {
            $errors[] = 'Description must not exceed 500 characters.';
        }

        // --- Line validations ---

        if (count($lines) < 2) {
            $errors[] = 'A journal entry must have at least 2 lines.';
            return $errors; // No point validating individual lines
        }

        $totalDebit = '0.00';
        $totalCredit = '0.00';

        foreach ($lines as $index => $line) {
            $lineNum = $index + 1;

            // Account is required
            if (empty($line['account_id'])) {
                $errors[] = "Line {$lineNum}: Account is required.";
                continue;
            }

            // Account must exist, be active, and not be a header account
            $account = $this->accountModel->find((int) $line['account_id']);
            if ($account === null) {
                $errors[] = "Line {$lineNum}: Account does not exist.";
                continue;
            }

            if (empty($account['is_active'])) {
                $errors[] = "Line {$lineNum}: Account '{$account['name']}' is inactive.";
            }

            if (!empty($account['is_header'])) {
                $errors[] = "Line {$lineNum}: Cannot post to header account '{$account['name']}'.";
            }

            // Exactly one of debit/credit must be > 0
            $debit = $line['debit'] ?? '0.00';
            $credit = $line['credit'] ?? '0.00';

            $hasDebit = bccomp((string) $debit, '0.00', 2) > 0;
            $hasCredit = bccomp((string) $credit, '0.00', 2) > 0;

            if ($hasDebit && $hasCredit) {
                $errors[] = "Line {$lineNum}: A line cannot have both a debit and a credit.";
            } elseif (!$hasDebit && !$hasCredit) {
                $errors[] = "Line {$lineNum}: A line must have either a debit or a credit amount.";
            }

            $totalDebit = bcadd($totalDebit, (string) $debit, 2);
            $totalCredit = bcadd($totalCredit, (string) $credit, 2);
        }

        // Total debits must equal total credits
        if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
            $errors[] = "Total debits ({$totalDebit}) must equal total credits ({$totalCredit}).";
        }

        // --- Fiscal period validation ---

        if (!empty($header['entry_date']) && $this->isValidDate($header['entry_date'])) {
            $period = $this->fiscalPeriodModel->findByDate($header['entry_date']);

            if ($period === null) {
                $errors[] = 'No fiscal period exists for the entry date.';
            } elseif ($period['status'] !== 'open') {
                $errors[] = 'The fiscal period for the entry date is not open.';
            }
        }

        return $errors;
    }

    /**
     * Check whether a string is a valid YYYY-MM-DD date.
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }
}
