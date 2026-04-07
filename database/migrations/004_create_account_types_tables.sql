CREATE TABLE account_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    normal_balance ENUM('debit','credit') NOT NULL,
    financial_statement ENUM('balance_sheet','income_statement') NOT NULL,
    display_order SMALLINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE account_subtypes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_type_id INT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_subtype (account_type_id, code),
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
