<?php
/**
 * Income Statement (Profit & Loss) report view.
 *
 * Variables:
 *   $report   — array with 'revenue' (accounts, total), 'expenses' (accounts, total),
 *               'cogs' (accounts, total — optional), 'gross_profit' (optional), 'net_income'
 *   $fromDate — period start (Y-m-d)
 *   $toDate   — period end (Y-m-d)
 */

/** Format an amount: positive normal, negative in parens, zero as dash. */
if (!function_exists('formatAmount')) {
    function formatAmount(float $amount): string
    {
        if (abs($amount) < 0.005) {
            return '&mdash;';
        }
        if ($amount < 0) {
            return '(' . number_format(abs($amount), 2) . ')';
        }
        return number_format($amount, 2);
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Income Statement</h4>
        <p class="text-muted small mb-0">
            For the period <?= \DoubleE\Core\View::e(date('F j, Y', strtotime($fromDate))) ?>
            to <?= \DoubleE\Core\View::e(date('F j, Y', strtotime($toDate))) ?>
        </p>
    </div>
    <a href="/reports" class="btn btn-outline-secondary btn-sm no-print" style="border-radius: 0;">
        <i class="bi bi-arrow-left me-1"></i> All Reports
    </a>
</div>

<!-- Filters -->
<?php
$view = new \DoubleE\Core\View();
echo $view->partial('reports/_report-filters', [
    'filterType' => 'date_range',
    'reportUrl'  => '/reports/income-statement',
    'pdfUrl'     => '/reports/export/income-statement',
    'fromDate'   => $fromDate,
    'toDate'     => $toDate,
]);
?>

<?php
$revenue      = $report['revenue'] ?? [];
$expenses     = $report['expenses'] ?? [];
$cogs         = $report['cogs'] ?? [];
$totalRevenue = (float) ($revenue['total'] ?? 0);
$totalCogs    = (float) ($cogs['total'] ?? 0);
$grossProfit  = (float) ($report['gross_profit'] ?? ($totalRevenue - $totalCogs));
$totalExpense = (float) ($expenses['total'] ?? 0);
$netIncome    = (float) ($report['net_income'] ?? 0);
$hasCogs      = !empty($cogs['accounts']);
?>

<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 130px;">Account #</th>
                        <th>Account Name</th>
                        <th style="width: 160px;" class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Revenue Section -->
                    <tr class="table-light">
                        <td colspan="3" class="fw-semibold text-uppercase small py-2">Revenue</td>
                    </tr>
                    <?php foreach ($revenue['accounts'] ?? [] as $account): ?>
                        <tr>
                            <td class="ps-4"><code class="text-dark"><?= \DoubleE\Core\View::e($account['account_number'] ?? '') ?></code></td>
                            <td><?= \DoubleE\Core\View::e($account['name'] ?? '') ?></td>
                            <td class="text-end font-monospace amount"><?= formatAmount((float) ($account['amount'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fw-semibold" style="border-top: 1px solid #000;">
                        <td colspan="2" class="text-end">Total Revenue</td>
                        <td class="text-end font-monospace amount"><?= formatAmount($totalRevenue) ?></td>
                    </tr>

                    <?php if ($hasCogs): ?>
                        <!-- Cost of Goods Sold Section -->
                        <tr><td colspan="3" class="py-1"></td></tr>
                        <tr class="table-light">
                            <td colspan="3" class="fw-semibold text-uppercase small py-2">Cost of Goods Sold</td>
                        </tr>
                        <?php foreach ($cogs['accounts'] ?? [] as $account): ?>
                            <tr>
                                <td class="ps-4"><code class="text-dark"><?= \DoubleE\Core\View::e($account['account_number'] ?? '') ?></code></td>
                                <td><?= \DoubleE\Core\View::e($account['name'] ?? '') ?></td>
                                <td class="text-end font-monospace amount"><?= formatAmount((float) ($account['amount'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="fw-semibold" style="border-top: 1px solid #000;">
                            <td colspan="2" class="text-end">Total COGS</td>
                            <td class="text-end font-monospace amount"><?= formatAmount($totalCogs) ?></td>
                        </tr>

                        <!-- Gross Profit -->
                        <tr><td colspan="3" class="py-1"></td></tr>
                        <tr class="fw-bold" style="background-color: #f8f9fa;">
                            <td colspan="2" class="text-end">Gross Profit</td>
                            <td class="text-end font-monospace amount"><?= formatAmount($grossProfit) ?></td>
                        </tr>
                    <?php endif; ?>

                    <!-- Expenses Section -->
                    <tr><td colspan="3" class="py-1"></td></tr>
                    <tr class="table-light">
                        <td colspan="3" class="fw-semibold text-uppercase small py-2">Expenses</td>
                    </tr>
                    <?php foreach ($expenses['accounts'] ?? [] as $account): ?>
                        <tr>
                            <td class="ps-4"><code class="text-dark"><?= \DoubleE\Core\View::e($account['account_number'] ?? '') ?></code></td>
                            <td><?= \DoubleE\Core\View::e($account['name'] ?? '') ?></td>
                            <td class="text-end font-monospace amount"><?= formatAmount((float) ($account['amount'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fw-semibold" style="border-top: 1px solid #000;">
                        <td colspan="2" class="text-end">Total Expenses</td>
                        <td class="text-end font-monospace amount"><?= formatAmount($totalExpense) ?></td>
                    </tr>
                </tbody>

                <!-- Net Income -->
                <tfoot>
                    <tr class="totals-row">
                        <td colspan="2" class="text-end fw-bold fs-6">Net Income</td>
                        <td class="text-end font-monospace amount fw-bold fs-6 <?= $netIncome < 0 ? 'text-danger' : '' ?>">
                            <?= formatAmount($netIncome) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
