<nav class="navbar navbar-expand-lg bg-white border-bottom px-4">
    <div class="container-fluid">
        <span class="navbar-text fw-semibold">
            <?= \DoubleE\Core\View::e($pageTitle ?? '') ?>
        </span>

        <div class="d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= \DoubleE\Core\View::e(\DoubleE\Core\Session::get('user_name', 'Guest')) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="/logout" class="d-inline">
                            <?= \DoubleE\Core\Csrf::field() ?>
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
