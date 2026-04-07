<?php
/**
 * Invoice / Bill detail view.
 *
 * Variables: $invoice (array with 'lines'), $contact (array), $allocations (array of payment allocations)
 */

$statusBadges = [
    'draft'   => 'secondary',
    'sent'    => 'primary',
    'partial' => 'warning',
    'paid'    => 'success',
    'overdue' => 'danger',
    'voided'  => 'danger',
];
$badge = $statusBadges[$invoice['status']] ?? 'secondary';

$isDraft  = ($invoice['status'] === 'draft');
$isVoided = ($invoice['status'] === 'voided');
$isPaid   = ($invoice['status'] === 'paid');
$isInvoice = ($invoice['document_type'] === 'invoice');

$lines    = $invoice['lines'] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <?= $isInvoice ? 'Invoice' : 'Bill' ?>
        <code class="text-dark ms-1"><?= \DoubleE\Core\View::e($invoice['document_number']) ?></code>
    </h4>
    <div class="d-flex gap-2">
        <a href="/invoices" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <a href="/invoices/<?= (int) $invoice['id'] ?>/pdf" class="btn btn-sm btn-outline-secondary" target="_blank" style="border-radius: 0;">
            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
        </a>
        <?php if ($isDraft): ?>
            <?php if ($canPost ?? false): ?>
            <form method="POST" action="/invoices/<?= (int) $invoice['id'] ?>/post" class="d-inline">
                <?= \DoubleE\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-dark" style="border-radius: 0;" onclick="return confirm('Post this <?= $isInvoice ? 'invoice' : 'bill' ?>? This will create journal entries.');">
                    <i class="bi bi-check-lg me-1"></i> Post
                </button>
            </form>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!$isDraft && !$isVoided && !$isPaid): ?>
            <?php if ($canCreate ?? false): ?>
            <a href="/payments/create?type=<?= $isInvoice ? 'received' : 'made' ?>&contact_id=<?= (int) $invoice['contact_id'] ?>" class="btn btn-outline-dark" style="border-radius: 0;">
                <i class="bi bi-cash me-1"></i> Record Payment
            </a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!$isDraft && !$isVoided): ?>
            <?php if ($canVoid ?? false): ?>
            <form method="POST" action="/invoices/<?= (int) $invoice['id'] ?>/void" class="d-inline">
                <?= \DoubleE\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-outline-danger" style="border-radius: 0;" onclick="return confirm('Void this <?= $isInvoice ? 'invoice' : 'bill' ?>? This action cannot be undone.');">
                    <i class="bi bi-x-circle me-1"></i> Void
                </button>
            </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Invoice Details -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0"><?= $isInvoice ? 'Invoice' : 'Bill' ?> Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Document Number</div>
                    <div class="col-md-9 fw-medium">
                        <code><?= \DoubleE\Core\View::e($invoice['document_number']) ?></code>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status</div>
                    <div class="col-md-9">
                        <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;">
                            <?= ucfirst(\DoubleE\Core\View::e($invoice['status'])) ?>
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted"><?= $isInvoice ? 'Customer' : 'Vendor' ?></div>
                    <div class="col-md-9">
                        <a href="/contacts/<?= (int) $contact['id'] ?>" class="text-decoration-none">
                            <?= \DoubleE\Core\View::e($contact['display_name']) ?>
                        </a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Issue Date</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($invoice['issue_date']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Due Date</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($invoice['due_date']) ?></div>
                </div>
                <?php if (!empty($invoice['reference'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Reference</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($invoice['reference']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($invoice['notes'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Notes</div>
                    <div class="col-md-9"><?= nl2br(\DoubleE\Core\View::e($invoice['notes'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($invoice['terms'])): ?>
                <div class="row mb-0">
                    <div class="col-md-3 text-muted">Terms</div>
                    <div class="col-md-9"><?= nl2br(\DoubleE\Core\View::e($invoice['terms'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Line Items -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Line Items</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th style="width: 180px;">Account</th>
                            <th style="width: 80px;" class="text-end">Qty</th>
                            <th style="width: 120px;" class="text-end">Unit Price</th>
                            <th style="width: 100px;" class="text-end">Tax</th>
                            <th style="width: 120px;" class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lines)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No line items.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lines as $line): ?>
                                <tr>
                                    <td><?= \DoubleE\Core\View::e($line['description']) ?></td>
                                    <td class="text-muted small">
                                        <code class="text-dark"><?= \DoubleE\Core\View::e($line['account_number'] ?? '') ?></code>
                                        <?= \DoubleE\Core\View::e($line['account_name'] ?? '') ?>
                                    </td>
                                    <td class="text-end font-monospace"><?= number_format((float) $line['quantity'], 2) ?></td>
                                    <td class="text-end font-monospace"><?= number_format((float) $line['unit_price'], 2) ?></td>
                                    <td class="text-end font-monospace"><?= number_format((float) $line['tax_amount'], 2) ?></td>
                                    <td class="text-end font-monospace"><?= number_format((float) $line['line_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-semibold">Subtotal:</td>
                            <td class="text-end font-monospace fw-semibold"><?= number_format((float) $invoice['subtotal'], 2) ?></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-semibold">Tax:</td>
                            <td class="text-end font-monospace fw-semibold"><?= number_format((float) $invoice['tax_amount'], 2) ?></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Total:</td>
                            <td class="text-end font-monospace fw-bold"><?= number_format((float) $invoice['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Payment History -->
        <?php if (!empty($allocations)): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Payment History</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">Payment #</th>
                            <th style="width: 110px;">Date</th>
                            <th>Method</th>
                            <th style="width: 140px;" class="text-end">Amount Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allocations as $alloc): ?>
                            <tr>
                                <td>
                                    <a href="/payments/<?= (int) $alloc['payment_id'] ?>" class="text-decoration-none">
                                        <code class="text-dark"><?= \DoubleE\Core\View::e($alloc['payment_number']) ?></code>
                                    </a>
                                </td>
                                <td><?= \DoubleE\Core\View::e($alloc['payment_date']) ?></td>
                                <td class="text-muted"><?= ucfirst(str_replace('_', ' ', \DoubleE\Core\View::e($alloc['payment_method'] ?? ''))) ?></td>
                                <td class="text-end font-monospace"><?= number_format((float) $alloc['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-semibold">
                            <td colspan="3" class="text-end">Total Paid:</td>
                            <td class="text-end font-monospace"><?= number_format((float) $invoice['amount_paid'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Summary Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-body p-4">
                <div class="text-muted small mb-1">Balance Due</div>
                <?php $balanceDue = (float) $invoice['balance_due']; ?>
                <div class="fs-2 fw-bold <?= $balanceDue > 0 ? 'text-danger' : 'text-success' ?> mb-3">
                    <?= number_format($balanceDue, 2) ?>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-muted small">Total</div>
                        <div class="font-monospace fw-medium"><?= number_format((float) $invoice['total'], 2) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Amount Paid</div>
                        <div class="font-monospace fw-medium"><?= number_format((float) $invoice['amount_paid'], 2) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Issue Date</div>
                        <div><?= \DoubleE\Core\View::e($invoice['issue_date']) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Due Date</div>
                        <div><?= \DoubleE\Core\View::e($invoice['due_date']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isVoided): ?>
        <div class="card border-0 shadow-sm border-danger mb-4" style="border-radius: 0;">
            <div class="card-body p-4">
                <h6 class="text-danger mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Voided</h6>
                <p class="text-muted small mb-0">
                    This <?= $isInvoice ? 'invoice' : 'bill' ?> has been voided and is no longer active.
                    Any associated journal entries have been reversed.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
