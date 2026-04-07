<?php
/**
 * Invoices — list view with filters.
 *
 * Variables: $invoices (array), $contacts (array), $filters (array)
 */

$statusBadges = [
    'draft'   => 'secondary',
    'sent'    => 'primary',
    'partial' => 'warning',
    'paid'    => 'success',
    'overdue' => 'danger',
    'voided'  => 'danger',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Invoices</h4>
    <div class="d-flex gap-2">
        <a href="/export/invoices?<?= http_build_query(array_filter([
            'document_type' => $filters['document_type'] ?? '',
        ])) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
        <?php if ($canCreate ?? false): ?>
        <a href="/invoices/create?type=invoice" class="btn btn-dark" style="border-radius: 0;">
            <i class="bi bi-plus-lg me-1"></i> New Invoice
        </a>
        <a href="/invoices/create?type=bill" class="btn btn-outline-dark" style="border-radius: 0;">
            <i class="bi bi-plus-lg me-1"></i> New Bill
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body py-3">
        <form method="GET" action="/invoices" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label for="filter-type" class="form-label small text-muted mb-1">Type</label>
                <select class="form-select form-select-sm" id="filter-type" name="document_type" style="border-radius: 0;">
                    <option value="">All Types</option>
                    <option value="invoice" <?= ($filters['document_type'] ?? '') === 'invoice' ? 'selected' : '' ?>>Invoice</option>
                    <option value="bill" <?= ($filters['document_type'] ?? '') === 'bill' ? 'selected' : '' ?>>Bill</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-status" class="form-label small text-muted mb-1">Status</label>
                <select class="form-select form-select-sm" id="filter-status" name="status" style="border-radius: 0;">
                    <option value="">All Statuses</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="partial" <?= ($filters['status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                    <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    <option value="voided" <?= ($filters['status'] ?? '') === 'voided' ? 'selected' : '' ?>>Voided</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-contact" class="form-label small text-muted mb-1">Contact</label>
                <select class="form-select form-select-sm" id="filter-contact" name="contact_id" style="border-radius: 0;">
                    <option value="">All Contacts</option>
                    <?php foreach ($contacts as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= ((string) ($filters['contact_id'] ?? '')) === (string) $c['id'] ? 'selected' : '' ?>>
                            <?= \DoubleE\Core\View::e($c['display_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm" style="border-radius: 0;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="/invoices" class="btn btn-outline-secondary btn-sm" style="border-radius: 0;">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 130px;">Document #</th>
                    <th>Contact</th>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 110px;">Due Date</th>
                    <th style="width: 130px;" class="text-end">Total</th>
                    <th style="width: 130px;" class="text-end">Balance Due</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 90px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No invoices found.
                            <?php if (!empty($filters)): ?>
                                Try adjusting your filters or
                            <?php endif; ?>
                            <a href="/invoices/create?type=invoice" class="text-decoration-none">create a new invoice</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <?php $invBadge = $statusBadges[$inv['status']] ?? 'secondary'; ?>
                        <tr>
                            <td>
                                <code class="text-dark"><?= \DoubleE\Core\View::e($inv['document_number']) ?></code>
                            </td>
                            <td>
                                <a href="/contacts/<?= (int) $inv['contact_id'] ?>" class="text-decoration-none">
                                    <?= \DoubleE\Core\View::e($inv['contact_name']) ?>
                                </a>
                            </td>
                            <td><?= \DoubleE\Core\View::e($inv['issue_date']) ?></td>
                            <td><?= \DoubleE\Core\View::e($inv['due_date']) ?></td>
                            <td class="text-end font-monospace"><?= number_format((float) $inv['total'], 2) ?></td>
                            <td class="text-end font-monospace">
                                <?php if ((float) $inv['balance_due'] > 0): ?>
                                    <span class="text-danger"><?= number_format((float) $inv['balance_due'], 2) ?></span>
                                <?php else: ?>
                                    <?= number_format((float) $inv['balance_due'], 2) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $invBadge ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($inv['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="/invoices/<?= (int) $inv['id'] ?>" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="View">
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

<?php if (!empty($pagination)):
    $view = new \DoubleE\Core\View();
    echo $view->partial('partials/pagination', ['pagination' => $pagination]);
endif; ?>
