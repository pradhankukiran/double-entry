<?php
/**
 * Contacts — list view with type filter tabs.
 *
 * Variables: $contacts (array), $typeFilter (string: '', 'customer', 'vendor')
 */

$typeBadges = [
    'customer' => 'primary',
    'vendor'   => 'warning',
    'both'     => 'info',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Contacts</h4>
    <div class="d-flex gap-2">
        <a href="/export/contacts" class="btn btn-sm btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
        <?php if ($canCreate ?? false): ?>
        <a href="/contacts/create" class="btn btn-dark" style="border-radius: 0;">
            <i class="bi bi-plus-lg me-1"></i> New Contact
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Type Filter Tabs -->
<ul class="nav nav-tabs mb-4" style="border-radius: 0;">
    <li class="nav-item">
        <a class="nav-link <?= $typeFilter === '' ? 'active' : '' ?>"
           href="/contacts"
           style="border-radius: 0;">All</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $typeFilter === 'customer' ? 'active' : '' ?>"
           href="/contacts?type=customer"
           style="border-radius: 0;">Customers</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $typeFilter === 'vendor' ? 'active' : '' ?>"
           href="/contacts?type=vendor"
           style="border-radius: 0;">Vendors</a>
    </li>
</ul>

<!-- Contacts Table -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Display Name</th>
                    <th style="width: 100px;">Type</th>
                    <th>Email</th>
                    <th style="width: 130px;">Phone</th>
                    <th style="width: 120px;">Payment Terms</th>
                    <th style="width: 140px;" class="text-end">Outstanding</th>
                    <th style="width: 90px;">Status</th>
                    <th style="width: 120px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No contacts found.
                            <a href="/contacts/create" class="text-decoration-none">Create your first contact</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <?php $badge = $typeBadges[$contact['type']] ?? 'secondary'; ?>
                        <tr>
                            <td>
                                <a href="/contacts/<?= (int) $contact['id'] ?>" class="text-decoration-none fw-medium">
                                    <?= \DoubleE\Core\View::e($contact['display_name']) ?>
                                </a>
                                <?php if (!empty($contact['company_name'])): ?>
                                    <br><small class="text-muted"><?= \DoubleE\Core\View::e($contact['company_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($contact['type'])) ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= \DoubleE\Core\View::e($contact['email'] ?? '-') ?></td>
                            <td class="text-muted"><?= \DoubleE\Core\View::e($contact['phone'] ?? '-') ?></td>
                            <td class="text-muted"><?= (int) $contact['payment_terms'] ?> days</td>
                            <td class="text-end font-monospace">
                                <?php $outstanding = (float) ($contact['outstanding'] ?? 0); ?>
                                <?php if ($outstanding > 0): ?>
                                    <span class="text-danger fw-medium"><?= number_format($outstanding, 2) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0.00</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($contact['is_active'])): ?>
                                    <span class="badge text-bg-success" style="border-radius: 0;">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary" style="border-radius: 0;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/contacts/<?= (int) $contact['id'] ?>" class="btn btn-sm btn-outline-dark me-1" style="border-radius: 0;" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/contacts/<?= (int) $contact['id'] ?>/edit" class="btn btn-sm btn-outline-dark" style="border-radius: 0;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
