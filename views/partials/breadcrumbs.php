<?php if (!empty($breadcrumbs)): ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-3" style="font-size: 0.85rem;">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <?php if ($i === count($breadcrumbs) - 1): ?>
                <li class="breadcrumb-item active"><?= \DoubleE\Core\View::e($crumb['label']) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= \DoubleE\Core\View::e($crumb['url']) ?>" class="text-decoration-none"><?= \DoubleE\Core\View::e($crumb['label']) ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>
