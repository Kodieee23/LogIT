-- LogIT Database Schema

CREATE DATABASE IF NOT EXISTS logit;
USE logit;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('staff', 'admin') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tasks Table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    department VARCHAR(100) NOT NULL,
    staff_helped VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('red', 'yellow', 'green') NOT NULL DEFAULT 'yellow',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Insert a default admin user (password: Admin@123)
-- Password hashed using bcrypt via PHP password_hash('Admin@123', PASSWORD_DEFAULT)
INSERT INTO users (username, password_hash, full_name, role) 
VALUES ('admin', '$2y$10$WpZ6zC.9gT0Ue0Z0Y.fJ/OMa0k.Oa2m3P.8L3eM1eE4O7.H/ZzXy6', 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert default categories
INSERT IGNORE INTO categories (name) VALUES 
('Hardware Repair'),
('Software Installation'),
('Network Issue'),
('User Support'),
('Server Maintenance');
