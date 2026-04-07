<?php
/**
 * Audit Trail — filterable log of all system activity.
 *
 * Variables: $entries, $users, $filters, $page, $totalPages, $totalRows
 */

$e = fn(mixed $v): string => \DoubleE\Core\View::e((string) ($v ?? ''));

$actionBadges = [
    'login'  => 'info',
    'logout' => 'secondary',
    'create' => 'success',
    'update' => 'primary',
    'delete' => 'danger',
    'post'   => 'success',
    'void'   => 'danger',
];

$entityTypes = [
    'user', 'account', 'journal_entry', 'invoice', 'bill',
    'payment', 'contact', 'bank_account', 'fiscal_year',
    'tax_rate', 'tax_group', 'recurring_transaction', 'setting',
];

$actions = ['login', 'logout', 'create', 'update', 'delete', 'post', 'void'];

/**
 * Map entity types to URL prefixes for linking.
 */
$entityLinks = [
    'account'       => '/accounts/',
    'journal_entry' => '/journal-entries/',
    'invoice'       => '/invoices/',
    'bill'          => '/invoices/',
    'payment'       => '/payments/',
    'contact'       => '/contacts/',
    'bank_account'  => '/bank-accounts/',
    'fiscal_year'   => '/fiscal-years/',
    'user'          => '/users/',
];

/**
 * Build the current query string with an overridden page parameter.
 */
