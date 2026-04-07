<?php
/**
 * Create Invoice / Bill form.
 *
 * Variables: $documentType (string: 'invoice' or 'bill'), $contacts (array), $accounts (array)
 */

$isInvoice = ($documentType === 'invoice');
$title     = $isInvoice ? 'New Invoice' : 'New Bill';

// Group accounts by type for dropdown optgroups
$accountsByType = [];
foreach ($accounts as $acct) {
    $typeName = $acct['type_name'] ?? 'Other';
    $accountsByType[$typeName][] = $acct;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><?= $title ?></h4>
</div>

<form method="POST" action="/invoices" id="invoice-form" novalidate>
    <?= \DoubleE\Core\Csrf::field() ?>
    <input type="hidden" name="document_type" value="<?= \DoubleE\Core\View::e($documentType) ?>">

    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
        <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
            <h6 class="mb-0"><?= $isInvoice ? 'Invoice' : 'Bill' ?> Details</h6>
        </div>
        <div class="card-body p-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="contact_id" class="form-label"><?= $isInvoice ? 'Customer' : 'Vendor' ?> <span class="text-danger">*</span></label>
                    <select class="form-select"
                            id="contact_id"
                            name="contact_id"
                            required
                            style="border-radius: 0;">
                        <option value="">-- Select <?= $isInvoice ? 'Customer' : 'Vendor' ?> --</option>
                        <?php foreach ($contacts as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"><?= \DoubleE\Core\View::e($c['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="ar_ap_account_id" class="form-label"><?= $isInvoice ? 'Accounts Receivable' : 'Accounts Payable' ?> Account <span class="text-danger">*</span></label>
                    <select class="form-select"
                            id="ar_ap_account_id"
                            name="ar_ap_account_id"
                            required
                            style="border-radius: 0;">
                        <option value="">-- Select Account --</option>
                        <?php foreach ($accountsByType as $typeName => $accts): ?>
                            <optgroup label="<?= \DoubleE\Core\View::e($typeName) ?>">
                                <?php foreach ($accts as $acct): ?>
                                    <option value="<?= (int) $acct['id'] ?>">
                                        <?= \DoubleE\Core\View::e($acct['account_number'] . ' - ' . $acct['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                    <input type="date"
                           class="form-control"
                           id="issue_date"
                           name="issue_date"
                           value="<?= date('Y-m-d') ?>"
                           required
                           style="border-radius: 0;">
                </div>
                <div class="col-md-4">
                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                    <input type="date"
                           class="form-control"
                           id="due_date"
                           name="due_date"
                           value="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                           required
                           style="border-radius: 0;">
                </div>
                <div class="col-md-4">
                    <label for="reference" class="form-label">Reference / PO Number</label>
                    <input type="text"
                           class="form-control"
                           id="reference"
                           name="reference"
                           placeholder="e.g. PO-12345"
                           style="border-radius: 0;">
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center" style="border-radius: 0;">
            <h6 class="mb-0">Line Items</h6>
            <button type="button" class="btn btn-outline-dark btn-sm" id="btn-add-line" style="border-radius: 0;">
                <i class="bi bi-plus-lg me-1"></i> Add Line
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="lines-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">Description <span class="text-danger">*</span></th>
                        <th style="width: 25%;">Account <span class="text-danger">*</span></th>
                        <th style="width: 10%;" class="text-end">Qty</th>
                        <th style="width: 13%;" class="text-end">Unit Price</th>
                        <th style="width: 10%;" class="text-end">Tax</th>
                        <th style="width: 12%;" class="text-end">Line Total</th>
                        <th style="width: 5%;" class="text-center">Remove</th>
                    </tr>
                </thead>
                <tbody id="lines-body">
                    <!-- Initial row -->
                    <tr class="line-row" data-index="0">
                        <td>
                            <input type="text" class="form-control form-control-sm" name="line_description[]" placeholder="Description" style="border-radius: 0;">
                        </td>
                        <td>
                            <select class="form-select form-select-sm" name="line_account[]" style="border-radius: 0;">
                                <option value="">-- Account --</option>
                                <?php foreach ($accountsByType as $typeName => $accts): ?>
                                    <optgroup label="<?= \DoubleE\Core\View::e($typeName) ?>">
                                        <?php foreach ($accts as $acct): ?>
                                            <option value="<?= (int) $acct['id'] ?>">
                                                <?= \DoubleE\Core\View::e($acct['account_number'] . ' - ' . $acct['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end line-qty" name="line_quantity[]" value="1" min="0" step="0.01" style="border-radius: 0;">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end line-price" name="line_unit_price[]" value="0.00" min="0" step="0.01" style="border-radius: 0;">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end line-tax" name="line_tax[]" value="0.00" min="0" step="0.01" style="border-radius: 0;">
                        </td>
                        <td class="text-end font-monospace line-total">0.00</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" style="border-radius: 0;" title="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="5" class="text-end fw-semibold">Subtotal:</td>
                        <td class="text-end font-monospace fw-semibold" id="inv-subtotal">0.00</td>
                        <td></td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="5" class="text-end fw-semibold">Tax:</td>
                        <td class="text-end font-monospace fw-semibold" id="inv-tax">0.00</td>
                        <td></td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="5" class="text-end fw-bold">Total:</td>
                        <td class="text-end font-monospace fw-bold fs-5" id="inv-total">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Notes & Terms -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
        <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
            <h6 class="mb-0">Notes &amp; Terms</h6>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control"
                              id="notes"
                              name="notes"
                              rows="3"
                              placeholder="Notes visible on the invoice"
                              style="border-radius: 0;"></textarea>
                </div>
                <div class="col-md-6">
                    <label for="terms" class="form-label">Terms &amp; Conditions</label>
                    <textarea class="form-control"
                              id="terms"
                              name="terms"
                              rows="3"
                              placeholder="Payment terms, late fees, etc."
                              style="border-radius: 0;"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between">
        <a href="/invoices" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Cancel
        </a>
        <div class="d-flex gap-2">
            <button type="submit" name="action" value="draft" class="btn btn-outline-dark" style="border-radius: 0;">
                <i class="bi bi-file-earmark me-1"></i> Save as Draft
            </button>
            <button type="submit" name="action" value="post" class="btn btn-dark" style="border-radius: 0;">
                <i class="bi bi-check-lg me-1"></i> Save &amp; Post
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('lines-body');
    const btnAdd = document.getElementById('btn-add-line');

    function recalculate() {
        let subtotal = 0;
        let tax = 0;
        document.querySelectorAll('.line-row').forEach(function(row) {
            const qty = parseFloat(row.querySelector('.line-qty').value) || 0;
            const price = parseFloat(row.querySelector('.line-price').value) || 0;
            const lineTax = parseFloat(row.querySelector('.line-tax').value) || 0;
            const lineTotal = Math.round(qty * price * 100) / 100;
            row.querySelector('.line-total').textContent = lineTotal.toFixed(2);
            subtotal += lineTotal;
            tax += lineTax;
        });
        document.getElementById('inv-subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('inv-tax').textContent = tax.toFixed(2);
        document.getElementById('inv-total').textContent = (subtotal + tax).toFixed(2);
    }

    function attachLineEvents(row) {
        row.querySelector('.line-qty').addEventListener('input', recalculate);
        row.querySelector('.line-price').addEventListener('input', recalculate);
        row.querySelector('.line-tax').addEventListener('input', recalculate);
        row.querySelector('.btn-remove-line').addEventListener('click', function() {
            if (document.querySelectorAll('.line-row').length > 1) {
                row.remove();
                recalculate();
            }
        });
    }

    // Attach events to initial row
    document.querySelectorAll('.line-row').forEach(attachLineEvents);

    btnAdd.addEventListener('click', function() {
        const first = document.querySelector('.line-row');
        const clone = first.cloneNode(true);
        clone.querySelectorAll('input').forEach(function(input) {
            if (input.type === 'number') {
                input.value = input.name.includes('quantity') ? '1' : '0.00';
            } else {
                input.value = '';
            }
        });
        clone.querySelectorAll('select').forEach(function(sel) {
            sel.selectedIndex = 0;
        });
        clone.querySelector('.line-total').textContent = '0.00';
        tbody.appendChild(clone);
        attachLineEvents(clone);
    });
});
</script>
