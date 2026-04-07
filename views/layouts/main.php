<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \DoubleE\Core\View::e($pageTitle ?? 'Double-E Accounting') ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <link href="/assets/css/print.css" rel="stylesheet" media="print">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php $view = new \DoubleE\Core\View(); echo $view->partial('partials/sidebar', $__sidebarData ?? []); ?>

        <!-- Page content -->
        <div id="page-content-wrapper" class="flex-grow-1">
            <!-- Top navbar -->
            <?php echo $view->partial('partials/header', ['pageTitle' => $pageTitle ?? '']); ?>

            <!-- Flash messages -->
            <div class="container-fluid px-4 mt-3">
                <?php echo $view->partial('partials/flash-messages'); ?>
            </div>

            <!-- Main content -->
            <main class="container-fluid px-4 py-3">
                <?= $content ?? '' ?>
            </main>

            <!-- Footer -->
            <?php echo $view->partial('partials/footer'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
