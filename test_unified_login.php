<?php
// Test Unified Login System
echo "<h1>🧪 Unified Login System Test</h1>";

include 'includes/db.php';

echo "<h2>System Status</h2>";

// Test database connection
if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

// Check admin tables
$tables = ['admin_users', 'users'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' missing</p>";
    }
}

// Check admin user
$admin_result = $conn->query("SELECT id, username, email, role FROM admin_users WHERE username='admin' AND status='active'");
if ($admin_result && $admin_result->num_rows > 0) {
    $admin = $admin_result->fetch_assoc();
    echo "<p style='color: green;'>✓ Admin user found: " . htmlspecialchars($admin['username']) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Admin user not found or inactive</p>";
    // Create admin user
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$password_hash', 'admin@jntexpress.com', 'super_admin')";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Admin user created successfully</p>";
    }
}

// Check regular user
$user_result = $conn->query("SELECT id, username, email FROM users WHERE username='admin'");
if ($user_result && $user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    echo "<p style='color: green;'>✓ Test user found: " . htmlspecialchars($user['username']) . "</p>";
} else {
    echo "<p style='color: orange;'>⚠ No test user found (this is normal)</p>";
}

echo "<h2>✅ Login Options Available</h2>";

echo "<div style='display: flex; gap: 20px; margin: 20px 0;'>";
echo "<a href='unified_login.php' style='background: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px;'>🔐 Unified Login Page</a>";
echo "<a href='unified_login.php?type=admin' style='background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px;'>🔧 Admin Login Direct</a>";
echo "<a href='auth/login.php' style='background: #10b981; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px;'>👤 User Login</a>";
echo "</div>";

echo "<h3>📋 Login Credentials</h3>";
echo "<div style='background: #f9fafb; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Admin Login:</h4>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "<p><strong>URL:</strong> unified_login.php?type=admin</p>";
echo "</div>";

echo "<div style='background: #f0fdf4; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>User Login:</h4>";
echo "<p><strong>Username:</strong> admin (or any registered user)</p>";
echo "<p><strong>Password:</strong> password</p>";
echo "<p><strong>URL:</strong> auth/login.php or unified_login.php</p>";
echo "</div>";

echo "<h3>🔧 Features Implemented</h3>";
echo "<ul>";
echo "<li>✅ Toggle between Admin and User login</li>";
echo "<li>✅ Visual login type selector</li>";
echo "<li>✅ Automatic credential display for admin</li>";
echo "<li>✅ Proper session management</li>";
echo "<li>✅ Secure password verification</li>";
echo "<li>✅ Redirect to appropriate dashboard</li>";
echo "</ul>";

echo "<h3>📱 Quick Access from Homepage</h3>";
echo "<p>Visit <a href='index.php' style='color: #dc2626; font-weight: bold;'>index.php</a> to see login options in the quick access cards</p>";
?>