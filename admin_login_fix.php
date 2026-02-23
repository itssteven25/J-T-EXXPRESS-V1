<?php
// Quick Admin Login Fix
echo "<h1>🔧 Quick Admin Login Fix</h1>";

include 'includes/db.php';

// Ensure admin tables exist
echo "<h2>Creating/Verifying Admin Tables...</h2>";

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

$conn->query($admin_users_table);
$conn->query($admin_logs_table);

echo "<p>✓ Admin tables verified/created</p>";

// Check/create admin user
echo "<h2>Checking Admin User...</h2>";
$check_admin = "SELECT id, username, password FROM admin_users WHERE username='admin'";
$result = $conn->query($check_admin);

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p>✓ Admin user found: " . $admin['username'] . "</p>";
    
    // Verify/update password
    if (!password_verify('admin123', $admin['password'])) {
        echo "<p>⚠ Updating admin password...</p>";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("UPDATE admin_users SET password = '$new_hash' WHERE username = 'admin'");
        echo "<p>✓ Admin password updated</p>";
    } else {
        echo "<p>✓ Admin password is correct</p>";
    }
} else {
    echo "<p>⚠ Creating default admin user...</p>";
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $insert = $conn->query("INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$admin_hash', 'admin@jntexpress.com', 'super_admin')");
    if ($insert) {
        echo "<p>✓ Default admin user created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create admin: " . $conn->error . "</p>";
    }
}

// Test login
echo "<h2>Testing Login Process...</h2>";
$username = 'admin';
$password = 'admin123';

$sql = "SELECT id, username, password, email, role FROM admin_users WHERE username = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
        echo "<p style='color: green; font-size: 20px;'>✅ ADMIN LOGIN IS WORKING!</p>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification failed</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Admin user not found or inactive</p>";
}

echo "<h2>✅ Access Links</h2>";
echo "<p><a href='admin/login.php' style='background: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; display: inline-block; margin: 10px;'>🔐 Admin Login</a></p>";
echo "<p><a href='admin_access.php' style='background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; display: inline-block; margin: 10px;'>🚪 Admin Access Portal</a></p>";

echo "<h3>🔧 Troubleshooting:</h3>";
echo "<ul>";
echo "<li><strong>XAMPP Services:</strong> Make sure Apache and MySQL are running</li>";
echo "<li><strong>Browser:</strong> Try clearing cache or using incognito mode</li>";
echo "<li><strong>URL:</strong> Visit http://localhost/J&T%20EXXPRESS%20V1/admin/login.php directly</li>";
echo "</ul>";
?>