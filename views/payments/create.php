<?php
/**
 * Create Payment form (Receive Payment / Make Payment).
 *
 * Variables: $paymentType (string: 'received' or 'made'), $contacts (array), $bankAccounts (array)
 */

$isReceived = ($paymentType === 'received');
$title      = $isReceived ? 'Receive Payment' : 'Make Payment';

// Group bank accounts by type for dropdown
$accountsByType = [];
foreach ($bankAccounts as $acct) {
    $typeName = $acct['type_name'] ?? 'Other';
    $accountsByType[$typeName][] = $acct;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><?= $title ?></h4>
</div>

<form method="POST" action="/payments" id="payment-form" novalidate>
    <?= \DoubleE\Core\Csrf::field() ?>
    <input type="hidden" name="type" value="<?= \DoubleE\Core\View::e($paymentType) ?>">

    <div class="row">
        <div class="col-lg-8">
            <!-- Payment Details -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
                <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                    <h6 class="mb-0">Payment Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="contact_id" class="form-label"><?= $isReceived ? 'Customer' : 'Vendor' ?> <span class="text-danger">*</span></label>
                            <select class="form-select"
                                    id="contact_id"
                                    name="contact_id"
                                    required
                                    style="border-radius: 0;">
                                <option value="">-- Select <?= $isReceived ? 'Customer' : 'Vendor' ?> --</option>
                                <?php foreach ($contacts as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>"><?= \DoubleE\Core\View::e($c['display_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="deposit_account_id" class="form-label"><?= $isReceived ? 'Deposit To' : 'Pay From' ?> Account <span class="text-danger">*</span></label>
                            <select class="form-select"
                                    id="deposit_account_id"
                                    name="deposit_account_id"
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
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control"
                                   id="payment_date"
                                   name="payment_date"
                                   value="<?= date('Y-m-d') ?>"
                                   required
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-4">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control"
                                   id="amount"
                                   name="amount"
                                   step="0.01"
                                   min="0.01"
                                   required
                                   placeholder="0.00"
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-4">
                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select"
                                    id="payment_method"
                                    name="payment_method"
                                    required
                                    style="border-radius: 0;">
                                <option value="">-- Select Method --</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference / Check Number</label>
                        <input type="text"
                               class="form-control"
                               id="reference"
                               name="reference"
                               placeholder="e.g. CHK-1234, TXN-5678"
                               style="border-radius: 0;">
                    </div>

                    <div class="mb-0">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control"
                                  id="notes"
                                  name="notes"
                                  rows="2"
                                  placeholder="Optional payment notes"
                                  style="border-radius: 0;"></textarea>
                    </div>
                </div>
            </div>

            <!-- Invoice Allocation -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
                <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                    <h6 class="mb-0">Apply to Outstanding <?= $isReceived ? 'Invoices' : 'Bills' ?></h6>
                </div>
                <div id="allocation-section">
                    <div class="card-body py-3 text-center text-muted" id="allocation-placeholder">
                        <p class="mb-0">Select a <?= $isReceived ? 'customer' : 'vendor' ?> to see outstanding <?= $isReceived ? 'invoices' : 'bills' ?>.</p>
                    </div>
                    <div class="table-responsive d-none" id="allocation-table-wrapper">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 140px;">Document #</th>
                                    <th style="width: 110px;">Due Date</th>
                                    <th style="width: 120px;" class="text-end">Total</th>
                                    <th style="width: 120px;" class="text-end">Balance Due</th>
                                    <th style="width: 140px;" class="text-end">Amount to Apply</th>
                                </tr>
                            </thead>
                            <tbody id="allocation-body">
                                <!-- Populated via JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-semibold">
                                    <td colspan="4" class="text-end">Total Applied:</td>
                                    <td class="text-end font-monospace" id="total-applied">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="card-body py-3 text-center text-muted d-none" id="allocation-empty">
                        <p class="mb-0">No outstanding <?= $isReceived ? 'invoices' : 'bills' ?> for this contact.</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between">
                <a href="/payments" class="btn btn-outline-secondary" style="border-radius: 0;">
                    <i class="bi bi-arrow-left me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-dark" style="border-radius: 0;">
                    <i class="bi bi-check-lg me-1"></i> Save Payment
                </button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactSelect = document.getElementById('contact_id');
    const placeholder   = document.getElementById('allocation-placeholder');
    const tableWrapper  = document.getElementById('allocation-table-wrapper');
    const emptyMessage  = document.getElementById('allocation-empty');
    const tbody         = document.getElementById('allocation-body');
    const totalApplied  = document.getElementById('total-applied');
    const paymentType   = '<?= \DoubleE\Core\View::e($paymentType) ?>';

    function recalcApplied() {
        let total = 0;
        document.querySelectorAll('.alloc-amount').forEach(function(input) {
            total += parseFloat(input.value) || 0;
        });
        totalApplied.textContent = total.toFixed(2);
    }

    contactSelect.addEventListener('change', function() {
        const contactId = this.value;
        tbody.innerHTML = '';
        placeholder.classList.add('d-none');
        tableWrapper.classList.add('d-none');
        emptyMessage.classList.add('d-none');

        if (!contactId) {
            placeholder.classList.remove('d-none');
            return;
        }

        // Fetch unpaid invoices for the selected contact
        const docType = paymentType === 'received' ? 'invoice' : 'bill';
        fetch('/api/contacts/' + contactId + '/unpaid-invoices?type=' + docType)
            .then(function(r) { return r.json(); })
            .catch(function() { return []; })
            .then(function(invoices) {
                if (!invoices || invoices.length === 0) {
                    emptyMessage.classList.remove('d-none');
                    return;
                }

                invoices.forEach(function(inv) {
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td><code class="text-dark">' + escapeHtml(inv.document_number) + '</code></td>' +
                        '<td>' + escapeHtml(inv.due_date) + '</td>' +
                        '<td class="text-end font-monospace">' + parseFloat(inv.total).toFixed(2) + '</td>' +
                        '<td class="text-end font-monospace">' + parseFloat(inv.balance_due).toFixed(2) + '</td>' +
                        '<td>' +
                            '<input type="hidden" name="alloc_invoice_id[]" value="' + inv.id + '">' +
                            '<input type="number" class="form-control form-control-sm text-end alloc-amount" ' +
                                'name="alloc_amount[]" value="0.00" min="0" max="' + inv.balance_due + '" step="0.01" style="border-radius: 0;">' +
                        '</td>';
                    tbody.appendChild(tr);
                });

                document.querySelectorAll('.alloc-amount').forEach(function(input) {
                    input.addEventListener('input', recalcApplied);
                });

                tableWrapper.classList.remove('d-none');
            });
    });

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
});
</script>
