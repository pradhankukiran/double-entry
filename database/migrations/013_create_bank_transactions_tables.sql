CREATE TABLE bank_import_batches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_account_id INT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_format ENUM('csv','ofx','qfx','manual') NOT NULL DEFAULT 'csv',
    transaction_count INT UNSIGNED NOT NULL DEFAULT 0,
    duplicate_count INT UNSIGNED NOT NULL DEFAULT 0,
    imported_by INT UNSIGNED NOT NULL,
    imported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bib_bank_account (bank_account_id),
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (imported_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bank_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_account_id INT UNSIGNED NOT NULL,
    import_batch_id INT UNSIGNED NULL,
    transaction_date DATE NOT NULL,
    description VARCHAR(500) NOT NULL,
    reference VARCHAR(100) NULL,
    amount DECIMAL(15,2) NOT NULL,
    fit_id VARCHAR(100) NULL,
    status ENUM('unmatched','matched','excluded','reconciled') NOT NULL DEFAULT 'unmatched',
    journal_entry_id INT UNSIGNED NULL,
    matched_at DATETIME NULL,
    matched_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bt_account_date (bank_account_id, transaction_date),
    INDEX idx_bt_status (status),
    INDEX idx_bt_fit_id (fit_id),
    INDEX idx_bt_journal_entry (journal_entry_id),
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (import_batch_id) REFERENCES bank_import_batches(id),
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id),
    FOREIGN KEY (matched_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
