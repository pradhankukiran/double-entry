<?php
/**
 * Fiscal Year Closing Wizard — preview of closing journal entry.
 *
 * Variables: $year, $revenueLines, $expenseLines, $reLine,
 *            $totalRevenue, $totalExpenses, $netIncome
 */

bcscale(2);

// Calculate totals for the closing entry preview
$totalDebit = '0.00';
$totalCredit = '0.00';

foreach ($revenueLines as $line) {
    $totalDebit = bcadd($totalDebit, $line['debit']);
}
foreach ($expenseLines as $line) {
    $totalCredit = bcadd($totalCredit, $line['credit']);
}
if ($reLine !== null) {
    $totalDebit = bcadd($totalDebit, $reLine['debit']);
    $totalCredit = bcadd($totalCredit, $reLine['credit']);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Close Fiscal Year: <?= \DoubleE\Core\View::e($year['name']) ?></h4>
    <a href="/fiscal-years" class="btn btn-outline-secondary" style="border-radius: 0;">
        <i class="bi bi-arrow-left me-1"></i> Back to Fiscal Years
    </a>
</div>

<!-- Warning -->
<div class="alert alert-warning border-0 mb-4" style="border-radius: 0;">
    <div class="d-flex align-items-start">
        <i class="bi bi-exclamation-triangle-fill me-3 mt-1"></i>
        <div>
            <strong>This action is irreversible.</strong>
            All income and expense accounts will be closed to Retained Earnings.
            All fiscal periods will be locked and no further entries can be posted to this year.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Summary Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Year-End Summary</h6>
            </div>
            <div class="card-body p-4">
                <div class="row text-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="text-muted small mb-1">Total Revenue</div>
                        <div class="fs-5 fw-semibold text-success">
                            $<?= number_format((float) $totalRevenue, 2) ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="text-muted small mb-1">Total Expenses</div>
                        <div class="fs-5 fw-semibold text-danger">
                            $<?= number_format((float) $totalExpenses, 2) ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Net Income</div>
                        <div class="fs-5 fw-semibold <?= bccomp($netIncome, '0.00') >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?php if (bccomp($netIncome, '0.00') < 0): ?>
                                ($<?= number_format(abs((float) $netIncome), 2) ?>)
                            <?php else: ?>
                                $<?= number_format((float) $netIncome, 2) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-muted small">Fiscal Year</div>
                        <div class="fw-medium">
                            <?= \DoubleE\Core\View::e($year['start_date']) ?>
                            &mdash;
                            <?= \DoubleE\Core\View::e($year['end_date']) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Entry Date</div>
                        <div class="fw-medium"><?= \DoubleE\Core\View::e($year['end_date']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closing Journal Entry Preview -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Proposed Closing Journal Entry</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 130px;">Account #</th>
                            <th>Account Name</th>
                            <th style="width: 140px;" class="text-end">Debit</th>
                            <th style="width: 140px;" class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($revenueLines)): ?>
                            <tr class="table-light">
                                <td colspan="4" class="text-muted small fw-semibold py-2">Revenue Accounts</td>
                            </tr>
                            <?php foreach ($revenueLines as $line): ?>
                                <tr>
                                    <td><code class="text-dark"><?= \DoubleE\Core\View::e($line['account_number']) ?></code></td>
                                    <td><?= \DoubleE\Core\View::e($line['account_name']) ?></td>
                                    <td class="text-end font-monospace">
                                        <?= bccomp($line['debit'], '0.00') > 0 ? number_format((float) $line['debit'], 2) : '' ?>
                                    </td>
                                    <td class="text-end font-monospace">
                                        <?= bccomp($line['credit'], '0.00') > 0 ? number_format((float) $line['credit'], 2) : '' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($expenseLines)): ?>
                            <tr class="table-light">
                                <td colspan="4" class="text-muted small fw-semibold py-2">Expense Accounts</td>
                            </tr>
                            <?php foreach ($expenseLines as $line): ?>
                                <tr>
                                    <td><code class="text-dark"><?= \DoubleE\Core\View::e($line['account_number']) ?></code></td>
                                    <td><?= \DoubleE\Core\View::e($line['account_name']) ?></td>
                                    <td class="text-end font-monospace">
                                        <?= bccomp($line['debit'], '0.00') > 0 ? number_format((float) $line['debit'], 2) : '' ?>
                                    </td>
                                    <td class="text-end font-monospace">
                                        <?= bccomp($line['credit'], '0.00') > 0 ? number_format((float) $line['credit'], 2) : '' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ($reLine !== null): ?>
                            <tr class="table-light">
                                <td colspan="4" class="text-muted small fw-semibold py-2">Equity</td>
                            </tr>
                            <tr class="fw-medium">
                                <td><code class="text-dark"><?= \DoubleE\Core\View::e($reLine['account_number']) ?></code></td>
                                <td><?= \DoubleE\Core\View::e($reLine['account_name']) ?></td>
                                <td class="text-end font-monospace">
                                    <?= bccomp($reLine['debit'], '0.00') > 0 ? number_format((float) $reLine['debit'], 2) : '' ?>
                                </td>
                                <td class="text-end font-monospace">
                                    <?= bccomp($reLine['credit'], '0.00') > 0 ? number_format((float) $reLine['credit'], 2) : '' ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if (empty($revenueLines) && empty($expenseLines)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No revenue or expense balances found for this fiscal year.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-semibold">
                            <td colspan="2" class="text-end">Totals:</td>
                            <td class="text-end font-monospace"><?= number_format((float) $totalDebit, 2) ?></td>
                            <td class="text-end font-monospace"><?= number_format((float) $totalCredit, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="/fiscal-years" class="btn btn-outline-secondary" style="border-radius: 0;">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <?php if (!empty($revenueLines) || !empty($expenseLines)): ?>
                <form method="POST" action="/fiscal-years/<?= (int) $year['id'] ?>/close">
                    <?= \DoubleE\Core\Csrf::field() ?>
                    <button type="submit"
                            class="btn btn-dark"
                            style="border-radius: 0;"
                            onclick="return confirm('Are you sure? This cannot be undone.');">
                        <i class="bi bi-lock-fill me-1"></i> Close Fiscal Year
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">What happens when you close?</h6>
            </div>
            <div class="card-body p-4">
                <ol class="mb-0 ps-3">
                    <li class="mb-2">A closing journal entry is created and posted automatically.</li>
                    <li class="mb-2">All revenue and expense account balances are transferred to Retained Earnings.</li>
                    <li class="mb-2">All fiscal periods in this year are locked.</li>
                    <li class="mb-0">The fiscal year status changes to <strong>closed</strong> permanently.</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Closing Entry Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="text-muted small">Revenue Accounts</div>
                    <div class="fw-medium"><?= count($revenueLines) ?> account<?= count($revenueLines) !== 1 ? 's' : '' ?></div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Expense Accounts</div>
                    <div class="fw-medium"><?= count($expenseLines) ?> account<?= count($expenseLines) !== 1 ? 's' : '' ?></div>
                </div>
                <div class="mb-0">
                    <div class="text-muted small">Total Lines</div>
                    <div class="fw-medium"><?= count($revenueLines) + count($expenseLines) + ($reLine !== null ? 1 : 0) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
