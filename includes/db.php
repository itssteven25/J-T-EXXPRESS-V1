<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "jt_express";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
} else {
    die("Error creating database: " . $conn->error);
}

// Create tables if they don't exist
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

$shipments_table = "CREATE TABLE IF NOT EXISTS shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) UNIQUE NOT NULL,
    destination VARCHAR(100) NOT NULL,
    status ENUM('Pick Up', 'In Transit', 'Delivered') DEFAULT 'Pick Up',
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// New table for shipping rates
$rates_table = "CREATE TABLE IF NOT EXISTS shipping_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_type VARCHAR(50) NOT NULL,
    weight_from DECIMAL(5,2) NOT NULL,
    weight_to DECIMAL(5,2) NOT NULL,
    price DECIMAL(8,2) NOT NULL,
    estimated_days INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// New table for package pickup requests
$pickup_table = "CREATE TABLE IF NOT EXISTS package_pickup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pickup_tracking_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    pickup_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    special_instructions TEXT,
    status ENUM('Pending', 'Scheduled', 'In Transit', 'Completed', 'Cancelled') DEFAULT 'Pending',
    assigned_courier_id INT,
    actual_pickup_time DATETIME,
    delivery_time DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (assigned_courier_id) REFERENCES users(id)
)";

// New table for drop points
$drop_points_table = "CREATE TABLE IF NOT EXISTS drop_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    phone VARCHAR(20),
    operating_hours TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// New table for service info
$service_info_table = "CREATE TABLE IF NOT EXISTS service_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// New table for user history/activities
$history_table = "CREATE TABLE IF NOT EXISTS user_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_type VARCHAR(50) NOT NULL,
    activity_description TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

// New table for user accounts/profiles
$user_accounts_table = "CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'Philippines',
    avatar VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

// New table for notifications
$notifications_table = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

// New table for support tickets
$support_table = "CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
    assigned_to INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

// New table for shipment history
$shipment_history_table = "CREATE TABLE IF NOT EXISTS shipment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT,
    status VARCHAR(50) NOT NULL,
    notes TEXT,
    updated_by VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id)
)";

// New table for shipment receivers
$shipment_receivers_table = "CREATE TABLE IF NOT EXISTS shipment_receivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT,
    receiver_name VARCHAR(100) NOT NULL,
    receiver_email VARCHAR(100),
    receiver_phone VARCHAR(20),
    receiver_address TEXT,
    receiver_city VARCHAR(50),
    receiver_postal_code VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id)
)";

// Enhanced shipments table with additional fields
$enhanced_shipments = "ALTER TABLE shipments 
    ADD COLUMN IF NOT EXISTS user_id INT,
    ADD COLUMN IF NOT EXISTS service_type VARCHAR(50) DEFAULT 'Standard',
    ADD COLUMN IF NOT EXISTS weight DECIMAL(8,2),
    ADD COLUMN IF NOT EXISTS declared_value DECIMAL(10,2),
    ADD COLUMN IF NOT EXISTS sender_name VARCHAR(100),
    ADD COLUMN IF NOT EXISTS sender_email VARCHAR(100),
    ADD COLUMN IF NOT EXISTS sender_phone VARCHAR(20),
    ADD COLUMN IF NOT EXISTS sender_address TEXT,
    ADD FOREIGN KEY (user_id) REFERENCES users(id)";

$conn->query($users_table);
$conn->query($shipments_table);
$conn->query($rates_table);
$conn->query($pickup_table);
$conn->query($drop_points_table);
$conn->query($service_info_table);
$conn->query($history_table);
$conn->query($user_accounts_table);
$conn->query($notifications_table);
$conn->query($support_table);
$conn->query($shipment_receivers_table);
$conn->query($shipment_history_table);

// Apply enhancements to existing tables
@$conn->query($enhanced_shipments); // Suppress errors for existing columns

