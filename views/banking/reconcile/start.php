<?php
/**
 * Start reconciliation form.
 *
 * Variables: $account
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Start Reconciliation</h4>
    <a href="/banking/reconcile" class="btn btn-outline-secondary" style="border-radius: 0;">
        <i class="bi bi-arrow-left me-1"></i> Back to Reconciliation
    </a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">
                    <?= \DoubleE\Core\View::e($account['bank_name']) ?>
                    <span class="text-muted fw-normal"> - <?= \DoubleE\Core\View::e($account['account_name']) ?></span>
                </h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">Enter the ending date and balance from your bank statement to begin reconciliation.</p>

                <form method="POST" action="/banking/reconcile" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>
                    <input type="hidden" name="bank_account_id" value="<?= (int) $account['id'] ?>">

                    <div class="mb-3">
                        <label for="statement_date" class="form-label">Statement Date <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="statement_date"
                               name="statement_date"
                               required
                               style="border-radius: 0;">
                    </div>

                    <div class="mb-3">
                        <label for="statement_balance" class="form-label">Statement Ending Balance <span class="text-danger">*</span></label>
                        <div class="input-group" style="border-radius: 0;">
                            <span class="input-group-text" style="border-radius: 0;">$</span>
                            <input type="number"
                                   class="form-control"
                                   id="statement_balance"
                                   name="statement_balance"
                                   step="0.01"
                                   placeholder="0.00"
                                   required
                                   style="border-radius: 0;">
                        </div>
                        <div class="form-text">Enter the ending balance exactly as it appears on your bank statement.</div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/banking/reconcile" class="btn btn-outline-secondary" style="border-radius: 0;">Cancel</a>
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">
                            <i class="bi bi-check2-square me-1"></i> Begin Reconciliation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
