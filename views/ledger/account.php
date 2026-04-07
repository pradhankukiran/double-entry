<?php
/**
 * Account Ledger — per-account view with running balance.
 *
 * Variables: $account (array), $ledger (array of line items with running_balance), $filters (date_from, date_to)
 */

$totalDebit  = 0;
$totalCredit = 0;
foreach ($ledger as $row) {
    $totalDebit  += (float) $row['debit'];
    $totalCredit += (float) $row['credit'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <code class="text-dark me-2"><?= \DoubleE\Core\View::e($account['account_number']) ?></code>
        <?= \DoubleE\Core\View::e($account['name']) ?>
        <span class="text-muted fw-normal fs-6 ms-2">Account Ledger</span>
    </h4>
    <div class="d-flex gap-2">
        <a href="/ledger" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> General Ledger
        </a>
        <a href="/accounts/<?= (int) $account['id'] ?>" class="btn btn-outline-dark" style="border-radius: 0;">
            <i class="bi bi-eye me-1"></i> Account Details
        </a>
    </div>
</div>

<!-- Date Filter Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body py-3">
        <form method="GET" action="/ledger/account/<?= (int) $account['id'] ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filter-date-from" class="form-label small text-muted mb-1">From Date</label>
                <input type="date"
                       class="form-control form-control-sm"
                       id="filter-date-from"
                       name="date_from"
                       value="<?= \DoubleE\Core\View::e($filters['date_from'] ?? '') ?>"
                       style="border-radius: 0;">
            </div>
            <div class="col-md-3">
                <label for="filter-date-to" class="form-label small text-muted mb-1">To Date</label>
                <input type="date"
                       class="form-control form-control-sm"
                       id="filter-date-to"
                       name="date_to"
                       value="<?= \DoubleE\Core\View::e($filters['date_to'] ?? '') ?>"
                       style="border-radius: 0;">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm" style="border-radius: 0;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="/ledger/account/<?= (int) $account['id'] ?>" class="btn btn-outline-secondary btn-sm" style="border-radius: 0;">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Account Ledger Table -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 120px;">Entry #</th>
                    <th>Description</th>
                    <th style="width: 130px;" class="text-end">Debit</th>
                    <th style="width: 130px;" class="text-end">Credit</th>
                    <th style="width: 150px;" class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ledger)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No transactions found for this account in the selected date range.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ledger as $row): ?>
                        <tr>
                            <td><?= \DoubleE\Core\View::e($row['entry_date'] ?? '') ?></td>
                            <td>
                                <a href="/journal/<?= (int) $row['journal_entry_id'] ?>" class="text-decoration-none">
                                    <code class="text-dark"><?= \DoubleE\Core\View::e($row['entry_number'] ?? '') ?></code>
                                </a>
                            </td>
                            <td><?= \DoubleE\Core\View::e($row['description'] ?? $row['entry_description'] ?? '') ?></td>
                            <td class="text-end font-monospace">
                                <?= (float) $row['debit'] > 0 ? number_format((float) $row['debit'], 2) : '' ?>
                            </td>
                            <td class="text-end font-monospace">
                                <?= (float) $row['credit'] > 0 ? number_format((float) $row['credit'], 2) : '' ?>
                            </td>
                            <td class="text-end font-monospace fw-medium" style="font-variant-numeric: tabular-nums;">
                                <?= number_format((float) ($row['running_balance'] ?? 0), 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($ledger)): ?>
            <tfoot>
                <tr class="table-light fw-semibold">
                    <td colspan="3" class="text-end">Totals:</td>
                    <td class="text-end font-monospace"><?= number_format($totalDebit, 2) ?></td>
                    <td class="text-end font-monospace"><?= number_format($totalCredit, 2) ?></td>
                    <td class="text-end font-monospace" style="font-variant-numeric: tabular-nums;">
                        <?= number_format((float) (end($ledger)['running_balance'] ?? 0), 2) ?>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