// Insert default admin user if none exists
$check_admin = "SELECT id FROM users WHERE username='admin'";
$result = $conn->query($check_admin);
if ($result->num_rows == 0) {
    $hashed_password = password_hash('password', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO users (username, password, email) VALUES ('admin', '$hashed_password', 'admin@jntexpress.com')";
    $conn->query($insert_admin);
}

// Insert default shipping rates
$check_rates = "SELECT id FROM shipping_rates LIMIT 1";
$result_rates = $conn->query($check_rates);
if ($result_rates->num_rows == 0) {
    $insert_rates = "INSERT INTO shipping_rates (service_type, weight_from, weight_to, price, estimated_days) VALUES 
        ('Standard', 0.00, 1.00, 50.00, 3),
        ('Standard', 1.01, 3.00, 80.00, 3),
        ('Standard', 3.01, 5.00, 110.00, 4),
        ('Express', 0.00, 1.00, 90.00, 1),
        ('Express', 1.01, 3.00, 130.00, 1),
        ('Express', 3.01, 5.00, 170.00, 2)";
    $conn->query($insert_rates);
}

// Insert sample drop points
$check_drop_points = "SELECT id FROM drop_points LIMIT 1";
$result_dp = $conn->query($check_drop_points);
if ($result_dp->num_rows == 0) {
    $insert_drop_points = "INSERT INTO drop_points (name, address, city, postal_code, phone, operating_hours) VALUES 
        ('J&T Main Branch - Manila', '123 Main Street, Manila', 'Manila', '1000', '02-1234567', '8:00 AM - 8:00 PM'),
        ('J&T Branch - Makati', '456 Business Ave, Makati City', 'Makati', '1200', '02-2345678', '8:00 AM - 8:00 PM'),
        ('J&T Branch - Quezon City', '789 Central Ave, Quezon City', 'Quezon City', '1100', '02-3456789', '8:00 AM - 8:00 PM')";
    $conn->query($insert_drop_points);
}

// Insert sample service info
$check_service_info = "SELECT id FROM service_info LIMIT 1";
$result_si = $conn->query($check_service_info);
if ($result_si->num_rows == 0) {
    $insert_service_info = "INSERT INTO service_info (title, content, category) VALUES 
        ('Free Shipping', 'Enjoy free shipping on orders above ₱500 within Metro Manila.', 'Promos'),
        ('Same Day Delivery', 'Avail same day delivery service for Metro Manila areas.', 'Services'),
        ('Insurance Coverage', 'Protect your valuable items with our insurance coverage options.', 'Services')";
    $conn->query($insert_service_info);
}

// Create admin users table
$admin_users_table = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('super_admin', 'admin', 'support') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
)";

// Create admin logs table
$admin_logs_table = "CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id)
)";

// Create admin sessions table
$admin_sessions_table = "CREATE TABLE IF NOT EXISTS admin_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    session_id VARCHAR(128) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id)
)";

$conn->query($admin_users_table);
$conn->query($admin_logs_table);
$conn->query($admin_sessions_table);

// Insert default admin user
$check_admin_user = "SELECT id FROM admin_users WHERE username='admin'";
$result_admin = $conn->query($check_admin_user);
if ($result_admin->num_rows == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$admin_password', 'admin@jntexpress.com', 'super_admin')";
    $conn->query($insert_admin);
}

// Ensure users table has role column
$check_role_column = "SHOW COLUMNS FROM users LIKE 'role'";
$role_result = $conn->query($check_role_column);
if ($role_result->num_rows == 0) {
    $add_role_column = "ALTER TABLE users ADD COLUMN role ENUM('user', 'courier', 'admin') DEFAULT 'user'";
    $conn->query($add_role_column);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Function to generate unique pickup tracking number
function generatePickupTrackingNumber($conn) {
    do {
        $tracking_number = 'PU' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        $check_sql = "SELECT id FROM package_pickup WHERE pickup_tracking_number = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    
    return $tracking_number;
}
