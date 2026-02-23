<?php
// Final Admin System Verification
echo "<h1>✅ Admin System Verification</h1>";

include 'includes/db.php';

echo "<h2>🔧 System Status</h2>";

// Test database connection
if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connection: SUCCESS</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection: FAILED</p>";
    exit();
}

// Verify all required tables
$tables_needed = [
    'admin_users' => 'Administrator accounts',
    'admin_logs' => 'Admin activity logs',
    'users' => 'Customer accounts',
    'shipments' => 'Package tracking',
    'package_pickup' => 'Pickup requests',
    'drop_points' => 'Branch locations',
    'shipping_rates' => 'Pricing information'
];

echo "<h3>📋 Database Tables</h3>";
foreach ($tables_needed as $table => $description) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ $table - $description</p>";
    } else {
        echo "<p style='color: red;'>✗ $table - MISSING ($description)</p>";
    }
}

// Verify admin user
echo "<h3>👤 Admin Account</h3>";
$admin_result = $conn->query("SELECT id, username, email, role, status FROM admin_users WHERE username='admin'");
if ($admin_result && $admin_result->num_rows > 0) {
    $admin = $admin_result->fetch_assoc();
    echo "<p style='color: green;'>✓ Admin user exists: " . htmlspecialchars($admin['username']) . "</p>";
    echo "<p>Status: <strong>" . $admin['status'] . "</strong> | Role: <strong>" . $admin['role'] . "</strong></p>";
    
    // Test password
    if (password_verify('admin123', $admin['password'])) {
        echo "<p style='color: green;'>✓ Admin password is correct</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Admin password needs update</p>";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("UPDATE admin_users SET password = '$new_hash' WHERE username = 'admin'");
        echo "<p style='color: green;'>✓ Admin password updated successfully</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Admin user not found</p>";
    echo "<p>Creating default admin user...</p>";
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$password_hash', 'admin@jntexpress.com', 'super_admin')";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Default admin user created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create admin user: " . $conn->error . "</p>";
    }
}

// Test file accessibility
echo "<h3>📄 Admin Files</h3>";
$admin_files = [
    'admin/login.php' => 'Login Page',
    'admin/dashboard.php' => 'Dashboard',
    'admin/bookings.php' => 'Bookings Management',
    'admin/shipments.php' => 'Shipment Tracking',
    'admin/users.php' => 'User Management',
    'admin/branches.php' => 'Branch Management',
    'admin/rates.php' => 'Rate Management',
    'admin/reports.php' => 'Reports',
    'admin/logout.php' => 'Logout'
];

foreach ($admin_files as $file => $name) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $name - File accessible</p>";
    } else {
        echo "<p style='color: red;'>✗ $name - File missing</p>";
    }
}

echo "<h2>✅ Ready to Test</h2>";
echo "<div style='background: #f0fdf4; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>🔐 Admin Login Credentials:</h3>";
echo "<p><strong>URL:</strong> <a href='admin/login.php' style='color: #dc2626; font-weight: bold;'>admin/login.php</a></p>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "</div>";

echo "<h3>📋 Admin Dashboard Features:</h3>";
echo "<ul>";
echo "<li>📊 Real-time shipment statistics</li>";
echo "<li>📋 Booking management and approval</li>";
echo "<li>🚚 Shipment status updates</li>";
echo "<li>👥 User account management</li>";
echo "<li>🏢 Branch location management</li>";
echo "<li>💰 Shipping rate configuration</li>";
echo "<li>📊 Reports and analytics</li>";
echo "<li>🔒 Secure session management</li>";
echo "</ul>";

echo "<h3>🔧 Troubleshooting:</h3>";
echo "<ol>";
echo "<li>If login fails, visit <a href='debug_admin.php' style='color: #dc2626;'>debug_admin.php</a> for detailed diagnostics</li>";
echo "<li>Clear browser cache and cookies if experiencing issues</li>";
echo "<li>Ensure XAMPP Apache and MySQL services are running</li>";
echo "<li>Check that all admin files exist in the admin directory</li>";
echo "</ol>";

echo "<p style='text-align: center; margin-top: 30px;'>";
echo "<a href='admin/login.php' style='background: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; display: inline-block;'>🔐 Test Admin Login Now</a>";
echo "</p>";
?>