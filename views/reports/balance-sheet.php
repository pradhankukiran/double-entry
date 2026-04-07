<?php
/**
 * Balance Sheet report view.
 *
 * Variables:
 *   $report   — array with 'assets', 'liabilities', 'equity' sections,
 *               each having 'accounts' (grouped by subtype), 'total';
 *               also 'net_income', 'total_liabilities_and_equity', 'is_balanced'
 *   $asOfDate — the as-of date string (Y-m-d)
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
        <h4 class="mb-0">Balance Sheet</h4>
        <p class="text-muted small mb-0">As of <?= \DoubleE\Core\View::e(date('F j, Y', strtotime($asOfDate))) ?></p>
    </div>
    <a href="/reports" class="btn btn-outline-secondary btn-sm no-print" style="border-radius: 0;">
        <i class="bi bi-arrow-left me-1"></i> All Reports
    </a>
</div>

<!-- Filters -->
<?php
$view = new \DoubleE\Core\View();
echo $view->partial('reports/_report-filters', [
    'filterType' => 'point_in_time',
    'reportUrl'  => '/reports/balance-sheet',
    'pdfUrl'     => '/reports/export/balance-sheet',
    'asOfDate'   => $asOfDate,
]);
?>

<?php
$assets      = $report['assets'] ?? [];
$liabilities = $report['liabilities'] ?? [];
$equity      = $report['equity'] ?? [];
$netIncome   = (float) ($report['net_income'] ?? 0);
$totalAssets = (float) ($report['assets']['total'] ?? 0);
$totalLiab   = (float) ($report['liabilities']['total'] ?? 0);
$totalEquity = (float) ($report['equity']['total'] ?? 0);
$totalLE     = (float) ($report['total_liabilities_and_equity'] ?? ($totalLiab + $totalEquity + $netIncome));
$isBalanced  = $report['is_balanced'] ?? (abs($totalAssets - $totalLE) < 0.005);
?>

<div class="row g-4">
    <!-- Assets -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-2" style="border-radius: 0;">
                <h6 class="fw-semibold mb-0">Assets</h6>
            </div>
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
                            <?php
                            $assetAccounts = $assets['accounts'] ?? [];
                            $currentSubtype = null;
                            ?>
                            <?php foreach ($assetAccounts as $account): ?>
                                <?php
                                $subtype = $account['subtype'] ?? '';
                                if ($subtype !== $currentSubtype):
                                    $currentSubtype = $subtype;
                                ?>
                                    <tr class="table-light">
                                        <td colspan="3" class="fw-semibold small py-2 ps-3">
                                            <?= \DoubleE\Core\View::e($currentSubtype) ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="ps-4"><code class="text-dark"><?= \DoubleE\Core\View::e($account['account_number'] ?? '') ?></code></td>
                                    <td><?= \DoubleE\Core\View::e($account['name'] ?? '') ?></td>
                                    <td class="text-end font-monospace amount"><?= formatAmount((float) ($account['amount'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="totals-row">
                                <td colspan="2" class="text-end fw-bold">Total Assets</td>
                                <td class="text-end font-monospace amount fw-bold"><?= formatAmount($totalAssets) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Liabilities -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-2" style="border-radius: 0;">
                <h6 class="fw-semibold mb-0">Liabilities</h6>
            </div>
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
                            <?php
                            $liabAccounts = $liabilities['accounts'] ?? [];
                            $currentSubtype = null;
                            ?>
                            <?php foreach ($liabAccounts as $account): ?>
                                <?php
                                $subtype = $account['subtype'] ?? '';
                                if ($subtype !== $currentSubtype):
                                    $currentSubtype = $subtype;
                                ?>
                                    <tr class="table-light">
                                        <td colspan="3" class="fw-semibold small py-2 ps-3">
                                            <?= \DoubleE\Core\View::e($currentSubtype) ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="ps-4"><code class="text-dark"><?= \DoubleE\Core\View::e($account['account_number'] ?? '') ?></code></td>
                                    <td><?= \DoubleE\Core\View::e($account['name'] ?? '') ?></td>
                                    <td class="text-end font-monospace amount"><?= formatAmount((float) ($account['amount'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="totals-row">
                                <td colspan="2" class="text-end fw-bold">Total Liabilities</td>
                                <td class="text-end font-monospace amount fw-bold"><?= formatAmount($totalLiab) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Equity -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-2" style="border-radius: 0;">
                <h6 class="fw-semibold mb-0">Equity</h6>
            </div>
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
                            <?php
                            $equityAccounts = $equity['accounts'] ?? [];
                            $currentSubtype = null;
                            ?>
                            <?php foreach ($equityAccounts as $account): ?>
                                <?php
                                $subtype = $account['subtype'] ?? '';
                                if ($subtype !== $currentSubtype):
                                    $currentSubtype = $subtype;
                                ?>
                                    <tr class="table-light">
                                        <td colspan="3" class="fw-semibold small py-2 ps-3">
                                            <?= \DoubleE\Core\View::e($currentSubtype) ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="ps-4"><code class="text-dark"><?= \DoubleE\Core\View::e($account['account_number'] ?? '') ?></code></td>
                                    <td><?= \DoubleE\Core\View::e($account['name'] ?? '') ?></td>
                                    <td class="text-end font-monospace amount"><?= formatAmount((float) ($account['amount'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Net Income line -->
                            <tr class="table-light">
                                <td colspan="3" class="fw-semibold small py-2 ps-3">Net Income</td>
                            </tr>
                            <tr>
                                <td class="ps-4"></td>
                                <td class="fst-italic">Net Income (Current Period)</td>
                                <td class="text-end font-monospace amount"><?= formatAmount($netIncome) ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="totals-row">
                                <td colspan="2" class="text-end fw-bold">Total Equity</td>
                                <td class="text-end font-monospace amount fw-bold"><?= formatAmount($totalEquity + $netIncome) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Grand Total -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            <tr class="totals-row">
                                <td class="text-end fw-bold">Total Liabilities &amp; Equity</td>
                                <td style="width: 160px;" class="text-end font-monospace amount fw-bold">
                                    <?= formatAmount($totalLE) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-2">
                    <span class="small text-muted me-2">Assets = Liabilities + Equity:</span>
                    <?php if ($isBalanced): ?>
                        <span class="badge bg-success" style="border-radius: 0;">BALANCED</span>
                    <?php else: ?>
                        <span class="badge bg-danger" style="border-radius: 0;">UNBALANCED</span>
                        <span class="text-danger small ms-2">
                            Difference: <?= number_format(abs($totalAssets - $totalLE), 2) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
