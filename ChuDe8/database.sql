-- Import trong phpMyAdmin. KHONG xoa du lieu da co.
CREATE DATABASE IF NOT EXISTS quan_ly_tin_tuc
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quan_ly_tin_tuc;
CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    ai_summary TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
