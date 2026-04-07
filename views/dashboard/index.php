<?php
/**
 * Dashboard — KPIs, revenue vs expenses chart, and recent activity.
 *
 * Variables: $kpis (array), $chartData (array), $activity (array)
 */

$revenue    = (float) ($kpis['total_revenue'] ?? 0);
$expenses   = (float) ($kpis['total_expenses'] ?? 0);
$receivable = (float) ($kpis['accounts_receivable'] ?? 0);
$cash       = (float) ($kpis['cash_balance'] ?? 0);
?>

<!-- KPI Cards -->
<div class="row g-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Total Revenue</p>
                        <h4 class="fw-bold mb-0">$<?= number_format($revenue, 2) ?></h4>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Total Expenses</p>
                        <h4 class="fw-bold mb-0">$<?= number_format($expenses, 2) ?></h4>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-graph-down-arrow text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Accounts Receivable</p>
                        <h4 class="fw-bold mb-0">$<?= number_format($receivable, 2) ?></h4>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-arrow-down-left text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Cash Balance</p>
                        <h4 class="fw-bold mb-0">$<?= number_format($cash, 2) ?></h4>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-wallet2 text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart + Recent Activity -->
<div class="row g-4 mt-2">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom" style="border-radius: 0;">
                <h6 class="fw-semibold mb-0">Revenue vs Expenses</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueExpenseChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom" style="border-radius: 0;">
                <h6 class="fw-semibold mb-0">Recent Activity</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($activity)): ?>
                    <p class="text-muted text-center py-4 mb-0">No recent transactions</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($activity as $item): ?>
                            <a href="/journal/<?= (int) $item['id'] ?>"
                               class="list-group-item list-group-item-action py-3 px-3" style="border-radius: 0;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-medium small">
                                            <code class="text-dark"><?= \DoubleE\Core\View::e($item['entry_number'] ?? '') ?></code>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <?= \DoubleE\Core\View::e($item['description'] ?? '') ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small"><?= \DoubleE\Core\View::e($item['entry_date'] ?? '') ?></div>
                                        <?php
                                            $sBadges = ['draft' => 'secondary', 'posted' => 'success', 'voided' => 'danger'];
                                            $sColor = $sBadges[$item['status'] ?? ''] ?? 'secondary';
                                        ?>
                                        <span class="badge text-bg-<?= $sColor ?> mt-1" style="border-radius: 0;">
                                            <?= ucfirst(\DoubleE\Core\View::e($item['status'] ?? '')) ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function() {
    const chartData = <?= json_encode($chartData ?? [], JSON_HEX_TAG | JSON_HEX_APOS) ?>;
    const labels    = chartData.labels   || [];
    const revenue   = chartData.revenue  || [];
    const expenses  = chartData.expenses || [];

    const ctx = document.getElementById('revenueExpenseChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenue,
                    backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: expenses,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
})();
</script>
