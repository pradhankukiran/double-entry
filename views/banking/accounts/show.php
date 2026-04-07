<?php
/**
 * Bank Account detail view.
 *
 * Variables: $account, $glAccount, $transactions
 */

$statusBadges = [
    'unmatched'  => 'warning',
    'matched'    => 'success',
    'excluded'   => 'secondary',
    'reconciled' => 'primary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <?= \DoubleE\Core\View::e($account['bank_name']) ?>
        <span class="text-muted fw-normal"> - <?= \DoubleE\Core\View::e($account['account_name']) ?></span>
        <?php if (!empty($account['last_four'])): ?>
            <small class="text-muted">(...<?= \DoubleE\Core\View::e($account['last_four']) ?>)</small>
        <?php endif; ?>
    </h4>
    <div class="d-flex gap-2">
        <a href="/banking/accounts" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Back to Bank Accounts
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Account Details Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Account Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Bank Name</div>
                    <div class="col-md-9 fw-medium"><?= \DoubleE\Core\View::e($account['bank_name']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Account Name</div>
                    <div class="col-md-9 fw-medium"><?= \DoubleE\Core\View::e($account['account_name']) ?></div>
                </div>
                <?php if (!empty($account['last_four'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Last 4 Digits</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($account['last_four']) ?></div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">GL Account</div>
                    <div class="col-md-9">
                        <?php if ($glAccount !== null): ?>
                            <a href="/accounts/<?= (int) $glAccount['id'] ?>" class="text-decoration-none">
                                <code class="text-dark"><?= \DoubleE\Core\View::e($glAccount['account_number']) ?></code>
                                <?= \DoubleE\Core\View::e($glAccount['name']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Account Type</div>
                    <div class="col-md-9">
                        <span class="badge text-bg-secondary" style="border-radius: 0;"><?= \DoubleE\Core\View::e(ucfirst(str_replace('_', ' ', $account['account_type'] ?? '-'))) ?></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Currency</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($account['currency_code'] ?? 'USD') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Balance</div>
                    <div class="col-md-9 fw-medium font-monospace"><?= number_format((float) ($account['balance'] ?? 0), 2) ?></div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center" style="border-radius: 0;">
                <h6 class="mb-0">Recent Transactions</h6>
                <span class="text-muted small"><?= count($transactions) ?> transaction<?= count($transactions) !== 1 ? 's' : '' ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 110px;">Date</th>
                            <th>Description</th>
                            <th class="text-end" style="width: 130px;">Amount</th>
                            <th style="width: 120px;">Reference</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 80px;">Match</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No transactions yet. Import a CSV file to get started.</td>
                            </tr>
                        <?php else: ?>
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
                                    <td>
                                        <?php
                                        $status = $txn['status'] ?? 'unmatched';
                                        $badge = $statusBadges[$status] ?? 'secondary';
                                        ?>
                                        <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;"><?= \DoubleE\Core\View::e(ucfirst($status)) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($txn['journal_entry_id'])): ?>
                                            <a href="/journal/<?= (int) $txn['journal_entry_id'] ?>" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="View Journal Entry">
                                                <i class="bi bi-link-45deg"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Quick Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-2">
                    <a href="/banking/accounts/<?= (int) $account['id'] ?>/import" class="btn btn-outline-dark" style="border-radius: 0;">
                        <i class="bi bi-upload me-2"></i> Import Transactions
                    </a>
                    <a href="/banking/reconcile/<?= (int) $account['id'] ?>/start" class="btn btn-outline-dark" style="border-radius: 0;">
                        <i class="bi bi-check2-square me-2"></i> Start Reconciliation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
