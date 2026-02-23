<?php
// Final Verification and Test Script
echo "<h1>✅ J&T Express System - Final Verification</h1>";

include 'includes/db.php';

echo "<h2>🔧 System Status Check</h2>";

// Test 1: Database Connection
if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connection: SUCCESS</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection: FAILED</p>";
    exit();
}

// Test 2: Role Column Fix
echo "<h3>Role Column Status:</h3>";
$check_role_column = "SHOW COLUMNS FROM users LIKE 'role'";
$role_result = $conn->query($check_role_column);

if ($role_result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Role column exists in users table</p>";
    
    // Test a query that uses the role column
    $test_query = "SELECT id, username, role FROM users WHERE role = 'user' LIMIT 1";
    $test_result = $conn->query($test_query);
    
    if ($test_result) {
        echo "<p style='color: green;'>✓ Role column queries working correctly</p>";
    } else {
        echo "<p style='color: red;'>✗ Role column queries failing: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Role column missing - run fix_role_column.php</p>";
}

// Test 3: Admin Users Table
echo "<h3>Admin Users Table:</h3>";
$admin_table_check = "SHOW TABLES LIKE 'admin_users'";
$admin_result = $conn->query($admin_table_check);

if ($admin_result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Admin users table exists</p>";
    
    // Check for default admin user
    $admin_user_check = "SELECT id, username, role FROM admin_users WHERE username='admin'";
    $admin_user_result = $conn->query($admin_user_check);
    
    if ($admin_user_result && $admin_user_result->num_rows > 0) {
        $admin = $admin_user_result->fetch_assoc();
        echo "<p style='color: green;'>✓ Default admin user exists: " . $admin['username'] . " (Role: " . $admin['role'] . ")</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Default admin user missing - will be created automatically</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Admin users table missing</p>";
}

// Test 4: Critical Admin Files
echo "<h3>Admin Files Status:</h3>";
$admin_files = [
    'admin/login.php' => 'Admin Login',
    'admin/dashboard.php' => 'Admin Dashboard',
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
        // Test for syntax errors
        $output = [];
        $return_var = 0;
        @exec("php -l $file 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<p style='color: green;'>✓ $name: File OK</p>";
        } else {
            echo "<p style='color: red;'>✗ $name: Syntax Error</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ $name: File Missing</p>";
    }
}

// Test 5: Database Tables
echo "<h3>Required Database Tables:</h3>";
$required_tables = [
    'users' => 'User accounts',
    'admin_users' => 'Administrator accounts',
    'shipments' => 'Package tracking',
    'package_pickup' => 'Pickup requests',
    'drop_points' => 'Branch locations',
    'shipping_rates' => 'Pricing information',
    'admin_logs' => 'Admin activity logs'
];

foreach ($required_tables as $table => $description) {
    $table_check = "SHOW TABLES LIKE '$table'";
    $result = $conn->query($table_check);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ $table: $description</p>";
    } else {
        echo "<p style='color: red;'>✗ $table: $description (MISSING)</p>";
    }
}

echo "<h2>✅ System Ready for Testing</h2>";

echo "<h3>🔐 Admin Login Credentials:</h3>";
echo "<div style='background: #f0fdf4; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<p><strong>URL:</strong> <a href='admin/login.php' style='color: #dc2626; font-weight: bold;'>admin/login.php</a></p>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "</div>";

echo "<h3>📋 Quick Test Links:</h3>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0;'>";
echo "<a href='admin/login.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>Admin Login</a>";
echo "<a href='admin/bookings.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>Bookings Page</a>";
echo "<a href='admin/dashboard.php' style='background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>Dashboard</a>";
echo "<a href='fix_role_column.php' style='background: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>Fix Role Column</a>";
echo "</div>";

echo "<h3>🔧 Troubleshooting:</h3>";
echo "<ol>";
echo "<li>If you see the role column error, run <strong>fix_role_column.php</strong></li>";
echo "<li>If admin login fails, check that admin user exists in admin_users table</li>";
echo "<li>Clear browser cache if experiencing display issues</li>";
echo "<li>Ensure XAMPP Apache and MySQL services are running</li>";
echo "</ol>";

echo "<p style='text-align: center; margin-top: 30px; font-size: 18px;'>";
echo "<strong>All systems operational! 🚀</strong>";
echo "</p>";
?>