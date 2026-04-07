CREATE TABLE fiscal_years (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('open','closing','closed') NOT NULL DEFAULT 'open',
    closed_by INT UNSIGNED NULL,
    closed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (closed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fiscal_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fiscal_year_id INT UNSIGNED NOT NULL,
    period_number SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('open','locked','closed') NOT NULL DEFAULT 'open',
    locked_by INT UNSIGNED NULL,
    locked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_fp (fiscal_year_id, period_number),
    FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_years(id),
    FOREIGN KEY (locked_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
