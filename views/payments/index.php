<?php
/**
 * Payments — list view with filters.
 *
 * Variables: $payments (array), $filters (array)
 */

$statusBadges = [
    'draft'  => 'secondary',
    'posted' => 'success',
    'voided' => 'danger',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Payments</h4>
    <div class="d-flex gap-2">
        <a href="/payments/create?type=received" class="btn btn-dark" style="border-radius: 0;">
            <i class="bi bi-plus-lg me-1"></i> Receive Payment
        </a>
        <a href="/payments/create?type=made" class="btn btn-outline-dark" style="border-radius: 0;">
            <i class="bi bi-plus-lg me-1"></i> Make Payment
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body py-3">
        <form method="GET" action="/payments" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label for="filter-type" class="form-label small text-muted mb-1">Type</label>
                <select class="form-select form-select-sm" id="filter-type" name="type" style="border-radius: 0;">
                    <option value="">All Types</option>
                    <option value="received" <?= ($filters['type'] ?? '') === 'received' ? 'selected' : '' ?>>Received</option>
                    <option value="made" <?= ($filters['type'] ?? '') === 'made' ? 'selected' : '' ?>>Made</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-status" class="form-label small text-muted mb-1">Status</label>
                <select class="form-select form-select-sm" id="filter-status" name="status" style="border-radius: 0;">
                    <option value="">All Statuses</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="posted" <?= ($filters['status'] ?? '') === 'posted' ? 'selected' : '' ?>>Posted</option>
                    <option value="voided" <?= ($filters['status'] ?? '') === 'voided' ? 'selected' : '' ?>>Voided</option>
                </select>
            </div>
            <div class="col-md-8 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm" style="border-radius: 0;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="/payments" class="btn btn-outline-secondary btn-sm" style="border-radius: 0;">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 150px;">Payment #</th>
                    <th>Contact</th>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 130px;" class="text-end">Amount</th>
                    <th style="width: 130px;">Method</th>
                    <th style="width: 100px;">Type</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 90px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No payments found.
                            <?php if (!empty($filters)): ?>
                                Try adjusting your filters or
                            <?php endif; ?>
                            <a href="/payments/create?type=received" class="text-decoration-none">receive a payment</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $pmt): ?>
                        <?php $pmtBadge = $statusBadges[$pmt['status']] ?? 'secondary'; ?>
                        <tr>
                            <td>
                                <code class="text-dark"><?= \DoubleE\Core\View::e($pmt['payment_number']) ?></code>
                            </td>
                            <td>
                                <a href="/contacts/<?= (int) $pmt['contact_id'] ?>" class="text-decoration-none">
                                    <?= \DoubleE\Core\View::e($pmt['contact_name']) ?>
                                </a>
                            </td>
                            <td><?= \DoubleE\Core\View::e($pmt['payment_date']) ?></td>
                            <td class="text-end font-monospace"><?= number_format((float) $pmt['amount'], 2) ?></td>
                            <td class="text-muted"><?= ucfirst(str_replace('_', ' ', \DoubleE\Core\View::e($pmt['payment_method']))) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $pmt['type'] === 'received' ? 'success' : 'primary' ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($pmt['type'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $pmtBadge ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($pmt['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="/payments/<?= (int) $pmt['id'] ?>" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
