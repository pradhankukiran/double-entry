<?php
/**
 * Pagination partial.
 *
 * Variables: $pagination (array from Pagination::paginate())
 */
if (empty($pagination) || $pagination['total_items'] === 0) {
    return;
}

$totalPages  = $pagination['total_pages'];
$currentPage = $pagination['current_page'];
$perPage     = $pagination['per_page'];
$totalItems  = $pagination['total_items'];
$baseUrl     = $pagination['base_url'];

// Calculate display range
$from = ($currentPage - 1) * $perPage + 1;
$to   = min($currentPage * $perPage, $totalItems);

// Build URL helper — preserves existing query params, sets page
$buildUrl = function (int $page) use ($baseUrl): string {
    $params = $_GET;
    $params['page'] = $page;
    $query = http_build_query($params);
    return $baseUrl . '?' . $query;
};

// Determine which page numbers to show: first, last, and 2 around current
$pages = [];
for ($p = 1; $p <= $totalPages; $p++) {
    if ($p === 1 || $p === $totalPages || abs($p - $currentPage) <= 2) {
        $pages[] = $p;
    }
}
$pages = array_unique($pages);
sort($pages);
?>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted" style="font-size: 0.85rem;">
        Showing <?= $from ?>-<?= $to ?> of <?= number_format($totalItems) ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0">
            <!-- Previous -->
            <li class="page-item <?= !$pagination['has_previous'] ? 'disabled' : '' ?>">
                <a class="page-link btn-outline-secondary" href="<?= $pagination['has_previous'] ? \DoubleE\Core\View::e($buildUrl($pagination['previous_page'])) : '#' ?>" style="border-radius: 0;">Previous</a>
            </li>

            <?php
            $prevPage = null;
            foreach ($pages as $p):
                // Insert ellipsis if gap
                if ($prevPage !== null && $p - $prevPage > 1):
            ?>
                <li class="page-item disabled"><span class="page-link" style="border-radius: 0;">...</span></li>
            <?php endif; ?>

            <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                <a class="page-link btn-outline-secondary" href="<?= \DoubleE\Core\View::e($buildUrl($p)) ?>" style="border-radius: 0;"><?= $p ?></a>
            </li>
            <?php
                $prevPage = $p;
            endforeach;
            ?>

            <!-- Next -->
            <li class="page-item <?= !$pagination['has_next'] ? 'disabled' : '' ?>">
                <a class="page-link btn-outline-secondary" href="<?= $pagination['has_next'] ? \DoubleE\Core\View::e($buildUrl($pagination['next_page'])) : '#' ?>" style="border-radius: 0;">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>
