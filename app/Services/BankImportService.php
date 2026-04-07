<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\BankAccount;
use DoubleE\Models\BankTransaction;
use DoubleE\Models\AuditLog;

class BankImportService
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
     * Import transactions from a CSV file into a bank account.
     *
     * Steps:
     * 1. Create an import batch record
     * 2. Read the CSV file line by line
     * 3. Map columns using the provided mapping (date, description, amount/debit/credit, reference)
     * 4. Check for duplicates by date + amount + description, or fit_id if available
     * 5. Insert non-duplicate bank_transactions
     * 6. Update the batch with final counts
     * 7. Update the bank account's last_imported_at timestamp
     *
     * @param int    $bankAccountId Bank account to import into
     * @param string $filePath      Absolute path to the uploaded CSV file
     * @param array  $columnMapping Map of field names to CSV column indices:
     *                              'date' => int, 'description' => int, 'amount' => int,
     *                              'debit' => int (optional), 'credit' => int (optional),
     *                              'reference' => int (optional), 'fit_id' => int (optional)
     * @param int    $userId        ID of the importing user
     *
     * @return array{imported: int, duplicates: int, batch_id: int}
     *
     * @throws \RuntimeException If the bank account or file is invalid
     */
    public function importCsv(int $bankAccountId, string $filePath, array $columnMapping, int $userId): array
    {
        $bankAccount = $this->bankAccountModel->find($bankAccountId);
        if ($bankAccount === null) {
            throw new \RuntimeException("Bank account #{$bankAccountId} not found.");
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("CSV file not found or not readable: {$filePath}");
        }

        $imported = 0;
        $duplicates = 0;

        $batchId = (int) $this->db->transaction(function () use (
            $bankAccountId,
            $filePath,
            $columnMapping,
            $userId,
            &$imported,
            &$duplicates,
        ) {
            // 1. Create the import batch
            $fileName = basename($filePath);
            $this->db->exec(
                "INSERT INTO bank_import_batches (bank_account_id, file_name, file_format, imported_by)
                 VALUES (?, ?, 'csv', ?)",
                [$bankAccountId, $fileName, $userId]
            );
            $batchId = $this->db->lastInsertId();

            // 2. Read and process the CSV
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \RuntimeException("Unable to open CSV file: {$filePath}");
            }

            // Skip the header row
            fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // 3. Map columns
                $date = $this->parseDate(trim($row[$columnMapping['date']] ?? ''));
                $description = trim($row[$columnMapping['description']] ?? '');
                $reference = isset($columnMapping['reference']) ? trim($row[$columnMapping['reference']] ?? '') : null;
                $fitId = isset($columnMapping['fit_id']) ? trim($row[$columnMapping['fit_id']] ?? '') : null;

                // Determine the amount: either a single amount column, or separate debit/credit
                $amount = $this->parseAmount($row, $columnMapping);

                if ($date === null || $description === '' || $amount === null) {
                    continue; // Skip rows with missing required data
                }

                // Normalize empty strings to null
                $reference = ($reference !== '' && $reference !== null) ? $reference : null;
                $fitId = ($fitId !== '' && $fitId !== null) ? $fitId : null;

                // 4. Check for duplicates
                if ($this->isDuplicate($bankAccountId, $date, $amount, $description, $fitId)) {
                    $duplicates++;
                    continue;
                }

                // 5. Insert the bank transaction
                $this->db->exec(
                    "INSERT INTO bank_transactions
                        (bank_account_id, import_batch_id, transaction_date, description, reference, amount, fit_id, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'unmatched')",
                    [$bankAccountId, $batchId, $date, $description, $reference, $amount, $fitId]
                );
                $imported++;
            }

            fclose($handle);

            // 6. Update the batch with counts
            $this->db->exec(
                "UPDATE bank_import_batches
                 SET transaction_count = ?, duplicate_count = ?
                 WHERE id = ?",
                [$imported, $duplicates, $batchId]
            );

            // 7. Update last_imported_at on the bank account
            $this->db->exec(
                "UPDATE bank_accounts SET last_imported_at = NOW() WHERE id = ?",
                [$bankAccountId]
            );

            return $batchId;
        });

        AuditLog::log(
            'bank_import.csv',
            'bank_account',
            $bankAccountId,
            null,
            ['batch_id' => $batchId, 'imported' => $imported, 'duplicates' => $duplicates]
        );

        return [
            'imported'   => $imported,
            'duplicates' => $duplicates,
            'batch_id'   => $batchId,
        ];
    }

    /**
     * Read the first few rows of a CSV file and return column headers for the mapping UI.
     *
     * @return array{headers: string[], sample_rows: array[]}
     */
    public function getColumnSuggestions(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("CSV file not found or not readable: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open CSV file: {$filePath}");
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return ['headers' => [], 'sample_rows' => []];
        }

        $headers = array_map('trim', $headers);

        // Read up to 5 sample rows
        $sampleRows = [];
        $rowCount = 0;
        while ($rowCount < 5 && ($row = fgetcsv($handle)) !== false) {
            if (!empty(array_filter($row))) {
                $sampleRows[] = array_map('trim', $row);
                $rowCount++;
            }
        }

        fclose($handle);

        return [
            'headers'     => $headers,
            'sample_rows' => $sampleRows,
        ];
    }

    /**
     * Parse a date string into Y-m-d format, supporting common bank CSV formats.
     */
    private function parseDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        // Try common date formats
        $formats = ['Y-m-d', 'm/d/Y', 'm/d/y', 'd/m/Y', 'Y/m/d', 'M d, Y', 'n/j/Y', 'n/j/y'];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // Fallback: let PHP try to parse it
        try {
            $date = new \DateTimeImmutable($value);
            return $date->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Extract the transaction amount from a CSV row.
     *
     * Supports a single "amount" column or separate "debit" and "credit" columns.
     * Returns a positive number for deposits and negative for withdrawals.
     */
    private function parseAmount(array $row, array $columnMapping): ?string
    {
        if (isset($columnMapping['amount'])) {
            $raw = trim($row[$columnMapping['amount']] ?? '');
            return $this->cleanNumber($raw);
        }

        // Separate debit/credit columns
        if (isset($columnMapping['debit']) && isset($columnMapping['credit'])) {
            $debit = $this->cleanNumber(trim($row[$columnMapping['debit']] ?? ''));
            $credit = $this->cleanNumber(trim($row[$columnMapping['credit']] ?? ''));

            if ($credit !== null && bccomp($credit, '0.00', 2) > 0) {
                return $credit;
            }

            if ($debit !== null && bccomp($debit, '0.00', 2) > 0) {
                return '-' . $debit;
            }

            // Both are zero or empty
            if ($debit !== null) {
                return $debit;
            }
            if ($credit !== null) {
                return $credit;
            }
        }

        return null;
    }

    /**
     * Clean a numeric string by removing currency symbols, commas, and whitespace.
     * Handles parenthesized negatives like (123.45).
     */
    private function cleanNumber(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        // Remove currency symbols and whitespace
        $cleaned = preg_replace('/[\$\s,]/', '', $value);

        // Handle parenthesized negatives: (123.45) => -123.45
        if (preg_match('/^\((.+)\)$/', $cleaned, $m)) {
            $cleaned = '-' . $m[1];
        }

        if (!is_numeric($cleaned)) {
            return null;
        }

        return number_format((float) $cleaned, 2, '.', '');
    }

    /**
     * Check whether a transaction already exists for this bank account.
     *
     * Matches by fit_id first (if available), otherwise by date + amount + description.
     */
    private function isDuplicate(
        int $bankAccountId,
        string $date,
        string $amount,
        string $description,
        ?string $fitId,
    ): bool {
        // If we have a FIT ID, use it as the primary duplicate check
        if ($fitId !== null) {
            $count = $this->db->queryScalar(
                "SELECT COUNT(*) FROM bank_transactions
                 WHERE bank_account_id = ? AND fit_id = ?",
                [$bankAccountId, $fitId]
            );
            return (int) $count > 0;
        }

        // Fall back to date + amount + description
        $count = $this->db->queryScalar(
            "SELECT COUNT(*) FROM bank_transactions
             WHERE bank_account_id = ? AND transaction_date = ? AND amount = ? AND description = ?",
            [$bankAccountId, $date, $amount, $description]
        );

        return (int) $count > 0;
    }
}
