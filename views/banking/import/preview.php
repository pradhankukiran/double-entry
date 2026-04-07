<?php
/**
 * CSV column mapping preview.
 *
 * Variables: $account, $headers, $previewRows, $suggestions, $tempPath
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Map Columns</h4>
    <a href="/banking/accounts/<?= (int) $account['id'] ?>/import" class="btn btn-outline-secondary" style="border-radius: 0;">
        <i class="bi bi-arrow-left me-1"></i> Re-upload
    </a>
</div>

<div class="row">
    <div class="col-lg-12">
        <!-- CSV Preview -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">
                    CSV Preview
                    <span class="text-muted fw-normal"> - <?= \DoubleE\Core\View::e($account['bank_name'] . ' / ' . $account['account_name']) ?></span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted small">#</th>
                            <?php foreach ($headers as $idx => $header): ?>
                                <th class="small">
                                    <span class="badge text-bg-dark me-1" style="border-radius: 0;">Col <?= $idx ?></span>
                                    <?= \DoubleE\Core\View::e($header) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewRows as $rowIdx => $row): ?>
                            <tr>
                                <td class="text-muted small"><?= $rowIdx + 1 ?></td>
                                <?php foreach ($headers as $colIdx => $h): ?>
                                    <td class="small"><?= \DoubleE\Core\View::e($row[$colIdx] ?? '') ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Column Mapping Form -->
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Column Mapping</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/banking/import/import" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>
                    <input type="hidden" name="bank_account_id" value="<?= (int) $account['id'] ?>">
                    <input type="hidden" name="temp_path" value="<?= \DoubleE\Core\View::e($tempPath) ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_col" class="form-label">Date Column <span class="text-danger">*</span></label>
                            <select class="form-select" id="date_col" name="date_col" required style="border-radius: 0;">
                                <option value="">-- Select Column --</option>
                                <?php foreach ($headers as $idx => $header): ?>
                                    <option value="<?= $idx ?>" <?= (isset($suggestions['date']) && $suggestions['date'] === $idx) ? 'selected' : '' ?>>
                                        Col <?= $idx ?>: <?= \DoubleE\Core\View::e($header) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="description_col" class="form-label">Description Column <span class="text-danger">*</span></label>
                            <select class="form-select" id="description_col" name="description_col" required style="border-radius: 0;">
                                <option value="">-- Select Column --</option>
                                <?php foreach ($headers as $idx => $header): ?>
                                    <option value="<?= $idx ?>" <?= (isset($suggestions['description']) && $suggestions['description'] === $idx) ? 'selected' : '' ?>>
                                        Col <?= $idx ?>: <?= \DoubleE\Core\View::e($header) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount Format <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="amount_mode" id="amount_mode_single" value="single" checked style="border-radius: 0;">
                            <label class="form-check-label" for="amount_mode_single">
                                Amount is a single column (positive for deposits, negative for withdrawals)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="amount_mode" id="amount_mode_separate" value="separate" style="border-radius: 0;">
                            <label class="form-check-label" for="amount_mode_separate">
                                Separate Debit and Credit columns
                            </label>
                        </div>
                    </div>

                    <!-- Single amount column -->
                    <div class="row mb-3" id="single-amount-section">
                        <div class="col-md-6">
                            <label for="amount_col" class="form-label">Amount Column <span class="text-danger">*</span></label>
                            <select class="form-select" id="amount_col" name="amount_col" style="border-radius: 0;">
                                <option value="">-- Select Column --</option>
                                <?php foreach ($headers as $idx => $header): ?>
                                    <option value="<?= $idx ?>" <?= (isset($suggestions['amount']) && $suggestions['amount'] === $idx) ? 'selected' : '' ?>>
                                        Col <?= $idx ?>: <?= \DoubleE\Core\View::e($header) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Separate debit/credit columns -->
                    <div class="row mb-3 d-none" id="separate-amount-section">
                        <div class="col-md-6">
                            <label for="debit_col" class="form-label">Debit Column <span class="text-danger">*</span></label>
                            <select class="form-select" id="debit_col" name="debit_col" style="border-radius: 0;">
                                <option value="">-- Select Column --</option>
                                <?php foreach ($headers as $idx => $header): ?>
                                    <option value="<?= $idx ?>" <?= (isset($suggestions['debit']) && $suggestions['debit'] === $idx) ? 'selected' : '' ?>>
                                        Col <?= $idx ?>: <?= \DoubleE\Core\View::e($header) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="credit_col" class="form-label">Credit Column <span class="text-danger">*</span></label>
                            <select class="form-select" id="credit_col" name="credit_col" style="border-radius: 0;">
                                <option value="">-- Select Column --</option>
                                <?php foreach ($headers as $idx => $header): ?>
                                    <option value="<?= $idx ?>" <?= (isset($suggestions['credit']) && $suggestions['credit'] === $idx) ? 'selected' : '' ?>>
                                        Col <?= $idx ?>: <?= \DoubleE\Core\View::e($header) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reference_col" class="form-label">Reference / Check # Column</label>
                            <select class="form-select" id="reference_col" name="reference_col" style="border-radius: 0;">
                                <option value="">-- None --</option>
                                <?php foreach ($headers as $idx => $header): ?>
                                    <option value="<?= $idx ?>" <?= (isset($suggestions['reference']) && $suggestions['reference'] === $idx) ? 'selected' : '' ?>>
                                        Col <?= $idx ?>: <?= \DoubleE\Core\View::e($header) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/banking/accounts/<?= (int) $account['id'] ?>/import" class="btn btn-outline-secondary" style="border-radius: 0;">Cancel</a>
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">
                            <i class="bi bi-database-add me-1"></i> Import Transactions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var singleRadio = document.getElementById('amount_mode_single');
    var separateRadio = document.getElementById('amount_mode_separate');
    var singleSection = document.getElementById('single-amount-section');
    var separateSection = document.getElementById('separate-amount-section');

    function toggleAmountMode() {
        if (singleRadio.checked) {
            singleSection.classList.remove('d-none');
            separateSection.classList.add('d-none');
        } else {
            singleSection.classList.add('d-none');
            separateSection.classList.remove('d-none');
        }
    }

    singleRadio.addEventListener('change', toggleAmountMode);
    separateRadio.addEventListener('change', toggleAmountMode);
    toggleAmountMode();
});
</script>
