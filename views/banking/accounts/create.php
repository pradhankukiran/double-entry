<?php
/**
 * Create Bank Account form.
 *
 * Variables: $glAccounts
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Bank Account</h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Bank Account Details</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/banking/accounts" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="bank_name"
                                   name="bank_name"
                                   placeholder="e.g. Chase, Bank of America"
                                   required
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="account_name"
                                   name="account_name"
                                   placeholder="e.g. Business Checking"
                                   required
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="last_four" class="form-label">Last 4 Digits</label>
                            <input type="text"
                                   class="form-control"
                                   id="last_four"
                                   name="last_four"
                                   placeholder="e.g. 1234"
                                   maxlength="4"
                                   pattern="[0-9]{4}"
                                   style="border-radius: 0;">
                            <div class="form-text">Last 4 digits of the account number for identification.</div>
                        </div>
                        <div class="col-md-8">
                            <label for="gl_account_id" class="form-label">GL Account <span class="text-danger">*</span></label>
                            <select class="form-select"
                                    id="gl_account_id"
                                    name="gl_account_id"
                                    required
                                    style="border-radius: 0;">
                                <option value="">-- Select GL Account --</option>
                                <?php foreach ($glAccounts as $gl): ?>
                                    <option value="<?= (int) $gl['id'] ?>">
                                        <?= \DoubleE\Core\View::e($gl['account_number'] . ' - ' . $gl['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Link to a bank-type account in the Chart of Accounts.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                            <select class="form-select"
                                    id="account_type"
                                    name="account_type"
                                    required
                                    style="border-radius: 0;">
                                <option value="">-- Select Type --</option>
                                <option value="checking">Checking</option>
                                <option value="savings">Savings</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="money_market">Money Market</option>
                                <option value="line_of_credit">Line of Credit</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="currency_code" class="form-label">Currency</label>
                            <input type="text"
                                   class="form-control"
                                   id="currency_code"
                                   name="currency_code"
                                   value="USD"
                                   maxlength="3"
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/banking/accounts" class="btn btn-outline-secondary" style="border-radius: 0;">Cancel</a>
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">Save Bank Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
