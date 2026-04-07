<div class="row g-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Total Revenue</p>
                        <h4 class="fw-bold mb-0">$0.00</h4>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Total Expenses</p>
                        <h4 class="fw-bold mb-0">$0.00</h4>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-graph-down-arrow text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Accounts Receivable</p>
                        <h4 class="fw-bold mb-0">$0.00</h4>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-arrow-down-left text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Cash Balance</p>
                        <h4 class="fw-bold mb-0">$0.00</h4>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-wallet2 text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-semibold mb-0">Revenue vs Expenses</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueExpenseChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-semibold mb-0">Recent Activity</h6>
            </div>
            <div class="card-body">
                <p class="text-muted text-center py-4">No recent transactions</p>
            </div>
        </div>
    </div>
</div>
