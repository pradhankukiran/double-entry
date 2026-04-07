<?php
/**
 * Create Recurring Transaction template form.
 *
 * Variables: $accounts (leaf accounts), $accountsByType (grouped for optgroups)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">New Recurring Transaction</h4>
</div>

<form method="POST" action="/recurring" id="recurring-form" novalidate>
    <?= \DoubleE\Core\Csrf::field() ?>

    <!-- Template Details -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
        <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
            <h6 class="mb-0">Template Details</h6>
        </div>
        <div class="card-body p-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="name"
                           name="name"
                           placeholder="e.g. Monthly Rent Payment"
                           required
                           style="border-radius: 0;">
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="type" name="type" required style="border-radius: 0;">
                        <option value="">-- Select --</option>
                        <option value="journal_entry">Journal Entry</option>
                        <option value="invoice">Invoice</option>
                        <option value="bill">Bill</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="frequency" class="form-label">Frequency <span class="text-danger">*</span></label>
                    <select class="form-select" id="frequency" name="frequency" required style="border-radius: 0;">
                        <option value="">-- Select --</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="biweekly">Bi-Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annually">Annually</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date"
                           class="form-control"
                           id="start_date"
                           name="start_date"
                           value="<?= date('Y-m-d') ?>"
                           required
                           style="border-radius: 0;">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date"
                           class="form-control"
                           id="end_date"
                           name="end_date"
                           style="border-radius: 0;">
                    <div class="form-text">Leave blank for no end date.</div>
                </div>
                <div class="col-md-6">
                    <label for="description" class="form-label">Description</label>
                    <input type="text"
                           class="form-control"
                           id="description"
                           name="description"
                           placeholder="Optional description for generated transactions"
                           style="border-radius: 0;">
                </div>
            </div>
            <div class="mb-0">
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           id="auto_post"
                           name="auto_post"
                           value="1"
                           style="border-radius: 0;">
                    <label class="form-check-label" for="auto_post">
                        Auto-Post
                    </label>
                    <div class="form-text">Automatically post generated transactions instead of saving as draft.</div>
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
                        <th style="width: 35%;">Account <span class="text-danger">*</span></th>
                        <th style="width: 25%;">Description</th>
                        <th style="width: 15%;" class="text-end">Debit</th>
                        <th style="width: 15%;" class="text-end">Credit</th>
                        <th style="width: 10%;" class="text-center">Remove</th>
                    </tr>
                </thead>
                <tbody id="lines-body">
                    <!-- Lines added dynamically by journal-entry.js -->
                </tbody>
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td colspan="2" class="text-end">Totals:</td>
                        <td class="text-end font-monospace" id="total-debits">0.00</td>
                        <td class="text-end font-monospace" id="total-credits">0.00</td>
                        <td></td>
                    </tr>
                    <tr id="difference-row">
                        <td colspan="2" class="text-end fw-semibold">Difference:</td>
                        <td colspan="2" class="text-end font-monospace fw-semibold" id="difference-amount">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between">
        <a href="/recurring" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-dark" style="border-radius: 0;">
            <i class="bi bi-check-lg me-1"></i> Save Template
        </button>
    </div>
</form>

<!-- Account data for JS -->
<script>
    window.__jeAccounts = <?= json_encode(
        array_map(function ($type, $accts) {
            return [
                'type' => $type,
                'accounts' => array_map(function ($a) {
                    return [
                        'id' => (int) $a['id'],
                        'number' => $a['account_number'],
                        'name' => $a['name'],
                    ];
                }, $accts),
            ];
        }, array_keys($accountsByType), array_values($accountsByType)),
        JSON_HEX_TAG | JSON_HEX_APOS
    ) ?>;
</script>
<script src="/assets/js/journal-entry.js"></script>
