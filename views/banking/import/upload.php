<?php
/**
 * CSV upload form for bank transaction import.
 *
 * Variables: $account
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Import Transactions</h4>
    <a href="/banking/accounts/<?= (int) $account['id'] ?>" class="btn btn-outline-secondary" style="border-radius: 0;">
        <i class="bi bi-arrow-left me-1"></i> Back to Account
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">
                    Upload CSV
                    <span class="text-muted fw-normal"> - <?= \DoubleE\Core\View::e($account['bank_name'] . ' / ' . $account['account_name']) ?></span>
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/banking/import/preview" enctype="multipart/form-data" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>
                    <input type="hidden" name="bank_account_id" value="<?= (int) $account['id'] ?>">

                    <div class="mb-4">
                        <label for="csv_file" class="form-label">CSV File <span class="text-danger">*</span></label>
                        <input type="file"
                               class="form-control"
                               id="csv_file"
                               name="csv_file"
                               accept=".csv"
                               required
                               style="border-radius: 0;">
                    </div>

                    <div class="alert alert-light border" style="border-radius: 0;">
                        <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-1"></i> Instructions</h6>
                        <ul class="mb-0 small">
                            <li>Upload a CSV file exported from your bank.</li>
                            <li>The file should include a header row with column names.</li>
                            <li>You will map the columns to the required fields on the next step.</li>
                            <li>Duplicate transactions will be automatically detected and skipped.</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/banking/accounts/<?= (int) $account['id'] ?>" class="btn btn-outline-secondary" style="border-radius: 0;">Cancel</a>
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">
                            <i class="bi bi-upload me-1"></i> Upload and Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
