-- J&T Express Database Schema

CREATE DATABASE IF NOT EXISTS jt_express;
USE jt_express;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Shipments table
CREATE TABLE shipments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tracking_number VARCHAR(50) UNIQUE NOT NULL,
    destination VARCHAR(100) NOT NULL,
    status ENUM('Pick Up', 'In Transit', 'Delivered') DEFAULT 'Pick Up',
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data for testing
INSERT INTO users (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@jntexpress.com'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@example.com');

INSERT INTO shipments (tracking_number, destination, status) VALUES 
('JT123456789', 'New York, USA', 'Delivered'),
('JT987654321', 'Los Angeles, USA', 'In Transit'),
('JT456789123', 'Chicago, USA', 'Pick Up'),
('JT789123456', 'Miami, USA', 'In Transit'),
('JT321654987', 'Seattle, USA', 'Delivered'),
('JT654987321', 'Boston, USA', 'Pick Up');