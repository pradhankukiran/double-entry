<?php
/**
 * Cash Flow Statement report view.
 *
 * Variables:
 *   $report   — array with 'operating', 'investing', 'financing' sections
 *               (each with 'items' and 'net'), plus 'net_change',
 *               'beginning_cash', 'ending_cash'
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
        <h4 class="mb-0">Cash Flow Statement</h4>
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
    'reportUrl'  => '/reports/cash-flow',
    'pdfUrl'     => '/reports/export/cash-flow',
    'fromDate'   => $fromDate,
    'toDate'     => $toDate,
]);
?>

<?php
$operating     = $report['operating'] ?? [];
$investing     = $report['investing'] ?? [];
$financing     = $report['financing'] ?? [];
$netChange     = (float) ($report['net_change'] ?? 0);
$beginningCash = (float) ($report['beginning_cash'] ?? 0);
$endingCash    = (float) ($report['ending_cash'] ?? 0);

$sections = [
    [
        'title' => 'Cash Flows from Operating Activities',
        'data'  => $operating,
    ],
    [
        'title' => 'Cash Flows from Investing Activities',
        'data'  => $investing,
    ],
    [
        'title' => 'Cash Flows from Financing Activities',
        'data'  => $financing,
    ],
];
?>

<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th style="width: 180px;" class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $section): ?>
                        <?php
                        $items     = $section['data']['items'] ?? [];
                        $sectionNet = (float) ($section['data']['net'] ?? 0);
                        ?>
                        <!-- Section Header -->
                        <tr class="table-light">
                            <td colspan="2" class="fw-semibold text-uppercase small py-2">
                                <?= \DoubleE\Core\View::e($section['title']) ?>
                            </td>
                        </tr>

                        <?php if (empty($items)): ?>
                            <tr>
                                <td class="ps-4 text-muted fst-italic">No activity</td>
                                <td class="text-end font-monospace amount">&mdash;</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="ps-4"><?= \DoubleE\Core\View::e($item['description'] ?? '') ?></td>
                                    <td class="text-end font-monospace amount">
                                        <?= formatAmount((float) ($item['amount'] ?? 0)) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Section Net -->
                        <tr class="fw-semibold" style="border-top: 1px solid #000;">
                            <td class="text-end">Net Cash from <?= \DoubleE\Core\View::e(explode(' from ', $section['title'])[1] ?? $section['title']) ?></td>
                            <td class="text-end font-monospace amount"><?= formatAmount($sectionNet) ?></td>
                        </tr>

                        <!-- Spacer -->
                        <tr><td colspan="2" class="py-1"></td></tr>
                    <?php endforeach; ?>
                </tbody>

                <tfoot>
                    <!-- Net Change in Cash -->
                    <tr class="totals-row">
                        <td class="text-end fw-bold">Net Change in Cash</td>
                        <td class="text-end font-monospace amount fw-bold <?= $netChange < 0 ? 'text-danger' : '' ?>">
                            <?= formatAmount($netChange) ?>
                        </td>
                    </tr>

                    <!-- Beginning / Ending Cash -->
                    <tr>
                        <td colspan="2" class="py-1"></td>
                    </tr>
                    <tr>
                        <td class="text-end">Beginning Cash Balance</td>
                        <td class="text-end font-monospace amount"><?= formatAmount($beginningCash) ?></td>
                    </tr>
                    <tr class="fw-bold" style="border-top: 2px solid #000;">
                        <td class="text-end">Ending Cash Balance</td>
                        <td class="text-end font-monospace amount"><?= formatAmount($endingCash) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
