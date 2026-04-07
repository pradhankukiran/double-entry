<?php
/**
 * Reconciliation working screen.
 *
 * Variables: $reconciliation, $account, $unclearedItems, $clearedItems, $clearedBalance, $difference
 */

$statementBalance = (float) ($reconciliation['statement_balance'] ?? 0);
$clearedBalanceNum = (float) $clearedBalance;
$differenceNum = (float) $difference;
$isBalanced = bccomp((string) $differenceNum, '0.00', 2) === 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        Reconcile
        <span class="text-muted fw-normal"> - <?= \DoubleE\Core\View::e($account['bank_name'] . ' / ' . $account['account_name']) ?></span>
    </h4>
    <a href="/banking/reconcile" class="btn btn-outline-secondary" style="border-radius: 0;">
        <i class="bi bi-arrow-left me-1"></i> Back to Reconciliation
    </a>
</div>

<!-- Summary Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <div class="text-muted small">Statement Date</div>
                <div class="fw-medium"><?= \DoubleE\Core\View::e(date('M j, Y', strtotime($reconciliation['statement_date']))) ?></div>
            </div>
            <div class="col-md-2 text-center">
                <div class="text-muted small">Statement Balance</div>
                <div class="fw-bold font-monospace"><?= number_format($statementBalance, 2) ?></div>
            </div>
            <div class="col-md-2 text-center">
                <div class="text-muted small">Cleared Balance</div>
                <div class="fw-bold font-monospace" id="cleared-balance"><?= number_format($clearedBalanceNum, 2) ?></div>
            </div>
            <div class="col-md-3 text-center">
                <div class="text-muted small">Difference</div>
                <div class="fw-bold font-monospace fs-5 <?= $isBalanced ? 'text-success' : 'text-danger' ?>" id="difference-display">
                    <?= number_format($differenceNum, 2) ?>
                </div>
            </div>
            <div class="col-md-3 text-end">
                <form method="POST" action="/banking/reconcile/<?= (int) $reconciliation['id'] ?>/complete" class="d-inline" id="complete-form">
                    <?= \DoubleE\Core\Csrf::field() ?>
                    <button type="submit"
                            class="btn <?= $isBalanced ? 'btn-success' : 'btn-secondary' ?>"
                            style="border-radius: 0;"
                            id="complete-btn"
                            <?= !$isBalanced ? 'disabled' : '' ?>
                            onclick="return confirm('Complete this reconciliation? This action cannot be undone.');">
                        <i class="bi bi-check-lg me-1"></i> Complete Reconciliation
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Uncleared Items -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center" style="border-radius: 0;">
        <h6 class="mb-0">Uncleared Items</h6>
        <span class="badge text-bg-dark" style="border-radius: 0;" id="uncleared-count"><?= count($unclearedItems) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">
                        <input class="form-check-input" type="checkbox" id="select-all-uncleared" title="Select All" style="border-radius: 0;">
                    </th>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 100px;">Entry #</th>
                    <th>Description</th>
                    <th class="text-end" style="width: 120px;">Debit</th>
                    <th class="text-end" style="width: 120px;">Credit</th>
                </tr>
            </thead>
            <tbody id="uncleared-tbody">
                <?php if (empty($unclearedItems)): ?>
                    <tr id="no-uncleared-row">
                        <td colspan="6" class="text-center text-muted py-4">All items have been cleared.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($unclearedItems as $item): ?>
                        <?php
                        $amount = (float) ($item['amount'] ?? 0);
                        $debit = $amount > 0 ? $amount : 0;
                        $credit = $amount < 0 ? abs($amount) : 0;
                        ?>
                        <tr data-transaction-id="<?= (int) $item['id'] ?>">
                            <td>
                                <input class="form-check-input clear-checkbox"
                                       type="checkbox"
                                       value="<?= (int) $item['id'] ?>"
                                       data-amount="<?= \DoubleE\Core\View::e((string) $amount) ?>"
                                       style="border-radius: 0;">
                            </td>
                            <td><?= \DoubleE\Core\View::e(date('M j, Y', strtotime($item['transaction_date'] ?? $item['date'] ?? ''))) ?></td>
                            <td>
                                <?php if (!empty($item['entry_number'])): ?>
                                    <a href="/journal/<?= (int) ($item['journal_entry_id'] ?? 0) ?>" class="text-decoration-none">
                                        <code class="text-dark"><?= \DoubleE\Core\View::e($item['entry_number']) ?></code>
                                    </a>
                                <?php elseif (!empty($item['reference'])): ?>
                                    <code class="text-dark"><?= \DoubleE\Core\View::e($item['reference']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= \DoubleE\Core\View::e($item['description'] ?? '') ?></td>
                            <td class="text-end font-monospace"><?= $debit > 0 ? number_format($debit, 2) : '' ?></td>
                            <td class="text-end font-monospace"><?= $credit > 0 ? number_format($credit, 2) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cleared Items -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center" style="border-radius: 0;">
        <h6 class="mb-0">Cleared Items</h6>
        <span class="badge text-bg-success" style="border-radius: 0;" id="cleared-count"><?= count($clearedItems) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">
                        <input class="form-check-input" type="checkbox" id="select-all-cleared" title="Deselect All" style="border-radius: 0;">
                    </th>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 100px;">Entry #</th>
                    <th>Description</th>
                    <th class="text-end" style="width: 120px;">Debit</th>
                    <th class="text-end" style="width: 120px;">Credit</th>
                </tr>
            </thead>
            <tbody id="cleared-tbody">
                <?php if (empty($clearedItems)): ?>
                    <tr id="no-cleared-row">
                        <td colspan="6" class="text-center text-muted py-4">No items cleared yet. Check items above to clear them.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clearedItems as $item): ?>
                        <?php
                        $amount = (float) ($item['amount'] ?? 0);
                        $debit = $amount > 0 ? $amount : 0;
                        $credit = $amount < 0 ? abs($amount) : 0;
                        ?>
                        <tr data-transaction-id="<?= (int) $item['id'] ?>">
                            <td>
                                <input class="form-check-input clear-checkbox"
                                       type="checkbox"
                                       value="<?= (int) $item['id'] ?>"
                                       data-amount="<?= \DoubleE\Core\View::e((string) $amount) ?>"
                                       checked
                                       style="border-radius: 0;">
                            </td>
                            <td><?= \DoubleE\Core\View::e(date('M j, Y', strtotime($item['transaction_date'] ?? $item['date'] ?? ''))) ?></td>
                            <td>
                                <?php if (!empty($item['entry_number'])): ?>
                                    <a href="/journal/<?= (int) ($item['journal_entry_id'] ?? 0) ?>" class="text-decoration-none">
                                        <code class="text-dark"><?= \DoubleE\Core\View::e($item['entry_number']) ?></code>
                                    </a>
                                <?php elseif (!empty($item['reference'])): ?>
                                    <code class="text-dark"><?= \DoubleE\Core\View::e($item['reference']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= \DoubleE\Core\View::e($item['description'] ?? '') ?></td>
                            <td class="text-end font-monospace"><?= $debit > 0 ? number_format($debit, 2) : '' ?></td>
                            <td class="text-end font-monospace"><?= $credit > 0 ? number_format($credit, 2) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var reconciliationId = <?= (int) $reconciliation['id'] ?>;
    var statementBalance = <?= json_encode($statementBalance) ?>;
    var csrfToken = document.querySelector('input[name="_csrf_token"]').value;
    var pendingRequests = 0;

    /**
     * Update the summary bar display values.
     */
    function updateDisplay(clearedBalance, difference) {
        var clearedEl = document.getElementById('cleared-balance');
        var diffEl = document.getElementById('difference-display');
        var completeBtn = document.getElementById('complete-btn');

        clearedEl.textContent = formatNumber(clearedBalance);
        diffEl.textContent = formatNumber(difference);

        var isBalanced = Math.abs(difference) < 0.005;
        diffEl.className = 'fw-bold font-monospace fs-5 ' + (isBalanced ? 'text-success' : 'text-danger');
        completeBtn.disabled = !isBalanced;
        completeBtn.className = 'btn ' + (isBalanced ? 'btn-success' : 'btn-secondary');
        completeBtn.style.borderRadius = '0';
    }

    /**
     * Format a number with 2 decimal places and commas.
     */
    function formatNumber(num) {
        var n = parseFloat(num);
        if (isNaN(n)) n = 0;
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /**
     * Update the badge counts.
     */
    function updateCounts() {
        var unclearedCount = document.querySelectorAll('#uncleared-tbody .clear-checkbox').length;
        var clearedCount = document.querySelectorAll('#cleared-tbody .clear-checkbox').length;
        document.getElementById('uncleared-count').textContent = unclearedCount;
        document.getElementById('cleared-count').textContent = clearedCount;
    }

    /**
     * Send toggle request to server via AJAX.
     */
    function toggleTransaction(transactionId, checkbox) {
        pendingRequests++;
        checkbox.disabled = true;

        var formData = new FormData();
        formData.append('_csrf_token', csrfToken);
        formData.append('reconciliation_id', reconciliationId);
        formData.append('transaction_id', transactionId);

        fetch('/banking/reconcile/toggle', {
            method: 'POST',
            body: formData
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                updateDisplay(data.clearedBalance, data.difference);

                // Move row between tables
                var row = checkbox.closest('tr');
                if (data.cleared) {
                    // Move to cleared table
                    checkbox.checked = true;
                    var clearedTbody = document.getElementById('cleared-tbody');
                    var noRow = document.getElementById('no-cleared-row');
                    if (noRow) noRow.remove();
                    clearedTbody.appendChild(row);
                } else {
                    // Move to uncleared table
                    checkbox.checked = false;
                    var unclearedTbody = document.getElementById('uncleared-tbody');
                    var noRow = document.getElementById('no-uncleared-row');
                    if (noRow) noRow.remove();
                    unclearedTbody.appendChild(row);
                }

                updateCounts();
            }
        })
        .catch(function (err) {
            // Revert checkbox on error
            checkbox.checked = !checkbox.checked;
            console.error('Toggle failed:', err);
        })
        .finally(function () {
            pendingRequests--;
            checkbox.disabled = false;
        });
    }

    /**
     * Handle individual checkbox changes.
     */
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('clear-checkbox')) return;
        var transactionId = parseInt(e.target.value, 10);
        toggleTransaction(transactionId, e.target);
    });

    /**
     * Select/deselect all uncleared items.
     */
    var selectAllUncleared = document.getElementById('select-all-uncleared');
    if (selectAllUncleared) {
        selectAllUncleared.addEventListener('change', function () {
            var checkboxes = document.querySelectorAll('#uncleared-tbody .clear-checkbox:not(:checked)');
            checkboxes.forEach(function (cb) {
                if (!cb.disabled) {
                    var transactionId = parseInt(cb.value, 10);
                    toggleTransaction(transactionId, cb);
                }
            });
            selectAllUncleared.checked = false;
        });
    }

    /**
     * Deselect all cleared items.
     */
    var selectAllCleared = document.getElementById('select-all-cleared');
    if (selectAllCleared) {
        selectAllCleared.addEventListener('change', function () {
            var checkboxes = document.querySelectorAll('#cleared-tbody .clear-checkbox:checked');
            checkboxes.forEach(function (cb) {
                if (!cb.disabled) {
                    var transactionId = parseInt(cb.value, 10);
                    toggleTransaction(transactionId, cb);
                }
            });
            selectAllCleared.checked = false;
        });
    }

    /**
     * Prevent form submission while requests are pending.
     */
    document.getElementById('complete-form').addEventListener('submit', function (e) {
        if (pendingRequests > 0) {
            e.preventDefault();
            alert('Please wait for all pending changes to complete.');
        }
    });
});
</script>
