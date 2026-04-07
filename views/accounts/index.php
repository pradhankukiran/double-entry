<?php
/**
 * Chart of Accounts — tree view index.
 *
 * Variables: $tree (nested account hierarchy), $accountTypes (list of types for filter)
 */

/** Recursively render account rows with indentation. */
function renderAccountRows(array $nodes, int $depth = 0): void {
    $typeBadges = [
        'Asset'     => 'primary',
        'Liability' => 'danger',
        'Equity'    => 'purple',
        'Revenue'   => 'success',
        'Expense'   => 'warning',
    ];

    foreach ($nodes as $account):
        $isInactive = empty($account['is_active']);
        $rowClass = $isInactive ? 'text-muted' : '';
        $indent = $depth * 24;
        $badgeColor = $typeBadges[$account['type_name'] ?? ''] ?? 'secondary';
?>
        <tr class="<?= $rowClass ?>">
            <td>
                <code class="text-dark"><?= \DoubleE\Core\View::e($account['account_number']) ?></code>
            </td>
            <td>
                <span style="padding-left: <?= $indent ?>px; display: inline-block;">
                    <?php if (!empty($account['is_header'])): ?>
                        <i class="bi bi-folder2 me-1 text-muted"></i>
                    <?php endif; ?>
                    <span class="<?= !empty($account['is_header']) ? 'fw-semibold' : '' ?>"><?= \DoubleE\Core\View::e($account['name']) ?></span>
                </span>
            </td>
            <td>
                <?php if ($badgeColor === 'purple'): ?>
                    <span class="badge" style="background-color: #6f42c1; border-radius: 0;"><?= \DoubleE\Core\View::e($account['type_name'] ?? '') ?></span>
                <?php else: ?>
                    <span class="badge text-bg-<?= $badgeColor ?>" style="border-radius: 0;"><?= \DoubleE\Core\View::e($account['type_name'] ?? '') ?></span>
                <?php endif; ?>
            </td>
            <td class="text-muted small"><?= \DoubleE\Core\View::e($account['subtype_name'] ?? '-') ?></td>
            <td>
                <?php if (!$isInactive): ?>
                    <span class="badge text-bg-success" style="border-radius: 0;">Active</span>
                <?php else: ?>
                    <span class="badge text-bg-secondary" style="border-radius: 0;">Inactive</span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <a href="/accounts/<?= (int) $account['id'] ?>" class="btn btn-sm btn-outline-dark me-1" style="border-radius: 0;" title="View">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="/accounts/<?= (int) $account['id'] ?>/edit" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
            </td>
        </tr>
<?php
        // Render children recursively
        if (!empty($account['children'])) {
            renderAccountRows($account['children'], $depth + 1);
        }
    endforeach;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Chart of Accounts</h4>
    <?php if ($canCreate ?? false): ?>
    <a href="/accounts/create" class="btn btn-dark" style="border-radius: 0;">
        <i class="bi bi-plus-lg me-1"></i> New Account
    </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px;">Account Number</th>
                    <th>Account Name</th>
                    <th style="width: 120px;">Type</th>
                    <th style="width: 160px;">Sub-Type</th>
                    <th style="width: 90px;">Status</th>
                    <th style="width: 120px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tree)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No accounts found. Create your first account to get started.</td>
                    </tr>
                <?php else: ?>
                    <?php renderAccountRows($tree); ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
