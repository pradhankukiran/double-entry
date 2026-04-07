<?php

declare(strict_types=1);

namespace DoubleE\Models;

class PaymentAllocation extends BaseModel
{
    protected string $table = 'payment_allocations';

    /**
     * Get all allocations for a payment, with invoice document numbers.
     */
    public function getByPayment(int $paymentId): array
    {
        $sql = "SELECT pa.*, i.document_number, i.document_type, i.total AS invoice_total, i.balance_due
                FROM {$this->table} pa
                INNER JOIN invoices i ON i.id = pa.invoice_id
                WHERE pa.payment_id = ?
                ORDER BY pa.id";

        return $this->db->query($sql, [$paymentId]);
    }

    /**
     * Get all allocations for an invoice, with payment details.
     */
    public function getByInvoice(int $invoiceId): array
    {
        $sql = "SELECT pa.*, p.payment_number, p.payment_date, p.type AS payment_type,
                       p.payment_method, p.amount AS payment_amount
                FROM {$this->table} pa
                INNER JOIN payments p ON p.id = pa.payment_id
                WHERE pa.invoice_id = ?
                ORDER BY p.payment_date, pa.id";

        return $this->db->query($sql, [$invoiceId]);
    }
}
