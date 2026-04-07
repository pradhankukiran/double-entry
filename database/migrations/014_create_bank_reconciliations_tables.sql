CREATE TABLE bank_reconciliations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_account_id INT UNSIGNED NOT NULL,
    statement_date DATE NOT NULL,
    statement_balance DECIMAL(15,2) NOT NULL,
    reconciled_balance DECIMAL(15,2) NULL,
    difference DECIMAL(15,2) NULL,
    status ENUM('in_progress','completed','voided') NOT NULL DEFAULT 'in_progress',
    reconciled_by INT UNSIGNED NOT NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_br_bank_account (bank_account_id),
    INDEX idx_br_status (status),
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (reconciled_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bank_reconciliation_lines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reconciliation_id INT UNSIGNED NOT NULL,
    journal_entry_line_id BIGINT UNSIGNED NOT NULL,
    is_cleared TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_recon_jel (reconciliation_id, journal_entry_line_id),
    FOREIGN KEY (reconciliation_id) REFERENCES bank_reconciliations(id) ON DELETE CASCADE,
    FOREIGN KEY (journal_entry_line_id) REFERENCES journal_entry_lines(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
