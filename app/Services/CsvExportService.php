<?php

declare(strict_types=1);

namespace DoubleE\Services;

class CsvExportService
{
    /**
     * Stream a CSV file download to the browser.
     *
     * Sets appropriate headers, writes the header row and all data rows
     * using fputcsv, then terminates the script.
     *
     * @param string   $filename The download filename (e.g. "accounts.csv")
     * @param string[] $headers  Column headers for the first row
     * @param array[]  $rows     Data rows (each an indexed array matching headers)
     */
    public function export(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, $headers);

        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
