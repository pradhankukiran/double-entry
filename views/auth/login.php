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
