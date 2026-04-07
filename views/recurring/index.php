<?php
/**
 * Recurring Transactions — list view.
 *
 * Variables: $templates (array of recurring templates)
 */

$statusBadges = [
    'active'   => 'success',
    'paused'   => 'warning',
    'inactive' => 'secondary',
    'expired'  => 'danger',
];

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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Recurring Transactions</h4>
    <?php if ($canCreate ?? false): ?>
    <a href="/recurring/create" class="btn btn-dark" style="border-radius: 0;">
        <i class="bi bi-plus-lg me-1"></i> New Template
    </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th style="width: 120px;">Type</th>
                    <th style="width: 110px;">Frequency</th>
                    <th style="width: 110px;">Next Run</th>
                    <th style="width: 110px;">Last Run</th>
                    <th style="width: 100px;" class="text-center">Occurrences</th>
                    <th style="width: 90px;">Status</th>
                    <th style="width: 140px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No recurring templates found. <a href="/recurring/create" class="text-decoration-none">Create your first template</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($templates as $tpl): ?>
                        <?php $tplBadge = $statusBadges[$tpl['status'] ?? ''] ?? 'secondary'; ?>
                        <tr>
                            <td class="fw-medium">
                                <a href="/recurring/<?= (int) $tpl['id'] ?>" class="text-decoration-none text-dark">
                                    <?= \DoubleE\Core\View::e($tpl['name']) ?>
                                </a>
                            </td>
                            <td><?= $typeLabels[$tpl['type'] ?? ''] ?? \DoubleE\Core\View::e($tpl['type'] ?? '') ?></td>
                            <td><?= $frequencyLabels[$tpl['frequency'] ?? ''] ?? \DoubleE\Core\View::e($tpl['frequency'] ?? '') ?></td>
                            <td><?= \DoubleE\Core\View::e($tpl['next_run_date'] ?? '-') ?></td>
                            <td><?= \DoubleE\Core\View::e($tpl['last_run_date'] ?? '-') ?></td>
                            <td class="text-center font-monospace"><?= (int) ($tpl['occurrences'] ?? 0) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $tplBadge ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($tpl['status'] ?? 'inactive')) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="/recurring/<?= (int) $tpl['id'] ?>" class="btn btn-sm btn-outline-dark me-1" style="border-radius: 0;" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (($tpl['status'] ?? '') === 'active' && ($canCreate ?? false)): ?>
                                    <form method="POST" action="/recurring/<?= (int) $tpl['id'] ?>/run" class="d-inline">
                                        <?= \DoubleE\Core\Csrf::field() ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-dark"
                                                style="border-radius: 0;"
                                                title="Run Now"
                                                onclick="return confirm('Create a transaction from this template now?');">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
