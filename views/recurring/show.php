<?php
/**
 * Recurring Template detail view.
 *
 * Variables: $template (with 'lines' array)
 */

$statusBadges = [
    'active'   => 'success',
    'paused'   => 'warning',
    'inactive' => 'secondary',
    'expired'  => 'danger',
];
$badge = $statusBadges[$template['status'] ?? ''] ?? 'secondary';

$frequencyLabels = [
    'daily'     => 'Daily',
    'weekly'    => 'Weekly',
    'biweekly'  => 'Bi-Weekly',
    'monthly'   => 'Monthly',
    'quarterly' => 'Quarterly',
    'annually'  => 'Annually',
];

$typeLabels = [
    'journal_entry' => 'Journal Entry',
    'invoice'       => 'Invoice',
    'bill'          => 'Bill',
];

$isActive = (($template['status'] ?? '') === 'active');

$lines       = $template['lines'] ?? [];
$totalDebit  = 0;
$totalCredit = 0;
foreach ($lines as $line) {
    $totalDebit  += (float) $line['debit'];
    $totalCredit += (float) $line['credit'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        Recurring Template
        <span class="text-muted ms-1"><?= \DoubleE\Core\View::e($template['name']) ?></span>
    </h4>
    <div class="d-flex gap-2">
        <a href="/recurring" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <?php if ($isActive): ?>
            <form method="POST" action="/recurring/<?= (int) $template['id'] ?>/run" class="d-inline">
                <?= \DoubleE\Core\Csrf::field() ?>
                <button type="submit"
                        class="btn btn-dark"
                        style="border-radius: 0;"
                        onclick="return confirm('Create a transaction from this template now?');">
                    <i class="bi bi-play-fill me-1"></i> Run Now
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Template Details Card -->
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Template Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Name</div>
                    <div class="col-md-9 fw-medium"><?= \DoubleE\Core\View::e($template['name']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Type</div>
                    <div class="col-md-9"><?= $typeLabels[$template['type'] ?? ''] ?? \DoubleE\Core\View::e($template['type'] ?? '') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Frequency</div>
                    <div class="col-md-9"><?= $frequencyLabels[$template['frequency'] ?? ''] ?? \DoubleE\Core\View::e($template['frequency'] ?? '') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status</div>
                    <div class="col-md-9">
                        <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;">
                            <?= ucfirst(\DoubleE\Core\View::e($template['status'] ?? 'inactive')) ?>
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Start Date</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($template['start_date'] ?? '-') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">End Date</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($template['end_date'] ?? 'No end date') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Next Run</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($template['next_run_date'] ?? '-') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Last Run</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($template['last_run_date'] ?? 'Never') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Occurrences</div>
                    <div class="col-md-9 font-monospace"><?= (int) ($template['occurrences'] ?? 0) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Auto-Post</div>
                    <div class="col-md-9">
                        <?php if (!empty($template['auto_post'])): ?>
                            <span class="badge text-bg-primary" style="border-radius: 0;">Yes</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary" style="border-radius: 0;">No</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($template['description'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Description</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($template['description']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Line Items -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Line Items</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 130px;">Account #</th>
                            <th>Account Name</th>
                            <th>Description</th>
                            <th style="width: 140px;" class="text-end">Debit</th>
                            <th style="width: 140px;" class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lines)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No line items.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lines as $line): ?>
                                <tr>
                                    <td>
                                        <code class="text-dark"><?= \DoubleE\Core\View::e($line['account_number'] ?? '') ?></code>
                                    </td>
                                    <td>
                                        <a href="/ledger/account/<?= (int) $line['account_id'] ?>" class="text-decoration-none">
                                            <?= \DoubleE\Core\View::e($line['account_name'] ?? '') ?>
                                        </a>
                                    </td>
                                    <td class="text-muted"><?= \DoubleE\Core\View::e($line['description'] ?? '') ?></td>
                                    <td class="text-end font-monospace">
                                        <?= (float) $line['debit'] > 0 ? number_format((float) $line['debit'], 2) : '' ?>
                                    </td>
                                    <td class="text-end font-monospace">
                                        <?= (float) $line['credit'] > 0 ? number_format((float) $line['credit'], 2) : '' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-semibold">
                            <td colspan="3" class="text-end">Totals:</td>
                            <td class="text-end font-monospace"><?= number_format($totalDebit, 2) ?></td>
                            <td class="text-end font-monospace"><?= number_format($totalCredit, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Run History (Placeholder) -->
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Run History</h6>
            </div>
            <div class="card-body">
                <p class="text-muted text-center py-3 mb-0">Run history tracking will be available in a future update.</p>
            </div>
        </div>
    </div>
</div>
