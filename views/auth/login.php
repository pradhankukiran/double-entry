<?php
$flash = \DoubleE\Core\Session::getAllFlash();
?>
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="card-body p-4">
        <h5 class="card-title text-center mb-4">Sign in to your account</h5>

        <?php foreach ($flash as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : \DoubleE\Core\View::e($type) ?> py-2" role="alert" style="border-radius: 0;">
                    <?= \DoubleE\Core\View::e($message) ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <form method="POST" action="/login" novalidate>
            <?= \DoubleE\Core\Csrf::field() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email"
                       class="form-control"
                       id="email"
                       name="email"
                       placeholder="you@company.com"
                       required
                       autofocus
                       style="border-radius: 0;">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password"
                       class="form-control"
                       id="password"
                       name="password"
                       placeholder="Enter your password"
                       required
                       style="border-radius: 0;">
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark" style="border-radius: 0;">Sign In</button>
            </div>

            <div class="text-center">
                <a href="#" class="text-muted small text-decoration-none">Forgot password?</a>
            </div>
        </form>
    </div>
</div>

<div class="text-center my-4">
    <span class="text-muted small text-uppercase" style="letter-spacing: 0.1em;">Demo Access</span>
    <hr class="mt-2 mb-0">
</div>

<div class="row g-3">
    <div class="col-4">
        <form method="POST" action="/login">
            <?= \DoubleE\Core\Csrf::field() ?>
            <input type="hidden" name="email" value="admin@double-e.com">
            <input type="hidden" name="password" value="admin123">
            <div class="card border h-100" style="border-radius: 0;">
                <div class="card-body p-3 text-center">
                    <div class="fw-bold mb-1">Admin</div>
                    <div class="text-muted small mb-3">Full access to all features</div>
                    <button type="submit" class="btn btn-outline-dark btn-sm" style="border-radius: 0;">Login as Admin</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-4">
        <form method="POST" action="/login">
            <?= \DoubleE\Core\Csrf::field() ?>
            <input type="hidden" name="email" value="sarah@apex-consulting.com">
            <input type="hidden" name="password" value="demo123">
            <div class="card border h-100" style="border-radius: 0;">
                <div class="card-body p-3 text-center">
                    <div class="fw-bold mb-1">Accountant</div>
                    <div class="text-muted small mb-3">Journal entries, reports, invoicing</div>
                    <button type="submit" class="btn btn-outline-dark btn-sm" style="border-radius: 0;">Login as Accountant</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-4">
        <form method="POST" action="/login">
            <?= \DoubleE\Core\Csrf::field() ?>
            <input type="hidden" name="email" value="mike@apex-consulting.com">
            <input type="hidden" name="password" value="demo123">
            <div class="card border h-100" style="border-radius: 0;">
                <div class="card-body p-3 text-center">
                    <div class="fw-bold mb-1">Viewer</div>
                    <div class="text-muted small mb-3">Read-only access to reports and ledger</div>
                    <button type="submit" class="btn btn-outline-dark btn-sm" style="border-radius: 0;">Login as Viewer</button>
                </div>
            </div>
        </form>
    </div>
</div>
