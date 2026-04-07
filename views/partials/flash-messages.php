<?php
$flash = \DoubleE\Core\Session::getAllFlash();
foreach ($flash as $type => $messages): ?>
    <?php foreach ($messages as $message): ?>
        <div class="alert alert-<?= match($type) {
            'success' => 'success',
            'error' => 'danger',
            'warning' => 'warning',
            default => 'info',
        } ?> alert-dismissible fade show" role="alert">
            <?= \DoubleE\Core\View::e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
