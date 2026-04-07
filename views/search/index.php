<?php
/**
 * Global Search — results page.
 *
 * Variables: $query (string), $results (array of grouped results)
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Search</h4>
</div>

<!-- Search Input -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body py-3">
        <form method="GET" action="/search">
            <div class="input-group">
                <input type="text"
                       name="q"
                       class="form-control form-control-lg"
                       placeholder="Search accounts, contacts, invoices, journal entries, payments..."
                       value="<?= \DoubleE\Core\View::e($query ?? '') ?>"
                       autofocus
                       style="border-radius: 0;">
                <button class="btn btn-dark" type="submit" style="border-radius: 0;">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($query !== ''): ?>
    <?php if (empty($results)): ?>
        <!-- No Results -->
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body text-center py-5">
                <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-3 mb-0">No results found for "<strong><?= \DoubleE\Core\View::e($query) ?></strong>"</p>
                <p class="text-muted small">Try different keywords or check the spelling.</p>
            </div>
        </div>
    <?php else: ?>
        <!-- Results grouped by type -->
        <?php foreach ($results as $group): ?>
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 0;">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><?= \DoubleE\Core\View::e($group['label']) ?></h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($group['items'] as $item): ?>
                        <a href="<?= \DoubleE\Core\View::e($item['link']) ?>"
                           class="list-group-item list-group-item-action d-flex align-items-center py-3"
                           style="border-radius: 0;">
                            <i class="<?= \DoubleE\Core\View::e($item['icon']) ?> me-3 text-muted" style="font-size: 1.2rem;"></i>
                            <div>
                                <div class="fw-medium"><?= \DoubleE\Core\View::e($item['title']) ?></div>
                                <small class="text-muted"><?= \DoubleE\Core\View::e($item['subtitle']) ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>
