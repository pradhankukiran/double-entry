<?php
/**
 * Payment detail view.
 *
 * Variables: $payment (array with 'allocations'), $contact (array)
 */

$statusBadges = [
    'draft'  => 'secondary',
    'posted' => 'success',
    'voided' => 'danger',
];
$badge = $statusBadges[$payment['status']] ?? 'secondary';

$isReceived  = ($payment['type'] === 'received');
$allocations = $payment['allocations'] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        Payment
        <code class="text-dark ms-1"><?= \DoubleE\Core\View::e($payment['payment_number']) ?></code>
    </h4>
    <div class="d-flex gap-2">
        <a href="/payments" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Payment Details -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Payment Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Payment Number</div>
                    <div class="col-md-9 fw-medium">
                        <code><?= \DoubleE\Core\View::e($payment['payment_number']) ?></code>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Type</div>
                    <div class="col-md-9">
                        <span class="badge text-bg-<?= $isReceived ? 'success' : 'primary' ?>" style="border-radius: 0;">
                            <?= $isReceived ? 'Payment Received' : 'Payment Made' ?>
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status</div>
                    <div class="col-md-9">
                        <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;">
                            <?= ucfirst(\DoubleE\Core\View::e($payment['status'])) ?>
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted"><?= $isReceived ? 'Customer' : 'Vendor' ?></div>
                    <div class="col-md-9">
                        <a href="/contacts/<?= (int) $contact['id'] ?>" class="text-decoration-none">
                            <?= \DoubleE\Core\View::e($contact['display_name']) ?>
                        </a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Payment Date</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($payment['payment_date']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Amount</div>
                    <div class="col-md-9 font-monospace fw-medium"><?= number_format((float) $payment['amount'], 2) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Payment Method</div>
                    <div class="col-md-9"><?= ucfirst(str_replace('_', ' ', \DoubleE\Core\View::e($payment['payment_method']))) ?></div>
                </div>
                <?php if (!empty($payment['reference'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Reference</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($payment['reference']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($payment['notes'])): ?>
                <div class="row mb-0">
                    <div class="col-md-3 text-muted">Notes</div>
                    <div class="col-md-9"><?= nl2br(\DoubleE\Core\View::e($payment['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Allocation Details -->
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Applied to <?= $isReceived ? 'Invoices' : 'Bills' ?></h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">Document #</th>
                            <th style="width: 100px;">Type</th>
                            <th style="width: 130px;" class="text-end">Invoice Total</th>
                            <th style="width: 130px;" class="text-end">Balance Due</th>
                            <th style="width: 140px;" class="text-end">Amount Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allocations)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No invoice allocations for this payment.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                            $totalAllocated = 0;
                            foreach ($allocations as $alloc):
                                $totalAllocated += (float) $alloc['amount'];
                            ?>
                                <tr>
                                    <td>
                                        <a href="/invoices/<?= (int) $alloc['invoice_id'] ?>" class="text-decoration-none">
                                            <code class="text-dark"><?= \DoubleE\Core\View::e($alloc['document_number']) ?></code>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-<?= $alloc['document_type'] === 'invoice' ? 'primary' : 'warning' ?>" style="border-radius: 0;">
                                            <?= ucfirst(\DoubleE\Core\View::e($alloc['document_type'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end font-monospace"><?= number_format((float) $alloc['invoice_total'], 2) ?></td>
                                    <td class="text-end font-monospace"><?= number_format((float) $alloc['balance_due'], 2) ?></td>
                                    <td class="text-end font-monospace fw-medium"><?= number_format((float) $alloc['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($allocations)): ?>
                    <tfoot>
                        <tr class="table-light fw-semibold">
                            <td colspan="4" class="text-end">Total Allocated:</td>
                            <td class="text-end font-monospace"><?= number_format($totalAllocated, 2) ?></td>
                        </tr>
                        <?php
                        $unallocated = (float) $payment['amount'] - $totalAllocated;
                        if ($unallocated > 0.005):
                        ?>
                        <tr class="table-light">
                            <td colspan="4" class="text-end text-muted">Unallocated:</td>
                            <td class="text-end font-monospace text-warning"><?= number_format($unallocated, 2) ?></td>
                        </tr>
                        <?php endif; ?>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-body p-4 text-center">
                <div class="text-muted small mb-1">Payment Amount</div>
                <div class="fs-2 fw-bold text-dark">
                    <?= number_format((float) $payment['amount'], 2) ?>
                </div>
                <div class="mt-2">
                    <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;">
                        <?= ucfirst(\DoubleE\Core\View::e($payment['status'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($payment['status'] === 'voided'): ?>
        <div class="card border-0 shadow-sm border-danger" style="border-radius: 0;">
            <div class="card-body p-4">
                <h6 class="text-danger mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Voided</h6>
                <p class="text-muted small mb-0">
                    This payment has been voided. Any associated journal entries have been reversed
                    and invoice balances have been restored.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
