<?php
/**
 * Create Tax Rate form.
 *
 * Variables: $accounts (leaf accounts for tax liability select)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">New Tax Rate</h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Tax Rate Details</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/tax/rates" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="name"
                                   name="name"
                                   placeholder="e.g. State Sales Tax"
                                   required
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="code"
                                   name="code"
                                   placeholder="e.g. SST"
                                   required
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="rate" class="form-label">Rate (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control"
                                       id="rate"
                                       name="rate"
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       required
                                       style="border-radius: 0;">
                                <span class="input-group-text" style="border-radius: 0;">%</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label for="tax_account_id" class="form-label">Tax Liability Account <span class="text-danger">*</span></label>
                            <select class="form-select"
                                    id="tax_account_id"
                                    name="tax_account_id"
                                    required
                                    style="border-radius: 0;">
                                <option value="">-- Select Account --</option>
                                <?php foreach ($accounts as $acct): ?>
                                    <option value="<?= (int) $acct['id'] ?>">
                                        <?= \DoubleE\Core\View::e($acct['account_number']) ?> - <?= \DoubleE\Core\View::e($acct['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   checked
                                   style="border-radius: 0;">
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                            <div class="form-text">Inactive tax rates will not appear in transaction forms.</div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/tax" class="btn btn-outline-secondary" style="border-radius: 0;">Cancel</a>
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">Save Tax Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
