<?php
/**
 * Fiscal Years index with expandable periods.
 *
 * Variables: $fiscalYears (each with nested 'periods' array)
 */

$statusBadges = [
    'open'    => 'success',
    'closing' => 'warning',
    'closed'  => 'secondary',
    'locked'  => 'warning',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Fiscal Years</h4>
    <a href="/fiscal-years/create" class="btn btn-dark" style="border-radius: 0;">
        <i class="bi bi-plus-lg me-1"></i> Create Fiscal Year
    </a>
</div>

<?php if (empty($fiscalYears)): ?>
    <div class="card border-0 shadow-sm" style="border-radius: 0;">
        <div class="card-body text-center text-muted py-5">
            No fiscal years found. Create your first fiscal year to get started.
        </div>
    </div>
<?php else: ?>
    <?php foreach ($fiscalYears as $index => $year):
        $yearBadge = $statusBadges[$year['status'] ?? ''] ?? 'secondary';
        $collapseId = 'periods-' . (int) $year['id'];
    ?>
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 0;">
            <div class="card-header bg-white py-3" style="border-radius: 0;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-sm btn-outline-secondary p-1"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= $collapseId ?>"
                                aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>"
                                style="border-radius: 0; width: 28px; height: 28px;">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div>
                            <h6 class="mb-0 fw-semibold"><?= \DoubleE\Core\View::e($year['name']) ?></h6>
                            <small class="text-muted">
                                <?= \DoubleE\Core\View::e($year['start_date']) ?>
                                &mdash;
                                <?= \DoubleE\Core\View::e($year['end_date']) ?>
                            </small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <?php if (($year['status'] ?? '') === 'open' && ($canClose ?? false)): ?>
                            <a href="/fiscal-years/<?= (int) $year['id'] ?>/close"
                               class="btn btn-sm btn-outline-dark"
                               style="border-radius: 0;">
                                <i class="bi bi-lock me-1"></i> Close Year
                            </a>
                        <?php endif; ?>
                        <span class="badge text-bg-<?= $yearBadge ?>" style="border-radius: 0; text-transform: capitalize;">
                            <?= \DoubleE\Core\View::e($year['status']) ?>
                        </span>
                        <?php if (!empty($year['periods'])): ?>
                            <span class="text-muted small">
                                <?= count($year['periods']) ?> period<?= count($year['periods']) !== 1 ? 's' : '' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="collapse <?= $index === 0 ? 'show' : '' ?>" id="<?= $collapseId ?>">
                <?php if (!empty($year['periods'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">#</th>
                                    <th>Period</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th style="width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($year['periods'] as $period):
                                    $periodBadge = $statusBadges[$period['status'] ?? ''] ?? 'secondary';
                                ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= (int) $period['period_number'] ?></td>
                                        <td class="fw-medium"><?= \DoubleE\Core\View::e($period['name']) ?></td>
                                        <td class="text-muted"><?= \DoubleE\Core\View::e($period['start_date']) ?></td>
                                        <td class="text-muted"><?= \DoubleE\Core\View::e($period['end_date']) ?></td>
                                        <td>
                                            <span class="badge text-bg-<?= $periodBadge ?>" style="border-radius: 0; text-transform: capitalize;">
                                                <?= \DoubleE\Core\View::e($period['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card-body text-center text-muted py-3">
                        No periods generated for this fiscal year.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
