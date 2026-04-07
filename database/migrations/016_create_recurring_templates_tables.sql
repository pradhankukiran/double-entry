CREATE TABLE recurring_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    template_type ENUM('journal_entry','invoice','bill') NOT NULL,
    frequency ENUM('daily','weekly','monthly','quarterly','annually') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    next_run_date DATE NOT NULL,
    last_run_date DATE NULL,
    total_occurrences INT UNSIGNED NULL,
    occurrences_created INT UNSIGNED NOT NULL DEFAULT 0,
    auto_post TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rt_active_next (is_active, next_run_date),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recurring_template_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id INT UNSIGNED NOT NULL,
    line_number SMALLINT NOT NULL,
    account_id INT UNSIGNED NOT NULL,
    description VARCHAR(500) NULL,
    debit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    credit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    INDEX idx_rtl_template (template_id),
    FOREIGN KEY (template_id) REFERENCES recurring_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
