<?php
/**
 * Invoice / Bill PDF template.
 *
 * Rendered within layouts/pdf.php via PdfService::renderReportPdf().
 *
 * Variables: $invoice (array with 'lines'), $contact (array), $address (array|null), $company (array)
 */

$isInvoice = ($invoice['document_type'] === 'invoice');
$docLabel  = $isInvoice ? 'INVOICE' : 'BILL';
$lines     = $invoice['lines'] ?? [];

$statusLabels = [
    'draft'   => 'DRAFT',
    'sent'    => 'SENT',
    'posted'  => 'POSTED',
    'partial' => 'PARTIAL',
    'paid'    => 'PAID',
    'overdue' => 'OVERDUE',
    'voided'  => 'VOIDED',
];
$statusColors = [
    'draft'   => '#6c757d',
    'sent'    => '#0d6efd',
    'posted'  => '#0d6efd',
    'partial' => '#0dcaf0',
    'paid'    => '#198754',
    'overdue' => '#dc3545',
    'voided'  => '#dc3545',
];
$statusLabel = $statusLabels[$invoice['status']] ?? strtoupper($invoice['status']);
$statusColor = $statusColors[$invoice['status']] ?? '#6c757d';

/**
 * Format a monetary amount: negative values in parentheses.
 */
function formatAmount(float $amount): string {
    if ($amount < 0) {
        return '(' . number_format(abs($amount), 2) . ')';
    }
    return number_format($amount, 2);
}
?>

<style>
    .invoice-header {
        display: table;
        width: 100%;
        margin-bottom: 24px;
    }
    .invoice-header .left,
    .invoice-header .right {
        display: table-cell;
        vertical-align: top;
        width: 50%;
    }
    .invoice-header .right {
        text-align: right;
    }
    .company-info {
        font-size: 11px;
        color: #555;
        line-height: 1.6;
    }
    .doc-title {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: 2px;
        color: #1a1a1a;
        margin-bottom: 8px;
    }
    .status-badge {
        display: inline-block;
        padding: 3px 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        color: #fff;
        margin-top: 4px;
    }
    .invoice-meta {
        display: table;
        width: 100%;
        margin-bottom: 24px;
    }
    .invoice-meta .bill-to,
    .invoice-meta .details {
        display: table-cell;
        vertical-align: top;
        width: 50%;
    }
    .invoice-meta .details {
        text-align: right;
    }
    .meta-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888;
        margin-bottom: 4px;
    }
    .meta-value {
        font-size: 12px;
        color: #1a1a1a;
        line-height: 1.6;
    }
    .detail-row {
        margin-bottom: 6px;
    }
    .detail-row .label {
        font-size: 10px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .detail-row .value {
        font-size: 12px;
        font-weight: 500;
    }
    .line-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .line-items-table thead th {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 8px;
        border-bottom: 2px solid #1a1a1a;
        background: #f5f5f5;
    }
    .line-items-table tbody td {
        padding: 8px;
        font-size: 11px;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: top;
    }
    .line-items-table .text-right {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }
    .totals-section {
        width: 280px;
        margin-left: auto;
        margin-bottom: 24px;
    }
    .totals-row {
        display: table;
        width: 100%;
        padding: 4px 0;
    }
    .totals-row .totals-label,
    .totals-row .totals-value {
        display: table-cell;
        font-size: 12px;
    }
    .totals-row .totals-label {
        text-align: left;
        color: #555;
    }
    .totals-row .totals-value {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }
    .totals-row.grand-total {
        border-top: 2px solid #1a1a1a;
        border-bottom: 3px double #1a1a1a;
        padding: 6px 0;
        margin-top: 4px;
    }
    .totals-row.grand-total .totals-label,
    .totals-row.grand-total .totals-value {
        font-weight: 700;
        font-size: 14px;
    }
    .totals-row.balance-due {
        margin-top: 8px;
    }
    .totals-row.balance-due .totals-label,
    .totals-row.balance-due .totals-value {
        font-weight: 700;
        font-size: 13px;
        color: #1a1a1a;
    }
    .notes-section {
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #ddd;
    }
    .notes-section .section-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888;
        margin-bottom: 4px;
    }
    .notes-section .section-content {
        font-size: 11px;
        color: #444;
        line-height: 1.6;
    }
</style>

<!-- Company Header + Document Title -->
<div class="invoice-header">
    <div class="left">
        <div style="font-size: 16px; font-weight: 700; margin-bottom: 4px;">
            <?= \DoubleE\Core\View::e($company['name']) ?>
        </div>
        <div class="company-info">
            <?php if (!empty($company['address'])): ?>
                <?= \DoubleE\Core\View::e($company['address']) ?><br>
            <?php endif; ?>
            <?php
                $cityLine = array_filter([
                    $company['city'] ?? '',
                    $company['state'] ?? '',
                    $company['postal_code'] ?? '',
                ]);
                if (!empty($cityLine)):
            ?>
                <?= \DoubleE\Core\View::e(implode(', ', array_slice($cityLine, 0, 2)) . (isset($cityLine[2]) ? ' ' . $cityLine[2] : '')) ?><br>
            <?php endif; ?>
            <?php if (!empty($company['phone'])): ?>
                <?= \DoubleE\Core\View::e($company['phone']) ?><br>
            <?php endif; ?>
            <?php if (!empty($company['email'])): ?>
                <?= \DoubleE\Core\View::e($company['email']) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="right">
        <div class="doc-title"><?= $docLabel ?></div>
        <span class="status-badge" style="background-color: <?= $statusColor ?>;">
            <?= $statusLabel ?>
        </span>
    </div>
