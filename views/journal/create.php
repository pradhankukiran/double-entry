<?php
/**
 * Create Journal Entry form.
 *
 * Variables: $accounts (leaf accounts for dropdown, each with id, account_number, name, type_name)
 */

// Group accounts by type for the dropdown optgroups
$accountsByType = [];
foreach ($accounts as $acct) {
    $typeName = $acct['type_name'] ?? 'Other';
    $accountsByType[$typeName][] = $acct;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">New Journal Entry</h4>
</div>

<form method="POST" action="/journal" id="journal-entry-form" novalidate>
    <?= \DoubleE\Core\Csrf::field() ?>

    <!-- Header Fields -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
        <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
            <h6 class="mb-0">Entry Details</h6>
        </div>
        <div class="card-body p-4">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="entry_date" class="form-label">Entry Date <span class="text-danger">*</span></label>
                    <input type="date"
                           class="form-control"
                           id="entry_date"
                           name="entry_date"
                           value="<?= date('Y-m-d') ?>"
                           required
                           style="border-radius: 0;">
                </div>
                <div class="col-md-4">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="description"
                           name="description"
                           placeholder="e.g. Monthly rent payment"
                           required
                           style="border-radius: 0;">
                </div>
                <div class="col-md-4">
                    <label for="reference" class="form-label">Reference</label>
                    <input type="text"
                           class="form-control"
                           id="reference"
                           name="reference"
                           placeholder="e.g. INV-001, CHK-1234"
                           style="border-radius: 0;">
                </div>
            </div>
            <div class="mb-0">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control"
                          id="notes"
                          name="notes"
                          rows="2"
                          placeholder="Optional notes or memo"
                          style="border-radius: 0;"></textarea>
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
        <a href="/journal" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Cancel
        </a>
        <div class="d-flex gap-2">
            <button type="submit" name="action" value="draft" class="btn btn-outline-dark btn-submit" style="border-radius: 0;">
                <i class="bi bi-file-earmark me-1"></i> Save as Draft
            </button>
            <button type="submit" name="action" value="post" class="btn btn-dark btn-submit" style="border-radius: 0;">
                <i class="bi bi-check-lg me-1"></i> Save &amp; Post
            </button>
        </div>
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
