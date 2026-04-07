<?php
/**
 * Create Account form.
 *
 * Variables: $types, $subtypes, $parentOptions
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">New Account</h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Account Details</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/accounts" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="account_number"
                                   name="account_number"
                                   placeholder="e.g. 1000"
                                   required
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-8">
                            <label for="name" class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="name"
                                   name="name"
                                   placeholder="e.g. Cash and Cash Equivalents"
                                   required
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control"
                                  id="description"
                                  name="description"
                                  rows="2"
                                  placeholder="Optional description of the account's purpose"
                                  style="border-radius: 0;"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="account_type_id" class="form-label">Account Type <span class="text-danger">*</span></label>
                            <select class="form-select"
                                    id="account_type_id"
                                    name="account_type_id"
                                    required
                                    style="border-radius: 0;">
                                <option value="">-- Select Type --</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= (int) $type['id'] ?>"><?= \DoubleE\Core\View::e($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="account_subtype_id" class="form-label">Account Sub-Type</label>
                            <select class="form-select"
                                    id="account_subtype_id"
                                    name="account_subtype_id"
                                    style="border-radius: 0;">
                                <option value="">-- Select Sub-Type --</option>
                                <?php foreach ($subtypes as $st): ?>
                                    <option value="<?= (int) $st['id'] ?>" data-type-id="<?= (int) $st['account_type_id'] ?>"><?= \DoubleE\Core\View::e($st['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Account</label>
                        <select class="form-select"
                                id="parent_id"
                                name="parent_id"
                                style="border-radius: 0;">
                            <option value="">-- None (Top Level) --</option>
                            <?php foreach ($parentOptions as $opt): ?>
                                <option value="<?= (int) $opt['id'] ?>"><?= \DoubleE\Core\View::e($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="currency_code" class="form-label">Currency</label>
                            <input type="text"
                                   class="form-control"
                                   id="currency_code"
                                   name="currency_code"
                                   value="USD"
                                   maxlength="3"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-4">
                            <label for="opening_balance" class="form-label">Opening Balance</label>
                            <input type="number"
                                   class="form-control"
                                   id="opening_balance"
                                   name="opening_balance"
                                   value="0.00"
                                   step="0.01"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_header"
                                       name="is_header"
                                       value="1"
                                       style="border-radius: 0;">
                                <label class="form-check-label" for="is_header">
                                    Header Account
                                </label>
                                <div class="form-text">Header accounts group child accounts and cannot hold transactions.</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/accounts" class="btn btn-outline-secondary" style="border-radius: 0;">Cancel</a>
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
