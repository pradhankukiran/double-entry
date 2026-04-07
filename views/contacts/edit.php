<?php
/**
 * Edit Contact form.
 *
 * Variables: $contact (array), $address (array|null — first address row)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Contact</h4>
</div>

<form method="POST" action="/contacts/<?= (int) $contact['id'] ?>/update" novalidate>
    <?= \DoubleE\Core\Csrf::field() ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Contact Details -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
                <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                    <h6 class="mb-0">Contact Details</h6>
                </div>
                <div class="card-body p-4">
                    <!-- Type -->
                    <div class="mb-3">
                        <label class="form-label">Contact Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_customer" value="customer" <?= $contact['type'] === 'customer' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_customer">Customer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_vendor" value="vendor" <?= $contact['type'] === 'vendor' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_vendor">Vendor</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_both" value="both" <?= $contact['type'] === 'both' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_both">Both</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text"
                               class="form-control"
                               id="company_name"
                               name="company_name"
                               value="<?= \DoubleE\Core\View::e($contact['company_name'] ?? '') ?>"
                               placeholder="e.g. Acme Corporation"
                               style="border-radius: 0;">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text"
                                   class="form-control"
                                   id="first_name"
                                   name="first_name"
                                   value="<?= \DoubleE\Core\View::e($contact['first_name'] ?? '') ?>"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text"
                                   class="form-control"
                                   id="last_name"
                                   name="last_name"
                                   value="<?= \DoubleE\Core\View::e($contact['last_name'] ?? '') ?>"
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="display_name"
                               name="display_name"
                               value="<?= \DoubleE\Core\View::e($contact['display_name']) ?>"
                               required
                               placeholder="Name as it appears on invoices"
                               style="border-radius: 0;">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   name="email"
                                   value="<?= \DoubleE\Core\View::e($contact['email'] ?? '') ?>"
                                   placeholder="contact@example.com"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text"
                                   class="form-control"
                                   id="phone"
                                   name="phone"
                                   value="<?= \DoubleE\Core\View::e($contact['phone'] ?? '') ?>"
                                   placeholder="(555) 123-4567"
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tax_id" class="form-label">Tax ID / EIN</label>
                            <input type="text"
                                   class="form-control"
                                   id="tax_id"
                                   name="tax_id"
                                   value="<?= \DoubleE\Core\View::e($contact['tax_id'] ?? '') ?>"
                                   placeholder="e.g. 12-3456789"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="website" class="form-label">Website</label>
                            <input type="url"
                                   class="form-control"
                                   id="website"
                                   name="website"
                                   value="<?= \DoubleE\Core\View::e($contact['website'] ?? '') ?>"
                                   placeholder="https://example.com"
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_terms" class="form-label">Payment Terms (days)</label>
                            <input type="number"
                                   class="form-control"
                                   id="payment_terms"
                                   name="payment_terms"
                                   value="<?= (int) $contact['payment_terms'] ?>"
                                   min="0"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="credit_limit" class="form-label">Credit Limit</label>
                            <input type="number"
                                   class="form-control"
                                   id="credit_limit"
                                   name="credit_limit"
                                   value="<?= \DoubleE\Core\View::e($contact['credit_limit'] ?? '') ?>"
                                   step="0.01"
                                   placeholder="0.00"
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control"
                                  id="notes"
                                  name="notes"
                                  rows="2"
                                  placeholder="Internal notes about this contact"
                                  style="border-radius: 0;"><?= \DoubleE\Core\View::e($contact['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
                <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                    <h6 class="mb-0">Billing Address</h6>
                </div>
                <div class="card-body p-4">
                    <?php if ($address !== null): ?>
                        <input type="hidden" name="address_id" value="<?= (int) $address['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1</label>
                        <input type="text"
                               class="form-control"
                               id="address_line1"
                               name="address_line1"
                               value="<?= \DoubleE\Core\View::e($address['line1'] ?? '') ?>"
                               placeholder="Street address"
                               style="border-radius: 0;">
                    </div>

                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2</label>
                        <input type="text"
                               class="form-control"
                               id="address_line2"
                               name="address_line2"
                               value="<?= \DoubleE\Core\View::e($address['line2'] ?? '') ?>"
                               placeholder="Suite, unit, floor, etc."
                               style="border-radius: 0;">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="address_city" class="form-label">City</label>
                            <input type="text"
                                   class="form-control"
                                   id="address_city"
                                   name="address_city"
                                   value="<?= \DoubleE\Core\View::e($address['city'] ?? '') ?>"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="address_state" class="form-label">State / Province</label>
                            <input type="text"
                                   class="form-control"
                                   id="address_state"
                                   name="address_state"
                                   value="<?= \DoubleE\Core\View::e($address['state'] ?? '') ?>"
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6">
                            <label for="address_postal_code" class="form-label">Postal Code</label>
                            <input type="text"
                                   class="form-control"
                                   id="address_postal_code"
                                   name="address_postal_code"
                                   value="<?= \DoubleE\Core\View::e($address['postal_code'] ?? '') ?>"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="address_country" class="form-label">Country</label>
                            <input type="text"
                                   class="form-control"
                                   id="address_country"
                                   name="address_country"
                                   value="<?= \DoubleE\Core\View::e($address['country'] ?? 'US') ?>"
                                   maxlength="2"
                                   style="border-radius: 0;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between">
                <a href="/contacts/<?= (int) $contact['id'] ?>" class="btn btn-outline-secondary" style="border-radius: 0;">
                    <i class="bi bi-arrow-left me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-dark" style="border-radius: 0;">
                    <i class="bi bi-check-lg me-1"></i> Update Contact
                </button>
            </div>
        </div>
    </div>
</form>