$pageUrl = function (int $p) use ($filters): string {
    $params = array_filter($filters, fn($v) => $v !== '');
    $params['page'] = $p;
    return '/audit?' . http_build_query($params);
};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Audit Trail</h4>
    <span class="text-muted small"><?= number_format($totalRows) ?> entries</span>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-body p-3">
        <form method="GET" action="/audit" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label for="filter_user" class="form-label small mb-1">User</label>
                <select id="filter_user" name="user_id" class="form-select form-select-sm" style="border-radius: 0;">
                    <option value="">All Users</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $e($u['id']) ?>"<?= (string)($filters['user_id'] ?? '') === (string)$u['id'] ? ' selected' : '' ?>>
                        <?= $e($u['first_name'] . ' ' . $u['last_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_action" class="form-label small mb-1">Action</label>
                <select id="filter_action" name="action" class="form-select form-select-sm" style="border-radius: 0;">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $a): ?>
                    <option value="<?= $e($a) ?>"<?= ($filters['action'] ?? '') === $a ? ' selected' : '' ?>>
                        <?= $e(ucfirst($a)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_entity" class="form-label small mb-1">Entity Type</label>
                <select id="filter_entity" name="entity_type" class="form-select form-select-sm" style="border-radius: 0;">
                    <option value="">All Types</option>
                    <?php foreach ($entityTypes as $et): ?>
                    <option value="<?= $e($et) ?>"<?= ($filters['entity_type'] ?? '') === $et ? ' selected' : '' ?>>
                        <?= $e(ucwords(str_replace('_', ' ', $et))) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_date_from" class="form-label small mb-1">Date From</label>
                <input type="date"
                       id="filter_date_from"
                       name="date_from"
                       class="form-control form-control-sm"
                       style="border-radius: 0;"
                       value="<?= $e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="filter_date_to" class="form-label small mb-1">Date To</label>
                <input type="date"
                       id="filter_date_to"
                       name="date_to"
                       class="form-control form-control-sm"
                       style="border-radius: 0;"
                       value="<?= $e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-dark" style="border-radius: 0;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="/audit" class="btn btn-sm btn-outline-secondary" style="border-radius: 0;">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <?php if (empty($entries)): ?>
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-clock-history d-block mb-2" style="font-size: 2rem;"></i>
        No audit log entries found.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 ps-3" style="width: 160px;">Date / Time</th>
                    <th class="border-0">User</th>
                    <th class="border-0">Action</th>
                    <th class="border-0">Entity Type</th>
                    <th class="border-0">Entity ID</th>
                    <th class="border-0">IP Address</th>
                    <th class="border-0 text-end pe-3" style="width: 80px;">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $i => $entry): ?>
                <?php
                    $badge = $actionBadges[$entry['action']] ?? 'dark';
                    $hasDetails = !empty($entry['old_values']) || !empty($entry['new_values']);
                    $collapseId = 'details_' . $entry['id'];
                    $linkBase = $entityLinks[$entry['entity_type']] ?? null;
                ?>
                <tr>
                    <td class="ps-3 text-nowrap small">
                        <?= $e(date('Y-m-d H:i:s', strtotime($entry['created_at']))) ?>
                    </td>
                    <td>
                        <?php if ($entry['user_name']): ?>
                            <span class="fw-medium"><?= $e(trim($entry['user_name'])) ?></span>
                            <br><small class="text-muted"><?= $e($entry['email']) ?></small>
                        <?php else: ?>
                            <span class="text-muted">System</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $badge ?>" style="border-radius: 0;">
                            <?= $e(ucfirst($entry['action'])) ?>
                        </span>
                    </td>
                    <td class="small"><?= $e(ucwords(str_replace('_', ' ', $entry['entity_type'] ?? ''))) ?></td>
                    <td>
                        <?php if ($entry['entity_id'] !== null): ?>
                            <?php if ($linkBase): ?>
                                <a href="<?= $e($linkBase . $entry['entity_id']) ?>" class="text-decoration-none">
                                    #<?= $e($entry['entity_id']) ?>
                                </a>
                            <?php else: ?>
                                #<?= $e($entry['entity_id']) ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= $e($entry['ip_address']) ?></td>
                    <td class="text-end pe-3">
                        <?php if ($hasDetails): ?>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                                style="border-radius: 0; font-size: 0.75rem;"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= $collapseId ?>"
                                aria-expanded="false">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <?php else: ?>
                        <span class="text-muted">&mdash;</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($hasDetails): ?>
                <tr class="collapse" id="<?= $collapseId ?>">
                    <td colspan="7" class="bg-light px-4 py-3">
                        <div class="row">
                            <?php
                                $oldVals = $entry['old_values'] ? json_decode($entry['old_values'], true) : null;
                                $newVals = $entry['new_values'] ? json_decode($entry['new_values'], true) : null;
                            ?>
                            <?php if ($oldVals && $newVals): ?>
                            <!-- Diff view: show changed fields side by side -->
                            <div class="col-12">
                                <h6 class="small fw-semibold mb-2">Changes</h6>
                                <table class="table table-sm table-bordered mb-0 small" style="border-radius: 0;">
                                    <thead class="bg-white">
                                        <tr>
                                            <th style="width: 25%;">Field</th>
                                            <th style="width: 37.5%;">Old Value</th>
                                            <th style="width: 37.5%;">New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $allKeys = array_unique(array_merge(array_keys($oldVals), array_keys($newVals)));
                                        sort($allKeys);
                                        foreach ($allKeys as $key):
                                            $old = $oldVals[$key] ?? null;
                                            $new = $newVals[$key] ?? null;
                                            $changed = $old !== $new;
                                        ?>
                                        <tr<?= $changed ? ' class="table-warning"' : '' ?>>
                                            <td class="fw-medium"><?= $e($key) ?></td>
                                            <td><?= $old !== null ? $e(is_array($old) ? json_encode($old) : (string) $old) : '<span class="text-muted">&mdash;</span>' ?></td>
                                            <td><?= $new !== null ? $e(is_array($new) ? json_encode($new) : (string) $new) : '<span class="text-muted">&mdash;</span>' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php elseif ($newVals): ?>
                            <div class="col-12">
                                <h6 class="small fw-semibold mb-2">New Values</h6>
                                <pre class="bg-white border p-3 mb-0 small" style="border-radius: 0; max-height: 300px; overflow: auto;"><?= $e(json_encode($newVals, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                            </div>
                            <?php elseif ($oldVals): ?>
                            <div class="col-12">
                                <h6 class="small fw-semibold mb-2">Previous Values</h6>
                                <pre class="bg-white border p-3 mb-0 small" style="border-radius: 0; max-height: 300px; overflow: auto;"><?= $e(json_encode($oldVals, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3">
        <small class="text-muted">
            Page <?= $page ?> of <?= $totalPages ?>
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $e($pageUrl($page - 1)) ?>" style="border-radius: 0;">Previous</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link" style="border-radius: 0;">Previous</span>
                </li>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $e($pageUrl($page + 1)) ?>" style="border-radius: 0;">Next</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link" style="border-radius: 0;">Next</span>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
