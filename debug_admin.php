<?php
// Admin Page Debug Tool
echo "<h1>🔧 Admin Page Debug Tool</h1>";

// Test each admin page
$admin_pages = [
    'admin/login.php' => 'Admin Login',
    'admin/dashboard.php' => 'Admin Dashboard',
    'admin/bookings.php' => 'Booking Management',
    'admin/shipments.php' => 'Shipment Management',
    'admin/users.php' => 'User Management',
    'admin/branches.php' => 'Branch Management',
    'admin/rates.php' => 'Rate Management',
    'admin/reports.php' => 'Reports',
    'admin/logout.php' => 'Logout'
];

echo "<h2>📄 Admin Page Status Check</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Page</th><th>Status</th><th>File Exists</th><th>Error Check</th></tr>";

foreach ($admin_pages as $file => $name) {
    echo "<tr>";
    echo "<td><strong>$name</strong><br><small>$file</small></td>";
    
    // Check if file exists
    if (file_exists($file)) {
        echo "<td style='color: green;'>✓ File Exists</td>";
        
        // Test file for syntax errors
        $output = [];
        $return_var = 0;
        exec("php -l $file", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<td style='color: green;'>✓ No Syntax Errors</td>";
        } else {
            echo "<td style='color: red;'>✗ Syntax Error</td>";
        }
        
        // Test database connection in file
        $content = file_get_contents($file);
        if (strpos($content, 'include') !== false && strpos($content, 'db.php') !== false) {
            echo "<td style='color: green;'>✓ Database Connection</td>";
        } else {
            echo "<td style='color: orange;'>⚠ No DB Include Found</td>";
        }
        
    } else {
        echo "<td style='color: red;'>✗ File Missing</td>";
        echo "<td style='color: red;'>✗ Cannot Test</td>";
        echo "<td style='color: red;'>✗ Cannot Test</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Test database connection
echo "<h2>💾 Database Connection Test</h2>";
include 'includes/db.php';

if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test required tables
    $required_tables = ['admin_users', 'admin_logs', 'admin_sessions', 'users', 'shipments', 'package_pickup', 'drop_points', 'shipping_rates'];
    echo "<h3>📋 Required Tables Check</h3>";
    
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        }
    }
    
    // Test admin user
    echo "<h3>👤 Admin User Check</h3>";
    $admin_result = $conn->query("SELECT id, username, email, role, status FROM admin_users WHERE username='admin'");
    if ($admin_result && $admin_result->num_rows > 0) {
        $admin = $admin_result->fetch_assoc();
        echo "<p style='color: green;'>✓ Admin user found: " . htmlspecialchars($admin['username']) . "</p>";
        echo "<p>Status: " . $admin['status'] . " | Role: " . $admin['role'] . "</p>";
        
        // Test password
        if (password_verify('admin123', $admin['password'])) {
            echo "<p style='color: green;'>✓ Admin password is correct</p>";
        } else {
            echo "<p style='color: red;'>✗ Admin password is incorrect</p>";
            echo "<p>Fixing password...</p>";
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $conn->query("UPDATE admin_users SET password = '$new_hash' WHERE username = 'admin'");
            echo "<p style='color: green;'>✓ Admin password fixed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Admin user not found</p>";
        echo "<p>Creating admin user...</p>";
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$password_hash', 'admin@jntexpress.com', 'super_admin')";
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✓ Admin user created successfully</p>";
        }
    }
    
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// Test session functionality
echo "<h2>🔒 Session Test</h2>";
session_start();
$_SESSION['test_admin'] = 'test_value';
if (isset($_SESSION['test_admin'])) {
    echo "<p style='color: green;'>✓ Session functionality working</p>";
    unset($_SESSION['test_admin']);
} else {
    echo "<p style='color: red;'>✗ Session functionality not working</p>";
}

// Quick access links
echo "<h2>✅ Quick Access Links</h2>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0;'>";
foreach ($admin_pages as $file => $name) {
    if (file_exists($file)) {
        echo "<a href='$file' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>$name</a>";
    }
}
echo "</div>";

echo "<h3>🔧 Common Fixes:</h3>";
echo "<ol>";
echo "<li><strong>Database Issues:</strong> Run the database fix script</li>";
echo "<li><strong>File Missing:</strong> Check if all admin files exist</li>";
echo "<li><strong>Permission Issues:</strong> Ensure PHP has read/write permissions</li>";
echo "<li><strong>Session Problems:</strong> Clear browser cache and cookies</li>";
echo "</ol>";
?>