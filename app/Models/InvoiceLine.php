<?php

declare(strict_types=1);

namespace DoubleE\Models;

class InvoiceLine extends BaseModel
{
    protected string $table = 'invoice_lines';

    /**
     * Get all lines for an invoice with account details.
     */
    public function getByInvoice(int $invoiceId): array
    {
        $sql = "SELECT il.*, a.account_number, a.name AS account_name
                FROM {$this->table} il
                INNER JOIN accounts a ON a.id = il.account_id
                WHERE il.invoice_id = ?
                ORDER BY il.line_order, il.id";

        return $this->db->query($sql, [$invoiceId]);
    }

    /**
     * Bulk insert lines for an invoice.
     *
     * @param int   $invoiceId The parent invoice ID
     * @param array $lines     Array of line data, each with: description, account_id, quantity, unit_price, tax_amount, line_total, line_order
     */
    public function createMany(int $invoiceId, array $lines): void
    {
        $sql = "INSERT INTO {$this->table}
                    (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        foreach ($lines as $index => $line) {
            $this->db->exec($sql, [
                $invoiceId,
                $line['description'],
                $line['account_id'],
                $line['quantity'] ?? '1.0000',
                $line['unit_price'] ?? '0.0000',
                $line['tax_amount'] ?? '0.00',
                $line['line_total'] ?? '0.00',
                $line['line_order'] ?? $index,
            ]);
        }
    }
}
