<?php
/**
 * General Ledger view — all posted journal entries grouped by entry.
 *
 * Variables: $entries (array of journal entries, each with 'lines'), $filters (date_from, date_to)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">General Ledger</h4>
</div>

<!-- Date Filter Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body py-3">
        <form method="GET" action="/ledger" class="row g-2 align-items-end">
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
                <a href="/ledger" class="btn btn-outline-secondary btn-sm" style="border-radius: 0;">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Ledger Entries -->
<?php if (empty($entries)): ?>
    <div class="card border-0 shadow-sm" style="border-radius: 0;">
        <div class="card-body text-center text-muted py-4">
            No posted journal entries found for the selected date range.
        </div>
    </div>
<?php else: ?>
    <?php foreach ($entries as $index => $entry): ?>
        <?php $isAlt = ($index % 2 === 1); ?>
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 0; <?= $isAlt ? 'background-color: #f8f9fa;' : '' ?>">
            <!-- Entry Header -->
            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center" style="border-radius: 0; <?= $isAlt ? 'background-color: #f8f9fa !important;' : '' ?>">
                <div>
                    <a href="/journal/<?= (int) $entry['id'] ?>" class="text-decoration-none">
                        <code class="text-dark fw-semibold"><?= \DoubleE\Core\View::e($entry['entry_number']) ?></code>
                    </a>
                    <span class="text-muted mx-2">|</span>
                    <span><?= \DoubleE\Core\View::e($entry['entry_date']) ?></span>
                    <span class="text-muted mx-2">|</span>
                    <span><?= \DoubleE\Core\View::e($entry['description']) ?></span>
                </div>
                <?php if (!empty($entry['reference'])): ?>
                    <span class="text-muted small">Ref: <?= \DoubleE\Core\View::e($entry['reference']) ?></span>
                <?php endif; ?>
            </div>
            <!-- Entry Lines -->
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 130px;">Account #</th>
                            <th>Account Name</th>
                            <th>Description</th>
                            <th style="width: 130px;" class="text-end">Debit</th>
                            <th style="width: 130px;" class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $lines = $entry['lines'] ?? [];
                        $entryDebit = 0;
                        $entryCredit = 0;
                        ?>
                        <?php foreach ($lines as $line): ?>
                            <?php
                            $entryDebit  += (float) $line['debit'];
                            $entryCredit += (float) $line['credit'];
                            ?>
                            <tr>
                                <td><code class="text-dark"><?= \DoubleE\Core\View::e($line['account_number'] ?? '') ?></code></td>
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
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-semibold">
                            <td colspan="3" class="text-end">Entry Total:</td>
                            <td class="text-end font-monospace"><?= number_format($entryDebit, 2) ?></td>
                            <td class="text-end font-monospace"><?= number_format($entryCredit, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
