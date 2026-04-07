CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_number VARCHAR(30) NOT NULL UNIQUE,
    contact_id INT UNSIGNED NOT NULL,
    type ENUM('received','made') NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash','check','bank_transfer','credit_card','other') NOT NULL,
    reference VARCHAR(100) NULL,
    deposit_account_id INT UNSIGNED NOT NULL,
    notes TEXT NULL,
    journal_entry_id INT UNSIGNED NULL,
    status ENUM('draft','posted','voided') NOT NULL DEFAULT 'draft',
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pmt_contact (contact_id),
    INDEX idx_pmt_type (type),
    INDEX idx_pmt_status (status),
    INDEX idx_pmt_date (payment_date),
    FOREIGN KEY (contact_id) REFERENCES contacts(id),
    FOREIGN KEY (deposit_account_id) REFERENCES accounts(id),
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payment_allocations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pa_payment (payment_id),
    INDEX idx_pa_invoice (invoice_id),
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
