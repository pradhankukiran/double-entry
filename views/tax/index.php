<?php
/**
 * Tax Management — list tax rates and tax groups.
 *
 * Variables: $rates (array), $groups (array)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tax Management</h4>
    <?php if ($canCreate ?? false): ?>
    <div class="d-flex gap-2">
        <a href="/tax/rates/create" class="btn btn-dark" style="border-radius: 0;">
            <i class="bi bi-plus-lg me-1"></i> New Tax Rate
        </a>
        <a href="/tax/groups/create" class="btn btn-outline-dark" style="border-radius: 0;">
            <i class="bi bi-plus-lg me-1"></i> New Tax Group
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Tax Rates Section -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
        <h6 class="mb-0">Tax Rates</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th style="width: 100px;">Code</th>
                    <th style="width: 100px;" class="text-end">Rate %</th>
                    <th>Tax Account</th>
                    <th style="width: 90px;">Status</th>
                    <th style="width: 90px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rates)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No tax rates defined. <a href="/tax/rates/create" class="text-decoration-none">Create your first tax rate</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rates as $rate): ?>
                        <tr>
                            <td class="fw-medium"><?= \DoubleE\Core\View::e($rate['name']) ?></td>
                            <td><code class="text-dark"><?= \DoubleE\Core\View::e($rate['code']) ?></code></td>
                            <td class="text-end font-monospace"><?= number_format((float) $rate['rate'], 2) ?>%</td>
                            <td class="text-muted"><?= \DoubleE\Core\View::e($rate['account_name'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($rate['is_active'])): ?>
                                    <span class="badge text-bg-success" style="border-radius: 0;">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary" style="border-radius: 0;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/tax/rates/<?= (int) $rate['id'] ?>/edit" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tax Groups Section -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
        <h6 class="mb-0">Tax Groups</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th style="width: 100px;">Code</th>
                    <th>Rates</th>
                    <th style="width: 90px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groups)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            No tax groups defined. <a href="/tax/groups/create" class="text-decoration-none">Create your first tax group</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td class="fw-medium"><?= \DoubleE\Core\View::e($group['name']) ?></td>
                            <td><code class="text-dark"><?= \DoubleE\Core\View::e($group['code']) ?></code></td>
                            <td class="text-muted">
                                <?php
                                    $rateNames = array_map(function ($r) {
                                        return \DoubleE\Core\View::e($r['name']);
                                    }, $group['rates'] ?? []);
                                    echo !empty($rateNames) ? implode(', ', $rateNames) : '-';
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="/tax/groups/<?= (int) $group['id'] ?>/edit" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
