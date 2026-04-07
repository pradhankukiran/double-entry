<?php
/**
 * Settings — company information and accounting preferences.
 *
 * Variables: $settings (associative array of key => value)
 */

$s = function (string $key, string $default = '') use ($settings): string {
    return \DoubleE\Core\View::e($settings[$key] ?? $default);
};

$months = [
    1  => 'January',
    2  => 'February',
    3  => 'March',
    4  => 'April',
    5  => 'May',
    6  => 'June',
    7  => 'July',
    8  => 'August',
    9  => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Settings</h4>
</div>

<form method="POST" action="/settings" novalidate>
    <?= \DoubleE\Core\Csrf::field() ?>

    <!-- Company Information -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
        <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
            <h6 class="mb-0">Company Information</h6>
        </div>
        <div class="card-body p-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="company_name"
                           name="company_name"
                           value="<?= $s('company_name') ?>"
                           required
                           style="border-radius: 0;">
                </div>
                <div class="col-md-6">
                    <label for="legal_name" class="form-label">Legal Name</label>
                    <input type="text"
                           class="form-control"
                           id="legal_name"
                           name="legal_name"
                           value="<?= $s('legal_name') ?>"
                           style="border-radius: 0;">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="tax_id" class="form-label">Tax ID / EIN</label>
                    <input type="text"
                           class="form-control"
                           id="tax_id"
                           name="tax_id"
                           value="<?= $s('tax_id') ?>"
                           placeholder="e.g. 12-3456789"
                           style="border-radius: 0;">
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel"
                           class="form-control"
                           id="phone"
                           name="phone"
                           value="<?= $s('phone') ?>"
                           style="border-radius: 0;">
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           value="<?= $s('email') ?>"
                           style="border-radius: 0;">
                </div>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control"
                          id="address"
                          name="address"
                          rows="3"
                          style="border-radius: 0;"><?= $s('address') ?></textarea>
            </div>
            <div class="mb-0">
                <label for="website" class="form-label">Website</label>
                <input type="url"
                       class="form-control"
                       id="website"
                       name="website"
                       value="<?= $s('website') ?>"
                       placeholder="https://"
                       style="border-radius: 0;">
            </div>
        </div>
    </div>

    <!-- Accounting Preferences -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
        <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
            <h6 class="mb-0">Accounting Preferences</h6>
        </div>
        <div class="card-body p-4">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="default_currency" class="form-label">Default Currency</label>
                    <input type="text"
                           class="form-control"
                           id="default_currency"
                           name="default_currency"
                           value="<?= $s('default_currency', 'USD') ?>"
                           maxlength="3"
                           style="border-radius: 0;">
                    <div class="form-text">Three-letter ISO 4217 currency code.</div>
                </div>
                <div class="col-md-4">
                    <label for="fiscal_year_start" class="form-label">Fiscal Year Start Month</label>
                    <select class="form-select"
                            id="fiscal_year_start"
                            name="fiscal_year_start"
                            style="border-radius: 0;">
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>"
                                <?= ((int) ($settings['fiscal_year_start'] ?? 1)) === $num ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date_format" class="form-label">Date Format</label>
                    <select class="form-select"
                            id="date_format"
                            name="date_format"
                            style="border-radius: 0;">
                        <?php
                        $dateFormats = [
                            'Y-m-d'  => 'YYYY-MM-DD (2026-04-07)',
                            'm/d/Y'  => 'MM/DD/YYYY (04/07/2026)',
                            'd/m/Y'  => 'DD/MM/YYYY (07/04/2026)',
                            'd-M-Y'  => 'DD-Mon-YYYY (07-Apr-2026)',
                        ];
                        $currentFormat = $settings['date_format'] ?? 'Y-m-d';
                        foreach ($dateFormats as $fmt => $label):
                        ?>
                            <option value="<?= \DoubleE\Core\View::e($fmt) ?>"
                                <?= $currentFormat === $fmt ? 'selected' : '' ?>>
                                <?= \DoubleE\Core\View::e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row mb-0">
                <div class="col-md-4">
                    <label for="number_format" class="form-label">Number Format</label>
                    <select class="form-select"
                            id="number_format"
                            name="number_format"
                            style="border-radius: 0;">
                        <?php
                        $numberFormats = [
                            '1,234.56'  => '1,234.56',
                            '1.234,56'  => '1.234,56',
                            '1 234.56'  => '1 234.56',
                            '1 234,56'  => '1 234,56',
                        ];
                        $currentNumber = $settings['number_format'] ?? '1,234.56';
                        foreach ($numberFormats as $nf => $label):
                        ?>
                            <option value="<?= \DoubleE\Core\View::e($nf) ?>"
                                <?= $currentNumber === $nf ? 'selected' : '' ?>>
                                <?= \DoubleE\Core\View::e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Save -->
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-dark" style="border-radius: 0;">
            <i class="bi bi-check-lg me-1"></i> Save Settings
        </button>
    </div>
</form>
