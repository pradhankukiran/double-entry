<?php
/**
 * Financial Reports — selection dashboard.
 *
 * Variables: (none required)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Financial Reports</h4>
</div>

<div class="row g-4">
    <!-- Trial Balance -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 0;">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <div class="bg-light d-inline-flex p-3" style="border-radius: 0;">
                        <i class="bi bi-balance-scale fs-3 text-primary"></i>
                    </div>
                </div>
                <h6 class="fw-semibold mb-1">Trial Balance</h6>
                <p class="text-muted small flex-grow-1">Verify that debits equal credits across all accounts at a point in time.</p>
                <a href="/reports/trial-balance" class="btn btn-dark btn-sm mt-2" style="border-radius: 0;">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Generate
                </a>
            </div>
        </div>
    </div>

    <!-- Balance Sheet -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 0;">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <div class="bg-light d-inline-flex p-3" style="border-radius: 0;">
                        <i class="bi bi-clipboard-data fs-3 text-success"></i>
                    </div>
                </div>
                <h6 class="fw-semibold mb-1">Balance Sheet</h6>
                <p class="text-muted small flex-grow-1">Assets, Liabilities, and Equity at a specific date.</p>
                <a href="/reports/balance-sheet" class="btn btn-dark btn-sm mt-2" style="border-radius: 0;">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Generate
                </a>
            </div>
        </div>
    </div>

    <!-- Income Statement -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 0;">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <div class="bg-light d-inline-flex p-3" style="border-radius: 0;">
                        <i class="bi bi-graph-up fs-3 text-warning"></i>
                    </div>
                </div>
                <h6 class="fw-semibold mb-1">Income Statement</h6>
                <p class="text-muted small flex-grow-1">Revenue and Expenses (P&amp;L) over a date range.</p>
                <a href="/reports/income-statement" class="btn btn-dark btn-sm mt-2" style="border-radius: 0;">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Generate
                </a>
            </div>
        </div>
    </div>

    <!-- Cash Flow Statement -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 0;">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <div class="bg-light d-inline-flex p-3" style="border-radius: 0;">
                        <i class="bi bi-cash-coin fs-3 text-info"></i>
                    </div>
                </div>
                <h6 class="fw-semibold mb-1">Cash Flow Statement</h6>
                <p class="text-muted small flex-grow-1">Cash inflows and outflows over a date range.</p>
                <a href="/reports/cash-flow" class="btn btn-dark btn-sm mt-2" style="border-radius: 0;">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Generate
                </a>
            </div>
        </div>
    </div>
</div>
