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
                    <div class="bg-light p-3">
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
                    <div class="bg-light p-3">
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
                    <div class="bg-light p-3">
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
                    <div class="bg-light p-3">
                        <i class="bi bi-wallet2 text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart + Recent Activity -->
<div class="row g-4 mt-2 align-items-stretch">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm dashboard-panel dashboard-chart-card h-100">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <h6 class="fw-semibold mb-1">Revenue vs Expenses</h6>
                        <p class="text-muted small mb-0">Six-month performance snapshot</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="dashboard-chart-wrap">
                    <canvas id="revenueExpenseChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm dashboard-panel dashboard-activity h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-semibold mb-0">Recent Activity</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($activity)): ?>
                    <p class="text-muted text-center py-4 mb-0">No recent activity</p>
                <?php else: ?>
                    <?php
                        $actionIcons = [
                            'login'  => 'bi-box-arrow-in-right',
                            'create' => 'bi-plus-circle',
                            'post'   => 'bi-check-circle',
                            'void'   => 'bi-x-circle',
                            'update' => 'bi-pencil',
                            'delete' => 'bi-trash',
                        ];
                        $actionColors = [
                            'login'  => 'text-info',
                            'create' => 'text-success',
                            'post'   => 'text-success',
                            'void'   => 'text-danger',
                            'update' => 'text-primary',
                            'delete' => 'text-danger',
                        ];
                    ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($activity as $item):
                            $action = $item['action'] ?? '';
                            $icon   = $actionIcons[$action] ?? 'bi-activity';
                            $color  = $actionColors[$action] ?? 'text-secondary';
                        ?>
                            <div class="list-group-item py-3 px-3" style="border-radius: 0;">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="mt-1">
                                        <i class="bi <?= $icon ?> <?= $color ?> fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="small">
                                            <?php if (!empty($item['entity_url'])): ?>
                                                <?php
                                                    $desc = \DoubleE\Core\View::e($item['description'] ?? '');
                                                    $url  = \DoubleE\Core\View::e($item['entity_url']);
                                                    // Link the entity reference within the description
                                                    $entityType = $item['entity_type'] ?? '';
                                                    $linkLabels = [
                                                        'journal_entry' => 'journal entry',
                                                        'invoice'       => 'invoice',
                                                        'payment'       => 'payment',
                                                        'account'       => 'account',
                                                        'contact'       => 'contact',
                                                        'bill'          => 'bill',
                                                    ];
                                                    $label = $linkLabels[$entityType] ?? '';
                                                    // Find the entity label+number portion and wrap it in a link
                                                    if ($label !== '' && preg_match('/(' . preg_quote($label, '/') . ' \S+)$/i', $desc, $m)) {
                                                        $desc = str_replace($m[1], '<a href="' . $url . '" class="fw-medium text-decoration-none">' . $m[1] . '</a>', $desc);
                                                    }
                                                ?>
                                                <?= $desc ?>
                                            <?php else: ?>
                                                <?= \DoubleE\Core\View::e($item['description'] ?? '') ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small mt-1"><?= \DoubleE\Core\View::e($item['time_ago'] ?? '') ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white border-top text-center">
                <a href="/audit" class="small text-decoration-none">View Full Audit Trail <i class="bi bi-arrow-right"></i></a>
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
                    borderWidth: 1,
                    borderRadius: 0,
                    borderSkipped: false,
                    maxBarThickness: 32
                },
                {
                    label: 'Expenses',
                    data: expenses,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1,
                    borderRadius: 0,
                    borderSkipped: false,
                    maxBarThickness: 32
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            layout: {
                padding: {
                    top: 8,
                    right: 8,
                    bottom: 0,
                    left: 0
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'start',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'rect',
                        boxWidth: 10,
                        padding: 18
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#6c757d'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(33, 37, 41, 0.08)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6c757d',
                        padding: 10,
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
