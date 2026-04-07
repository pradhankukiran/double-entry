<?php
/**
 * Account detail view.
 *
 * Variables: $account, $type, $subtype, $parent, $children
 */

$typeBadges = [
    'Asset'     => 'primary',
    'Liability' => 'danger',
    'Equity'    => 'purple',
    'Revenue'   => 'success',
    'Expense'   => 'warning',
];
$badgeColor = $typeBadges[$type['name'] ?? ''] ?? 'secondary';
$isInactive = empty($account['is_active']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <code class="text-dark me-2"><?= \DoubleE\Core\View::e($account['account_number']) ?></code>
        <?= \DoubleE\Core\View::e($account['name']) ?>
    </h4>
    <div class="d-flex gap-2">
        <a href="/accounts" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Back to Chart of Accounts
        </a>
        <a href="/accounts/<?= (int) $account['id'] ?>/edit" class="btn btn-outline-dark" style="border-radius: 0;">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <?php if (empty($account['is_system'])): ?>
            <form method="POST" action="/accounts/<?= (int) $account['id'] ?>/toggle-active" class="d-inline">
                <?= \DoubleE\Core\Csrf::field() ?>
                <?php if (!$isInactive): ?>
                    <button type="submit" class="btn btn-outline-danger" style="border-radius: 0;" onclick="return confirm('Are you sure you want to deactivate this account?');">
                        <i class="bi bi-x-circle me-1"></i> Deactivate
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn btn-outline-success" style="border-radius: 0;">
                        <i class="bi bi-check-circle me-1"></i> Activate
                    </button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Account Details -->
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Account Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Account Number</div>
                    <div class="col-md-9 fw-medium"><code><?= \DoubleE\Core\View::e($account['account_number']) ?></code></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Account Name</div>
                    <div class="col-md-9 fw-medium"><?= \DoubleE\Core\View::e($account['name']) ?></div>
                </div>
                <?php if (!empty($account['description'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Description</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($account['description']) ?></div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Type</div>
                    <div class="col-md-9">
                        <?php if ($badgeColor === 'purple'): ?>
                            <span class="badge" style="background-color: #6f42c1; border-radius: 0;"><?= \DoubleE\Core\View::e($type['name'] ?? '') ?></span>
                        <?php else: ?>
                            <span class="badge text-bg-<?= $badgeColor ?>" style="border-radius: 0;"><?= \DoubleE\Core\View::e($type['name'] ?? '') ?></span>
                        <?php endif; ?>
                        <span class="text-muted small ms-2">(Normal balance: <?= \DoubleE\Core\View::e($type['normal_balance'] ?? '') ?>)</span>
                    </div>
                </div>
                <?php if ($subtype !== null): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Sub-Type</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($subtype['name']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($parent !== null): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Parent Account</div>
                    <div class="col-md-9">
                        <a href="/accounts/<?= (int) $parent['id'] ?>" class="text-decoration-none">
                            <?= \DoubleE\Core\View::e($parent['account_number'] . ' - ' . $parent['name']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Currency</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($account['currency_code'] ?? 'USD') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Opening Balance</div>
                    <div class="col-md-9"><?= number_format((float) ($account['opening_balance'] ?? 0), 2) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status</div>
                    <div class="col-md-9">
                        <?php if (!$isInactive): ?>
                            <span class="badge text-bg-success" style="border-radius: 0;">Active</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary" style="border-radius: 0;">Inactive</span>
                        <?php endif; ?>
                        <?php if (!empty($account['is_header'])): ?>
                            <span class="badge text-bg-secondary" style="border-radius: 0;">Header</span>
                        <?php endif; ?>
                        <?php if (!empty($account['is_system'])): ?>
                            <span class="badge text-bg-dark" style="border-radius: 0;">System</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Child Accounts -->
        <?php if (!empty($children)): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Child Accounts</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Account Number</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($children as $child): ?>
                            <tr>
                                <td><code class="text-dark"><?= \DoubleE\Core\View::e($child['account_number']) ?></code></td>
                                <td><?= \DoubleE\Core\View::e($child['name']) ?></td>
                                <td>
                                    <?php if (!empty($child['is_active'])): ?>
                                        <span class="badge text-bg-success" style="border-radius: 0;">Active</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary" style="border-radius: 0;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="/accounts/<?= (int) $child['id'] ?>" class="btn btn-sm btn-outline-dark" style="border-radius: 0;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Transaction History -->
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Transaction History</h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-0">Transaction history will appear here once journal entries are created.</p>
            </div>
        </div>
    </div>
</div>
