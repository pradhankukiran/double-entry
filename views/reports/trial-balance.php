<?php
/**
 * Trial Balance report view.
 *
 * Variables:
 *   $report   — array with 'accounts' (grouped by type), 'total_debits', 'total_credits', 'is_balanced'
 *   $asOfDate — the as-of date string (Y-m-d)
 */

/** Format an amount: positive normal, negative in parens, zero as dash. */
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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Trial Balance</h4>
        <p class="text-muted small mb-0">As of <?= \DoubleE\Core\View::e(date('F j, Y', strtotime($asOfDate))) ?></p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="/export/trial-balance?as_of_date=<?= \DoubleE\Core\View::e($asOfDate) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
        <a href="/reports" class="btn btn-outline-secondary btn-sm" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> All Reports
        </a>
    </div>
</div>

<!-- Filters -->
<?php
$view = new \DoubleE\Core\View();
echo $view->partial('reports/_report-filters', [
    'filterType' => 'point_in_time',
    'reportUrl'  => '/reports/trial-balance',
    'pdfUrl'     => '/reports/export/trial-balance',
    'asOfDate'   => $asOfDate,
]);
?>

<!-- Report Table -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 130px;">Account #</th>
                        <th>Account Name</th>
                        <th style="width: 150px;" class="text-end">Debit</th>
                        <th style="width: 150px;" class="text-end">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $accounts = $report['accounts'] ?? [];
                    $currentType = null;
                    ?>
                    <?php foreach ($accounts as $account): ?>
                        <?php
                        $type = $account['account_type'] ?? '';
                        if ($type !== $currentType):
                            $currentType = $type;
                        ?>
                            <tr class="table-light">
                                <td colspan="4" class="fw-semibold text-uppercase small py-2">
                                    <?= \DoubleE\Core\View::e($currentType) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><code class="text-dark"><?= \DoubleE\Core\View::e($account['account_number'] ?? '') ?></code></td>
                            <td><?= \DoubleE\Core\View::e($account['name'] ?? '') ?></td>
                            <td class="text-end font-monospace amount">
                                <?php $debit = (float) ($account['debit'] ?? 0); ?>
                                <?= $debit > 0 ? number_format($debit, 2) : '' ?>
                            </td>
                            <td class="text-end font-monospace amount">
                                <?php $credit = (float) ($account['credit'] ?? 0); ?>
                                <?= $credit > 0 ? number_format($credit, 2) : '' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <?php
                    $totalDebits  = (float) ($report['total_debits'] ?? 0);
                    $totalCredits = (float) ($report['total_credits'] ?? 0);
                    $isBalanced   = $report['is_balanced'] ?? (abs($totalDebits - $totalCredits) < 0.005);
                    ?>
                    <tr class="totals-row <?= $isBalanced ? 'balanced' : 'unbalanced' ?>">
                        <td colspan="2" class="text-end fw-bold">Totals</td>
                        <td class="text-end font-monospace amount fw-bold">
                            <?= number_format($totalDebits, 2) ?>
                        </td>
                        <td class="text-end font-monospace amount fw-bold">
                            <?= number_format($totalCredits, 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end py-2">
                            <?php if ($isBalanced): ?>
                                <span class="badge bg-success" style="border-radius: 0;">BALANCED</span>
                            <?php else: ?>
                                <span class="badge bg-danger" style="border-radius: 0;">UNBALANCED</span>
                                <span class="text-danger small ms-2">
                                    Difference: <?= number_format(abs($totalDebits - $totalCredits), 2) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
