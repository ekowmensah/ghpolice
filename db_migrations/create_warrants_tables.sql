-- Create warrants table
CREATE TABLE IF NOT EXISTS warrants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warrant_number VARCHAR(50) UNIQUE NOT NULL,
    warrant_type ENUM('Arrest', 'Search', 'Bench', 'Detention', 'Other') NOT NULL,
    case_id INT NOT NULL,
    suspect_id INT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE NULL,
    issued_by VARCHAR(255) NOT NULL,
    issuing_court VARCHAR(255) NULL,
    warrant_details TEXT NULL,
    execution_instructions TEXT NULL,
    status ENUM('Active', 'Executed', 'Cancelled', 'Expired') DEFAULT 'Active',
    executed_date DATETIME NULL,
    cancellation_reason TEXT NULL,
    cancelled_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (suspect_id) REFERENCES suspects(id) ON DELETE SET NULL,
    INDEX idx_warrant_number (warrant_number),
    INDEX idx_case_id (case_id),
    INDEX idx_suspect_id (suspect_id),
    INDEX idx_status (status),
    INDEX idx_issue_date (issue_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create warrant execution logs table
CREATE TABLE IF NOT EXISTS warrant_execution_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warrant_id INT NOT NULL,
    executed_by INT NOT NULL,
    execution_date DATETIME NOT NULL,
    execution_location VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warrant_id) REFERENCES warrants(id) ON DELETE CASCADE,
    FOREIGN KEY (executed_by) REFERENCES officers(id) ON DELETE RESTRICT,
    INDEX idx_warrant_id (warrant_id),
    INDEX idx_executed_by (executed_by),
    INDEX idx_execution_date (execution_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
