<?php
/**
 * AR/AP Aging Report view.
 *
 * Variables: $aging (array with 'buckets', 'grand_total', 'type', 'as_of_date'), $type (string)
 */

$buckets    = $aging['buckets'] ?? [];
$grandTotal = (float) ($aging['grand_total'] ?? 0);
$asOfDate   = $aging['as_of_date'] ?? date('Y-m-d');
$isAR       = ($type === 'invoice');

$bucketBadges = [
    'current' => 'success',
    '1_30'    => 'info',
    '31_60'   => 'warning',
    '61_90'   => 'danger',
    '90_plus' => 'dark',
];

$bucketIcons = [
    'current' => 'bi-check-circle',
    '1_30'    => 'bi-clock',
    '31_60'   => 'bi-exclamation-circle',
    '61_90'   => 'bi-exclamation-triangle',
    '90_plus' => 'bi-x-circle',
];

/**
 * Format a monetary amount: negative values in parentheses.
 */
function agingFormatAmount(float $amount): string {
    if ($amount < 0) {
        return '(' . number_format(abs($amount), 2) . ')';
    }
    return number_format($amount, 2);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <?= $isAR ? 'Accounts Receivable' : 'Accounts Payable' ?> Aging Report
    </h4>
    <div class="d-flex gap-2 align-items-center">
        <span class="text-muted small">As of <?= \DoubleE\Core\View::e($asOfDate) ?></span>
        <a href="/reports" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Reports
        </a>
    </div>
</div>

<!-- AR / AP Toggle -->
<ul class="nav nav-tabs mb-4" style="border-radius: 0;">
    <li class="nav-item">
        <a class="nav-link <?= $isAR ? 'active' : '' ?>" href="/reports/aging?type=invoice" style="border-radius: 0;">
            <i class="bi bi-receipt me-1"></i> Accounts Receivable
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !$isAR ? 'active' : '' ?>" href="/reports/aging?type=bill" style="border-radius: 0;">
            <i class="bi bi-file-earmark-text me-1"></i> Accounts Payable
        </a>
    </li>
</ul>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <?php foreach ($buckets as $key => $bucket): ?>
    <div class="col">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 0;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi <?= $bucketIcons[$key] ?? 'bi-circle' ?> text-<?= $bucketBadges[$key] ?? 'secondary' ?> me-2"></i>
                    <span class="small fw-semibold text-muted"><?= \DoubleE\Core\View::e($bucket['label']) ?></span>
                </div>
                <div class="fs-5 fw-bold font-monospace">
                    <?= agingFormatAmount((float) $bucket['total']) ?>
                </div>
                <div class="text-muted small mt-1">
                    <?= count($bucket['invoices']) ?> <?= count($bucket['invoices']) === 1 ? ($isAR ? 'invoice' : 'bill') : ($isAR ? 'invoices' : 'bills') ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Grand Total -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body p-3 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Total Outstanding</span>
        <span class="fs-4 fw-bold font-monospace"><?= agingFormatAmount($grandTotal) ?></span>
    </div>
</div>

<!-- Detail Table -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
        <h6 class="mb-0">Outstanding <?= $isAR ? 'Invoices' : 'Bills' ?></h6>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Contact</th>
                    <th>Document #</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th class="text-end">Days Overdue</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Balance Due</th>
                    <th>Bucket</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $hasRows = false;
                foreach ($buckets as $bucketKey => $bucket):
                    foreach ($bucket['invoices'] as $inv):
                        $hasRows = true;
                ?>
                <tr>
                    <td><?= \DoubleE\Core\View::e($inv['contact_name'] ?? '') ?></td>
                    <td>
                        <a href="/invoices/<?= (int) $inv['id'] ?>" class="text-decoration-none">
                            <code class="text-dark"><?= \DoubleE\Core\View::e($inv['document_number']) ?></code>
                        </a>
                    </td>
                    <td><?= \DoubleE\Core\View::e($inv['issue_date']) ?></td>
                    <td><?= \DoubleE\Core\View::e($inv['due_date']) ?></td>
                    <td class="text-end font-monospace">
                        <?= (int) $inv['days_overdue'] ?>
                    </td>
                    <td class="text-end font-monospace"><?= agingFormatAmount((float) $inv['total']) ?></td>
                    <td class="text-end font-monospace fw-semibold"><?= agingFormatAmount((float) $inv['balance_due']) ?></td>
                    <td>
                        <span class="badge text-bg-<?= $bucketBadges[$bucketKey] ?? 'secondary' ?>" style="border-radius: 0;">
                            <?= \DoubleE\Core\View::e($bucket['label']) ?>
                        </span>
                    </td>
                </tr>
                <?php
                    endforeach;
                endforeach;
                ?>
                <?php if (!$hasRows): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No outstanding <?= $isAR ? 'invoices' : 'bills' ?> found.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
            <?php if ($hasRows): ?>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="6" class="text-end">Grand Total:</td>
                    <td class="text-end font-monospace"><?= agingFormatAmount($grandTotal) ?></td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
