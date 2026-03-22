-- Database: internship_system

CREATE DATABASE IF NOT EXISTS internship_system;
USE internship_system;

-- 1. Users Table (Common for all roles)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student', 'company') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    college VARCHAR(255) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Companies Table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(255) DEFAULT '',
    full_name VARCHAR(100) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    phone VARCHAR(20) DEFAULT '',
    address TEXT,
    industry VARCHAR(100) DEFAULT '',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Internships Table
CREATE TABLE IF NOT EXISTS internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    location VARCHAR(255),
    start_date DATE,
    end_date DATE,
    payment_type ENUM('Paid', 'Unpaid') DEFAULT 'Unpaid',
    stipend DECIMAL(10,2) DEFAULT 0.00,
    posted_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- 5. Applications Table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internship_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    applied_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    certificate_issued ENUM('yes', 'no') DEFAULT 'no',
    certificate_path VARCHAR(255) NULL,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Default Admin Account
-- Email: admin@test.com
-- Password: admin123 (Hash below is for 'admin123')
INSERT INTO users (name, email, password, role) VALUES 
('System Admin', 'admin@test.com', '$2y$10$GA4/PJjnvu9X3zXottFdNen1/y3lPYbn6k2TucjAMG4otN1yCZ//K', 'admin');
-- Note: You should register a new admin account or change this password immediately.
