-- Database Schema for ASFOUR Survey 2026

CREATE DATABASE IF NOT EXISTS asfour_survey;
USE asfour_survey;

-- Table: surveys
CREATE TABLE IF NOT EXISTS surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL,
    angkatan VARCHAR(20) DEFAULT NULL,
    aspirasi TEXT NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- AI Analysis columns
    sentiment ENUM('positive', 'negative', 'neutral') DEFAULT NULL,
    category ENUM('kesiswaan', 'kurikulum', 'humas', 'sarana_prasarana', 'lainnya') DEFAULT NULL,
    ai_confidence DECIMAL(3,2) DEFAULT NULL,
    ai_explanation TEXT DEFAULT NULL,
    analyzed TINYINT(1) DEFAULT 0,
    analyzed_at TIMESTAMP NULL
);

-- Table: admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: settings
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY DEFAULT 1,
    survey_status ENUM('open', 'closed') DEFAULT 'open',
    CHECK (id = 1)
);

-- Table: admin_logs
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Initial Data
INSERT IGNORE INTO settings (id, survey_status) VALUES (1, 'open');

-- Default Super Admin (username: empeka4lahat, password: 4uf0ur4lahat)
INSERT IGNORE INTO admins (username, password, role) VALUES ('empeka4lahat', '$2y$10$0M.j96mY6L2WnQG3.Oa8yeI9gO2f1s.Vv1W7m5mX8pP5F4V5Z6Y1.', 'super_admin');
