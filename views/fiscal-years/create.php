<?php
/**
 * Create Fiscal Year form.
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Fiscal Year</h4>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Fiscal Year Details</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/fiscal-years" novalidate>
                    <?= \DoubleE\Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="name"
                               name="name"
                               placeholder="e.g. FY 2026"
                               required
                               style="border-radius: 0;">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control"
                                   id="start_date"
                                   name="start_date"
                                   required
                                   style="border-radius: 0;">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control"
                                   id="end_date"
                                   name="end_date"
                                   required
                                   style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="alert alert-light border mb-4" style="border-radius: 0;">
                        <i class="bi bi-info-circle me-1"></i>
                        12 monthly periods will be automatically generated based on the start and end dates.
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/fiscal-years" class="btn btn-outline-secondary" style="border-radius: 0;">Cancel</a>
                        <button type="submit" class="btn btn-dark" style="border-radius: 0;">Create Fiscal Year</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
