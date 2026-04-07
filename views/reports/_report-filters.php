<?php
/**
 * Shared report filter partial.
 *
 * Variables:
 *   $filterType   — 'point_in_time' or 'date_range'
 *   $reportUrl    — the GET action URL for the form (e.g. '/reports/trial-balance')
 *   $pdfUrl       — the Export PDF URL (e.g. '/reports/export/trial-balance')
 *   $asOfDate     — (optional) current as-of date value
 *   $fromDate     — (optional) current from-date value
 *   $toDate       — (optional) current to-date value
 */
$filterType = $filterType ?? 'point_in_time';
$reportUrl  = $reportUrl ?? '';
$pdfUrl     = $pdfUrl ?? '';
?>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body py-3">
        <form method="GET" action="<?= \DoubleE\Core\View::e($reportUrl) ?>" class="row g-2 align-items-end">
            <?php if ($filterType === 'point_in_time'): ?>
                <div class="col-md-3">
                    <label for="filter-as-of-date" class="form-label small text-muted mb-1">As of Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="filter-as-of-date"
                           name="as_of_date"
                           value="<?= \DoubleE\Core\View::e($asOfDate ?? date('Y-m-d')) ?>"
                           style="border-radius: 0;">
                </div>
            <?php else: ?>
                <div class="col-md-3">
                    <label for="filter-from-date" class="form-label small text-muted mb-1">From Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="filter-from-date"
                           name="from_date"
                           value="<?= \DoubleE\Core\View::e($fromDate ?? date('Y-m-01')) ?>"
                           style="border-radius: 0;">
                </div>
                <div class="col-md-3">
                    <label for="filter-to-date" class="form-label small text-muted mb-1">To Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="filter-to-date"
                           name="to_date"
                           value="<?= \DoubleE\Core\View::e($toDate ?? date('Y-m-d')) ?>"
                           style="border-radius: 0;">
                </div>
            <?php endif; ?>

            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm" style="border-radius: 0;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="<?= \DoubleE\Core\View::e($reportUrl) ?>" class="btn btn-outline-secondary btn-sm" style="border-radius: 0;">
                    Clear
                </a>
                <?php if ($pdfUrl !== ''): ?>
                    <?php
                    // Build PDF link with current filter params
                    $pdfParams = [];
                    if ($filterType === 'point_in_time' && !empty($asOfDate)) {
                        $pdfParams['as_of_date'] = $asOfDate;
                    } else {
                        if (!empty($fromDate)) {
                            $pdfParams['from_date'] = $fromDate;
                        }
                        if (!empty($toDate)) {
                            $pdfParams['to_date'] = $toDate;
                        }
                    }
                    $pdfHref = $pdfUrl . ($pdfParams ? '?' . http_build_query($pdfParams) : '');
                    ?>
                    <a href="<?= \DoubleE\Core\View::e($pdfHref) ?>"
                       class="btn btn-outline-dark btn-sm no-print"
                       style="border-radius: 0;">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                    </a>
                <?php endif; ?>
                <button type="button"
                        class="btn btn-outline-dark btn-sm no-print"
                        style="border-radius: 0;"
                        onclick="window.print();">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </form>
    </div>
</div>
