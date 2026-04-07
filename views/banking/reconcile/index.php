<?php
/**
 * Bank reconciliation index — list accounts with reconciliation status.
 *
 * Variables: $accounts
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Bank Reconciliation</h4>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bank Name</th>
                    <th>Account Name</th>
                    <th>GL Account</th>
                    <th>Last Reconciled</th>
                    <th>Last Reconciled Balance</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No bank accounts found. Add a bank account first.</td>
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
                                <?php if (!empty($acct['last_reconciled_at'])): ?>
                                    <?= \DoubleE\Core\View::e(date('M j, Y', strtotime($acct['last_reconciled_at']))) ?>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td class="font-monospace">
                                <?php if (isset($acct['last_reconciled_balance'])): ?>
                                    <?= number_format((float) $acct['last_reconciled_balance'], 2) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/banking/reconcile/<?= (int) $acct['id'] ?>/start" class="btn btn-sm btn-dark" style="border-radius: 0;">
                                    <i class="bi bi-check2-square me-1"></i> Start Reconciliation
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