</div>

<!-- Bill To + Invoice Details -->
<div class="invoice-meta">
    <div class="bill-to">
        <div class="meta-label"><?= $isInvoice ? 'Bill To' : 'From' ?></div>
        <div class="meta-value">
            <?php if (!empty($contact['display_name'])): ?>
                <strong><?= \DoubleE\Core\View::e($contact['display_name']) ?></strong><br>
            <?php endif; ?>
            <?php if (!empty($contact['company_name']) && ($contact['company_name'] !== ($contact['display_name'] ?? ''))): ?>
                <?= \DoubleE\Core\View::e($contact['company_name']) ?><br>
            <?php endif; ?>
            <?php if ($address): ?>
                <?php if (!empty($address['address_line_1'])): ?>
                    <?= \DoubleE\Core\View::e($address['address_line_1']) ?><br>
                <?php endif; ?>
                <?php if (!empty($address['address_line_2'])): ?>
                    <?= \DoubleE\Core\View::e($address['address_line_2']) ?><br>
                <?php endif; ?>
                <?php
                    $addrParts = array_filter([
                        $address['city'] ?? '',
                        $address['state'] ?? '',
                        $address['postal_code'] ?? '',
                    ]);
                    if (!empty($addrParts)):
                ?>
                    <?= \DoubleE\Core\View::e(implode(', ', $addrParts)) ?><br>
                <?php endif; ?>
                <?php if (!empty($address['country'])): ?>
                    <?= \DoubleE\Core\View::e($address['country']) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="details">
        <div class="detail-row">
            <div class="label">Document Number</div>
            <div class="value"><?= \DoubleE\Core\View::e($invoice['document_number']) ?></div>
        </div>
        <div class="detail-row">
            <div class="label">Issue Date</div>
            <div class="value"><?= \DoubleE\Core\View::e($invoice['issue_date']) ?></div>
        </div>
        <div class="detail-row">
            <div class="label">Due Date</div>
            <div class="value"><?= \DoubleE\Core\View::e($invoice['due_date']) ?></div>
        </div>
        <?php if (!empty($invoice['reference'])): ?>
        <div class="detail-row">
            <div class="label">Reference</div>
            <div class="value"><?= \DoubleE\Core\View::e($invoice['reference']) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Line Items -->
<table class="line-items-table">
    <thead>
        <tr>
            <th style="width: 50%;">Description</th>
            <th class="text-right" style="width: 12%;">Quantity</th>
            <th class="text-right" style="width: 18%;">Unit Price</th>
            <th class="text-right" style="width: 20%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lines as $line): ?>
        <tr>
            <td><?= \DoubleE\Core\View::e($line['description']) ?></td>
            <td class="text-right"><?= number_format((float) $line['quantity'], 2) ?></td>
            <td class="text-right"><?= number_format((float) $line['unit_price'], 2) ?></td>
            <td class="text-right"><?= number_format((float) $line['line_total'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Totals -->
<div class="totals-section">
    <div class="totals-row">
        <span class="totals-label">Subtotal</span>
        <span class="totals-value"><?= number_format((float) $invoice['subtotal'], 2) ?></span>
    </div>
    <div class="totals-row">
        <span class="totals-label">Tax</span>
        <span class="totals-value"><?= number_format((float) $invoice['tax_amount'], 2) ?></span>
    </div>
    <div class="totals-row grand-total">
        <span class="totals-label">Total</span>
        <span class="totals-value"><?= number_format((float) $invoice['total'], 2) ?></span>
    </div>
    <div class="totals-row">
        <span class="totals-label">Amount Paid</span>
        <span class="totals-value"><?= number_format((float) ($invoice['amount_paid'] ?? 0), 2) ?></span>
    </div>
    <div class="totals-row balance-due">
        <span class="totals-label">Balance Due</span>
        <span class="totals-value"><?= formatAmount((float) ($invoice['balance_due'] ?? $invoice['total'])) ?></span>
    </div>
</div>

<!-- Terms & Notes -->
<?php if (!empty($invoice['terms']) || !empty($invoice['notes'])): ?>
<div class="notes-section">
    <?php if (!empty($invoice['terms'])): ?>
    <div style="margin-bottom: 12px;">
        <div class="section-label">Terms</div>
        <div class="section-content"><?= nl2br(\DoubleE\Core\View::e($invoice['terms'])) ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($invoice['notes'])): ?>
    <div>
        <div class="section-label">Notes</div>
        <div class="section-content"><?= nl2br(\DoubleE\Core\View::e($invoice['notes'])) ?></div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
