<?php
/**
 * Create Tax Group form.
 *
 * Variables: $rates (all tax rates for checkbox selection)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">New Tax Group</h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Tax Group Details</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/tax/groups" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="name"
                                   name="name"
                                   placeholder="e.g. Combined Sales Tax"
                                   required
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="code"
                                   name="code"
                                   placeholder="e.g. CST"
                                   required
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tax Rates <span class="text-danger">*</span></label>
                        <p class="form-text mt-0 mb-2">Select the tax rates to include in this group and specify their application order.</p>

                        <?php if (empty($rates)): ?>
                            <div class="alert alert-secondary" style="border-radius: 0;">
                                No tax rates available. <a href="/tax/rates/create" class="alert-link">Create a tax rate</a> first.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">Include</th>
                                            <th>Tax Rate</th>
                                            <th style="width: 100px;">Code</th>
                                            <th style="width: 100px;" class="text-end">Rate %</th>
                                            <th style="width: 100px;">Order</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rates as $i => $rate): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="rate_ids[]"
                                                           value="<?= (int) $rate['id'] ?>"
                                                           id="rate_<?= (int) $rate['id'] ?>"
                                                           style="border-radius: 0;">
                                                </td>
                                                <td>
                                                    <label for="rate_<?= (int) $rate['id'] ?>" class="mb-0 fw-medium">
                                                        <?= \DoubleE\Core\View::e($rate['name']) ?>
                                                    </label>
                                                </td>
                                                <td><code class="text-dark"><?= \DoubleE\Core\View::e($rate['code']) ?></code></td>
                                                <td class="text-end font-monospace"><?= number_format((float) $rate['rate'], 2) ?>%</td>
                                                <td>
                                                    <input type="number"
                                                           class="form-control form-control-sm"
                                                           name="rate_orders[<?= (int) $rate['id'] ?>]"
                                                           value="<?= $i + 1 ?>"
                                                           min="1"
                                                           style="border-radius: 0;">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/tax" class="btn btn-outline-secondary" style="border-radius: 0;">Cancel</a>
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">Save Tax Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
