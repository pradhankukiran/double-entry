<?php
/**
 * Bank Accounts listing.
 *
 * Variables: $accounts
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Bank Accounts</h4>
    <a href="/banking/accounts/create" class="btn btn-dark" style="border-radius: 0;">
        <i class="bi bi-plus-lg me-1"></i> Add Bank Account
    </a>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bank Name</th>
                    <th>Account Name</th>
                    <th>GL Account</th>
                    <th>Type</th>
                    <th class="text-end">Balance</th>
                    <th>Last Imported</th>
                    <th>Last Reconciled</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No bank accounts found. Add your first bank account to get started.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($accounts as $acct): ?>
                        <tr>
                            <td class="fw-medium"><?= \DoubleE\Core\View::e($acct['bank_name']) ?></td>
                            <td>
                                <?= \DoubleE\Core\View::e($acct['account_name']) ?>
                                <?php if (!empty($acct['last_four'])): ?>
                                    <span class="text-muted small">(...<?= \DoubleE\Core\View::e($acct['last_four']) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($acct['gl_account_number'])): ?>
                                    <code class="text-dark"><?= \DoubleE\Core\View::e($acct['gl_account_number']) ?></code>
                                    <span class="text-muted small"><?= \DoubleE\Core\View::e($acct['gl_account_name'] ?? '') ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-secondary" style="border-radius: 0;"><?= \DoubleE\Core\View::e($acct['account_type'] ?? '-') ?></span>
                            </td>
                            <td class="text-end font-monospace">
                                <?= number_format((float) ($acct['balance'] ?? 0), 2) ?>
                            </td>
                            <td>
                                <?php if (!empty($acct['last_imported_at'])): ?>
                                    <?= \DoubleE\Core\View::e(date('M j, Y', strtotime($acct['last_imported_at']))) ?>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($acct['last_reconciled_at'])): ?>
                                    <?= \DoubleE\Core\View::e(date('M j, Y', strtotime($acct['last_reconciled_at']))) ?>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/banking/accounts/<?= (int) $acct['id'] ?>" class="btn btn-sm btn-outline-dark me-1" style="border-radius: 0;" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/banking/accounts/<?= (int) $acct['id'] ?>/import" class="btn btn-sm btn-outline-dark me-1" style="border-radius: 0;" title="Import">
                                    <i class="bi bi-upload"></i>
                                </a>
                                <a href="/banking/reconcile/<?= (int) $acct['id'] ?>/start" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="Reconcile">
                                    <i class="bi bi-check2-square"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
