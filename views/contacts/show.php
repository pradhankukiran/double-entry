<?php
/**
 * Contact detail view.
 *
 * Variables: $contact (array), $addresses (array), $invoices (array), $payments (array), $outstanding (string)
 */

$typeBadges = [
    'customer' => 'primary',
    'vendor'   => 'warning',
    'both'     => 'info',
];
$badge = $typeBadges[$contact['type']] ?? 'secondary';

$statusBadges = [
    'draft'   => 'secondary',
    'sent'    => 'primary',
    'partial' => 'warning',
    'paid'    => 'success',
    'overdue' => 'danger',
    'voided'  => 'danger',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><?= \DoubleE\Core\View::e($contact['display_name']) ?></h4>
    <div class="d-flex gap-2">
        <a href="/contacts" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <a href="/contacts/<?= (int) $contact['id'] ?>/edit" class="btn btn-outline-dark" style="border-radius: 0;">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <!-- Contact Details -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Contact Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Type</div>
                    <div class="col-md-9">
                        <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;">
                            <?= ucfirst(\DoubleE\Core\View::e($contact['type'])) ?>
                        </span>
                    </div>
                </div>
                <?php if (!empty($contact['company_name'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Company</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($contact['company_name']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($contact['first_name']) || !empty($contact['last_name'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Contact Person</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e(trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? ''))) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($contact['email'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Email</div>
                    <div class="col-md-9"><a href="mailto:<?= \DoubleE\Core\View::e($contact['email']) ?>" class="text-decoration-none"><?= \DoubleE\Core\View::e($contact['email']) ?></a></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($contact['phone'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Phone</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($contact['phone']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($contact['tax_id'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Tax ID</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($contact['tax_id']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($contact['website'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Website</div>
                    <div class="col-md-9"><a href="<?= \DoubleE\Core\View::e($contact['website']) ?>" target="_blank" class="text-decoration-none"><?= \DoubleE\Core\View::e($contact['website']) ?></a></div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Payment Terms</div>
                    <div class="col-md-9"><?= (int) $contact['payment_terms'] ?> days</div>
                </div>
                <?php if (!empty($contact['credit_limit'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Credit Limit</div>
                    <div class="col-md-9 font-monospace"><?= number_format((float) $contact['credit_limit'], 2) ?></div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status</div>
                    <div class="col-md-9">
                        <?php if (!empty($contact['is_active'])): ?>
                            <span class="badge text-bg-success" style="border-radius: 0;">Active</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary" style="border-radius: 0;">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($contact['notes'])): ?>
                <div class="row mb-0">
                    <div class="col-md-3 text-muted">Notes</div>
                    <div class="col-md-9"><?= nl2br(\DoubleE\Core\View::e($contact['notes'])) ?></div>
                </div>
                <?php endif; ?>

                <!-- Addresses -->
                <?php if (!empty($addresses)): ?>
                <hr>
                <?php foreach ($addresses as $addr): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted"><?= ucfirst(\DoubleE\Core\View::e($addr['type'])) ?> Address</div>
                    <div class="col-md-9">
                        <?= \DoubleE\Core\View::e($addr['line1']) ?><br>
                        <?php if (!empty($addr['line2'])): ?><?= \DoubleE\Core\View::e($addr['line2']) ?><br><?php endif; ?>
                        <?= \DoubleE\Core\View::e($addr['city']) ?><?php if (!empty($addr['state'])): ?>, <?= \DoubleE\Core\View::e($addr['state']) ?><?php endif; ?>
                        <?php if (!empty($addr['postal_code'])): ?> <?= \DoubleE\Core\View::e($addr['postal_code']) ?><?php endif; ?><br>
                        <?= \DoubleE\Core\View::e($addr['country']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Outstanding Balance -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-body p-4 text-center">
                <div class="text-muted small mb-1">Outstanding Balance</div>
                <?php $outstandingVal = (float) $outstanding; ?>
                <div class="fs-2 fw-bold <?= $outstandingVal > 0 ? 'text-danger' : 'text-success' ?>">
                    <?= number_format($outstandingVal, 2) ?>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 mb-4">
            <?php if (in_array($contact['type'], ['customer', 'both'], true)): ?>
                <a href="/invoices/create?type=invoice&contact_id=<?= (int) $contact['id'] ?>" class="btn btn-outline-dark" style="border-radius: 0;">
                    <i class="bi bi-receipt me-1"></i> New Invoice
                </a>
                <a href="/payments/create?type=received&contact_id=<?= (int) $contact['id'] ?>" class="btn btn-outline-dark" style="border-radius: 0;">
                    <i class="bi bi-cash me-1"></i> Receive Payment
                </a>
            <?php endif; ?>
            <?php if (in_array($contact['type'], ['vendor', 'both'], true)): ?>
                <a href="/invoices/create?type=bill&contact_id=<?= (int) $contact['id'] ?>" class="btn btn-outline-dark" style="border-radius: 0;">
                    <i class="bi bi-file-text me-1"></i> New Bill
                </a>
                <a href="/payments/create?type=made&contact_id=<?= (int) $contact['id'] ?>" class="btn btn-outline-dark" style="border-radius: 0;">
                    <i class="bi bi-wallet2 me-1"></i> Make Payment
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Invoices & Bills -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
    <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
        <h6 class="mb-0">Invoices &amp; Bills</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 130px;">Document #</th>
                    <th style="width: 90px;">Type</th>
                    <th style="width: 110px;">Issue Date</th>
                    <th style="width: 110px;">Due Date</th>
                    <th style="width: 130px;" class="text-end">Total</th>
                    <th style="width: 130px;" class="text-end">Balance Due</th>
                    <th style="width: 100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No invoices or bills found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <?php $invBadge = $statusBadges[$inv['status']] ?? 'secondary'; ?>
                        <tr>
                            <td>
                                <a href="/invoices/<?= (int) $inv['id'] ?>" class="text-decoration-none">
                                    <code class="text-dark"><?= \DoubleE\Core\View::e($inv['document_number']) ?></code>
                                </a>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $inv['document_type'] === 'invoice' ? 'primary' : 'warning' ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($inv['document_type'])) ?>
                                </span>
                            </td>
                            <td><?= \DoubleE\Core\View::e($inv['issue_date']) ?></td>
                            <td><?= \DoubleE\Core\View::e($inv['due_date']) ?></td>
                            <td class="text-end font-monospace"><?= number_format((float) $inv['total'], 2) ?></td>
                            <td class="text-end font-monospace">
                                <?php if ((float) $inv['balance_due'] > 0): ?>
                                    <span class="text-danger"><?= number_format((float) $inv['balance_due'], 2) ?></span>
                                <?php else: ?>
                                    <?= number_format((float) $inv['balance_due'], 2) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $invBadge ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($inv['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Payments -->
<div class="card border-0 shadow-sm" style="border-radius: 0;">
    <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
        <h6 class="mb-0">Payments</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px;">Payment #</th>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 100px;">Type</th>
                    <th style="width: 130px;" class="text-end">Amount</th>
                    <th>Method</th>
                    <th style="width: 100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No payments found.</td>
                    </tr>
                <?php else: ?>
                    <?php
                    $pmtStatusBadges = [
                        'draft'  => 'secondary',
                        'posted' => 'success',
                        'voided' => 'danger',
                    ];
                    ?>
                    <?php foreach ($payments as $pmt): ?>
                        <?php $pmtBadge = $pmtStatusBadges[$pmt['status']] ?? 'secondary'; ?>
                        <tr>
                            <td>
                                <a href="/payments/<?= (int) $pmt['id'] ?>" class="text-decoration-none">
                                    <code class="text-dark"><?= \DoubleE\Core\View::e($pmt['payment_number']) ?></code>
                                </a>
                            </td>
                            <td><?= \DoubleE\Core\View::e($pmt['payment_date']) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $pmt['type'] === 'received' ? 'success' : 'primary' ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($pmt['type'])) ?>
                                </span>
                            </td>
                            <td class="text-end font-monospace"><?= number_format((float) $pmt['amount'], 2) ?></td>
                            <td class="text-muted"><?= ucfirst(str_replace('_', ' ', \DoubleE\Core\View::e($pmt['payment_method']))) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $pmtBadge ?>" style="border-radius: 0;">
                                    <?= ucfirst(\DoubleE\Core\View::e($pmt['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
