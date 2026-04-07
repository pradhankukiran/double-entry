<?php
/**
 * Journal Entries — list view with filters.
 *
 * Variables: $entries (array), $filters (array with status, date_from, date_to, search)
 */

$statusBadges = [
    'draft'  => 'secondary',
    'posted' => 'success',
    'voided' => 'danger',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Journal Entries</h4>
    <?php if ($canCreate ?? false): ?>
    <a href="/journal/create" class="btn btn-dark" style="border-radius: 0;">
        <i class="bi bi-plus-lg me-1"></i> New Entry
    </a>
    <?php endif; ?>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body py-3">
        <form method="GET" action="/journal" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label for="filter-status" class="form-label small text-muted mb-1">Status</label>
                <select class="form-select form-select-sm" id="filter-status" name="status" style="border-radius: 0;">
                    <option value="">All Statuses</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="posted" <?= ($filters['status'] ?? '') === 'posted' ? 'selected' : '' ?>>Posted</option>
                    <option value="voided" <?= ($filters['status'] ?? '') === 'voided' ? 'selected' : '' ?>>Voided</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-date-from" class="form-label small text-muted mb-1">Date From</label>
                <input type="date"
                       class="form-control form-control-sm"
                       id="filter-date-from"
                       name="date_from"
                       value="<?= \DoubleE\Core\View::e($filters['date_from'] ?? '') ?>"
                       style="border-radius: 0;">
            </div>
            <div class="col-md-2">
                <label for="filter-date-to" class="form-label small text-muted mb-1">Date To</label>
                <input type="date"
                       class="form-control form-control-sm"
                       id="filter-date-to"
                       name="date_to"
                       value="<?= \DoubleE\Core\View::e($filters['date_to'] ?? '') ?>"
                       style="border-radius: 0;">
            </div>
            <div class="col-md-3">
                <label for="filter-search" class="form-label small text-muted mb-1">Search</label>
                <input type="text"
                       class="form-control form-control-sm"
                       id="filter-search"
                       name="search"
                       value="<?= \DoubleE\Core\View::e($filters['search'] ?? '') ?>"
                       placeholder="Description, reference, entry #..."
                       style="border-radius: 0;">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm" style="border-radius: 0;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="/journal" class="btn btn-outline-secondary btn-sm" style="border-radius: 0;">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Entries Table -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 120px;">Entry #</th>
                    <th style="width: 110px;">Date</th>
                    <th>Description</th>
                    <th style="width: 120px;">Reference</th>
                    <th style="width: 130px;" class="text-end">Debit Total</th>
                    <th style="width: 130px;" class="text-end">Credit Total</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 90px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entries)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No journal entries found.
                            <?php if (!empty($filters)): ?>
                                Try adjusting your filters or
                            <?php endif; ?>
                            <a href="/journal/create" class="text-decoration-none">create a new entry</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entries as $entry): ?>
                        <?php $badge = $statusBadges[$entry['status']] ?? 'secondary'; ?>
                        <tr>
                            <td>
                                <code class="text-dark"><?= \DoubleE\Core\View::e($entry['entry_number']) ?></code>
                            </td>
                            <td><?= \DoubleE\Core\View::e($entry['entry_date']) ?></td>
                            <td><?= \DoubleE\Core\View::e($entry['description']) ?></td>
                            <td class="text-muted"><?= \DoubleE\Core\View::e($entry['reference'] ?? '-') ?></td>
                            <td class="text-end font-monospace"><?= number_format((float) ($entry['total_debit'] ?? 0), 2) ?></td>
                            <td class="text-end font-monospace"><?= number_format((float) ($entry['total_credit'] ?? 0), 2) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($entry['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="/journal/<?= (int) $entry['id'] ?>" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="View">
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
