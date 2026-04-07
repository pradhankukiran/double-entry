<?php
/**
 * Import results summary.
 *
 * Variables: $account, $imported, $duplicates, $transactions
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Import Results</h4>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body p-4 text-center">
                <div class="display-6 fw-bold text-dark"><?= (int) $imported ?></div>
                <div class="text-muted">Transactions Imported</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body p-4 text-center">
                <div class="display-6 fw-bold text-muted"><?= (int) $duplicates ?></div>
                <div class="text-muted">Duplicates Skipped</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body p-4 text-center">
                <div class="display-6 fw-bold text-dark"><?= (int) $imported + (int) $duplicates ?></div>
                <div class="text-muted">Total Rows Processed</div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($transactions)): ?>
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
        <h6 class="mb-0">Imported Transactions</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 110px;">Date</th>
                    <th>Description</th>
                    <th class="text-end" style="width: 130px;">Amount</th>
                    <th style="width: 120px;">Reference</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $txn): ?>
                    <tr>
                        <td><?= \DoubleE\Core\View::e(date('M j, Y', strtotime($txn['transaction_date']))) ?></td>
                        <td><?= \DoubleE\Core\View::e($txn['description']) ?></td>
                        <td class="text-end font-monospace <?= (float) $txn['amount'] < 0 ? 'text-danger' : '' ?>">
                            <?= number_format((float) $txn['amount'], 2) ?>
                        </td>
                        <td>
                            <?php if (!empty($txn['reference'])): ?>
                                <code class="text-dark"><?= \DoubleE\Core\View::e($txn['reference']) ?></code>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-end gap-2">
    <a href="/banking/accounts/<?= (int) $account['id'] ?>/import" class="btn btn-outline-secondary" style="border-radius: 0;">
        <i class="bi bi-upload me-1"></i> Import More
    </a>
    <a href="/banking/accounts/<?= (int) $account['id'] ?>" class="btn btn-dark" style="border-radius: 0;">
        <i class="bi bi-arrow-right me-1"></i> Go to Bank Account
    </a>
</div>
