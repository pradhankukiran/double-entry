<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\TaxRate;
use DoubleE\Models\TaxGroup;

class TaxService
{
    private Database $db;
    private TaxRate $taxRateModel;
    private TaxGroup $taxGroupModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->taxRateModel = new TaxRate();
        $this->taxGroupModel = new TaxGroup();
    }

    /**
     * Calculate tax for a given amount using a single tax rate.
     *
     * @param string $amount    The taxable amount (e.g. '100.00')
     * @param int    $taxRateId The tax rate record ID
     *
     * @return string The calculated tax amount rounded to 2 decimal places
     *
     * @throws \RuntimeException If the tax rate is not found
     */
    public function calculateTax(string $amount, int $taxRateId): string
    {
        $taxRate = $this->taxRateModel->find($taxRateId);

        if ($taxRate === null) {
            throw new \RuntimeException("Tax rate #{$taxRateId} not found.");
        }

        $rate = bcdiv((string) $taxRate['rate'], '100', 6);

        return bcmul($amount, $rate, 2);
    }

    /**
     * Calculate tax for a given amount using a tax group (compound rates).
     *
     * Each rate in the group is applied in order. Rates are applied to the
     * original amount (not compounded on previous tax) unless the group is
     * configured otherwise.
     *
     * @param string $amount     The taxable amount
     * @param int    $taxGroupId The tax group record ID
     *
     * @return array ['rates' => [['name' => ..., 'rate' => ..., 'tax' => ...], ...], 'total_tax' => string]
     *
     * @throws \RuntimeException If the tax group is not found
     */
    public function calculateGroupTax(string $amount, int $taxGroupId): array
    {
        $group = $this->taxGroupModel->getWithRates($taxGroupId);

        if ($group === null) {
            throw new \RuntimeException("Tax group #{$taxGroupId} not found.");
        }

        $rates = [];
        $totalTax = '0.00';

        foreach ($group['rates'] as $taxRate) {
            $rate = bcdiv((string) $taxRate['rate'], '100', 6);
            $tax = bcmul($amount, $rate, 2);

            $rates[] = [
                'tax_rate_id' => (int) $taxRate['id'],
                'name'        => $taxRate['name'],
                'code'        => $taxRate['code'],
                'rate'        => $taxRate['rate'],
                'tax'         => $tax,
                'apply_order' => (int) $taxRate['apply_order'],
            ];

            $totalTax = bcadd($totalTax, $tax, 2);
        }

        return [
            'rates'     => $rates,
            'total_tax' => $totalTax,
        ];
    }

    /**
     * Get a summary of tax collected and tax paid within a date range.
     *
     * Aggregates journal entry lines that reference tax accounts (via tax_rates.tax_account_id).
     * Credits to a tax account represent tax collected; debits represent tax paid.
     *
     * @param string $fromDate Period start (inclusive)
     * @param string $toDate   Period end (inclusive)
     *
     * @return array Tax summary grouped by tax rate with collected, paid, and net amounts
     */
    public function getTaxSummary(string $fromDate, string $toDate): array
    {
        $sql = "SELECT tr.id AS tax_rate_id,
                       tr.name AS tax_name,
                       tr.code AS tax_code,
                       tr.rate,
                       COALESCE(SUM(jel.credit), 0) AS tax_collected,
                       COALESCE(SUM(jel.debit), 0) AS tax_paid
                FROM tax_rates tr
                INNER JOIN journal_entry_lines jel ON jel.account_id = tr.tax_account_id
                INNER JOIN journal_entries je ON je.id = jel.journal_entry_id
                WHERE je.status = 'posted'
                  AND je.entry_date >= ?
                  AND je.entry_date <= ?
                GROUP BY tr.id, tr.name, tr.code, tr.rate
                ORDER BY tr.name ASC";

        $rows = $this->db->query($sql, [$fromDate, $toDate]);

        bcscale(2);
        $totalCollected = '0.00';
        $totalPaid = '0.00';

        foreach ($rows as &$row) {
            $row['net_tax'] = bcsub($row['tax_collected'], $row['tax_paid']);
            $totalCollected = bcadd($totalCollected, $row['tax_collected']);
            $totalPaid = bcadd($totalPaid, $row['tax_paid']);
        }
        unset($row);

        return [
            'from_date'       => $fromDate,
            'to_date'         => $toDate,
            'rates'           => $rows,
            'total_collected'  => $totalCollected,
            'total_paid'       => $totalPaid,
            'net_tax'          => bcsub($totalCollected, $totalPaid),
        ];
    }
}
