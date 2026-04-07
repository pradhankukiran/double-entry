CREATE TABLE bank_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED NOT NULL UNIQUE,
    bank_name VARCHAR(255) NOT NULL,
    account_name VARCHAR(255) NULL,
    account_number_last4 CHAR(4) NULL,
    currency_code CHAR(3) NOT NULL DEFAULT 'USD',
    account_type ENUM('checking','savings','credit_card','line_of_credit','other') NOT NULL DEFAULT 'checking',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    current_balance DECIMAL(15,2) NULL,
    last_imported_at DATETIME NULL,
    last_reconciled_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ba_active (is_active),
    FOREIGN KEY (account_id) REFERENCES accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
