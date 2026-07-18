-- Legal Advisor Website Database
-- Import this file into your MySQL database through phpMyAdmin

CREATE DATABASE IF NOT EXISTS legal_advisor;
USE legal_advisor;

-- Admin table (single admin)
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admin (username, email, password, full_name) VALUES 
('admin', 'admin@legaladvisor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Clients table
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Lawyers table
CREATE TABLE lawyers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    specializations TEXT, -- JSON format: ["Family Law", "Corporate", "Criminal"]
    experience_years INT DEFAULT 0,
    bar_number VARCHAR(50),
    bio TEXT,
    profile_image VARCHAR(255) DEFAULT 'default-lawyer.jpg',
    status ENUM('pending', 'approved', 'rejected', 'inactive') DEFAULT 'pending',
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    lawyer_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    legal_issue_type VARCHAR(100) NOT NULL,
    issue_description TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    approved_by INT, -- admin or lawyer id
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (lawyer_id) REFERENCES lawyers(id) ON DELETE SET NULL
);

-- Cases table (auto-created from approved appointments)
CREATE TABLE cases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_reference VARCHAR(20) UNIQUE NOT NULL,
    appointment_id INT NOT NULL,
    client_id INT NOT NULL,
    lawyer_id INT,
    case_title VARCHAR(200) NOT NULL,
    case_description TEXT NOT NULL,
    status ENUM('New', 'Under Review', 'In Progress', 'Resolved', 'Closed') DEFAULT 'New',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (lawyer_id) REFERENCES lawyers(id) ON DELETE SET NULL
);

-- Chat messages table
CREATE TABLE chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_id INT NOT NULL,
    sender_type ENUM('client', 'lawyer', 'admin') NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
);

-- Lawyer ratings table
CREATE TABLE lawyer_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lawyer_id INT NOT NULL,
    client_id INT NOT NULL,
    case_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lawyer_id) REFERENCES lawyers(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (lawyer_id, client_id, case_id)
);

-- Time slots table for appointment booking
CREATE TABLE time_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slot_time TIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert default time slots
INSERT INTO time_slots (slot_time) VALUES 
('09:00:00'), ('10:00:00'), ('11:00:00'), ('12:00:00'),
('14:00:00'), ('15:00:00'), ('16:00:00'), ('17:00:00');

-- Legal issue types
CREATE TABLE legal_issue_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

INSERT INTO legal_issue_types (type_name, description) VALUES 
('Family Law', 'Divorce, child custody, adoption, domestic relations'),
('Corporate Law', 'Business formation, contracts, mergers, compliance'),
('Criminal Law', 'Criminal defense, DUI, theft, assault cases'),
('Immigration Law', 'Visa applications, citizenship, deportation defense'),
('Personal Injury', 'Accident claims, medical malpractice, compensation'),
('Real Estate Law', 'Property transactions, landlord-tenant, zoning'),
('Employment Law', 'Workplace disputes, discrimination, wrongful termination'),
('Intellectual Property', 'Patents, trademarks, copyrights, trade secrets');