<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Users</h4>
    <a href="#" class="btn btn-dark" style="border-radius: 0;">
        <i class="bi bi-plus-lg me-1"></i> Add User
    </a>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <span class="fw-medium"><?= \DoubleE\Core\View::e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></span>
                            </td>
                            <td><?= \DoubleE\Core\View::e($user['email'] ?? '') ?></td>
                            <td><?= \DoubleE\Core\View::e($user['role_name'] ?? 'No Role') ?></td>
                            <td>
                                <?php if (!empty($user['is_active'])): ?>
                                    <span class="badge text-bg-success" style="border-radius: 0;">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary" style="border-radius: 0;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($user['last_login_at'])): ?>
                                    <span class="text-muted small"><?= \DoubleE\Core\View::e($user['last_login_at']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Never</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/users/<?= (int) $user['id'] ?>/edit" class="btn btn-sm btn-outline-dark" style="border-radius: 0;">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
