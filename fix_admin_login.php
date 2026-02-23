<?php
// Admin Login Diagnostic Tool
echo "<h1>🔧 Admin Login Diagnostic</h1>";

// Include database connection
include 'includes/db.php';

echo "<h2>1. Database Connection Test</h2>";
if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

echo "<h2>2. Admin Tables Check</h2>";
$tables = ['admin_users', 'admin_logs', 'admin_sessions'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' missing</p>";
    }
}

echo "<h2>3. Check Admin Users</h2>";
$result = $conn->query("SELECT id, username, email, role, status FROM admin_users");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Found " . $result->num_rows . " admin user(s)</p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ No admin users found</p>";
    echo "<p>Creating default admin user...</p>";
    
    // Create default admin
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$password_hash', 'admin@jntexpress.com', 'super_admin')";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Default admin user created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create admin user: " . $conn->error . "</p>";
    }
}

echo "<h2>4. Test Admin Login Process</h2>";
// Test login simulation
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
        echo "<p style='color: green;'>✅ Login test PASSED!</p>";
        echo "<p>Admin ID: " . $admin['id'] . "</p>";
        echo "<p>Username: " . htmlspecialchars($admin['username']) . "</p>";
        echo "<p>Email: " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p>Role: " . $admin['role'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification FAILED</p>";
        echo "<p>Updating admin password...</p>";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $update = $conn->query("UPDATE admin_users SET password = '$new_hash' WHERE username = 'admin'");
        if ($update) {
            echo "<p style='color: green;'>✓ Admin password updated successfully</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Admin user not found or inactive</p>";
}

echo "<h2>5. File System Check</h2>";
$admin_files = [
    'admin/login.php',
    'admin/dashboard.php',
    'admin/logout.php'
];

foreach ($admin_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ File exists: $file</p>";
    } else {
        echo "<p style='color: red;'>✗ File missing: $file</p>";
    }
}

echo "<h2>✅ Quick Access Links</h2>";
echo "<p><a href='admin/login.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Admin Login Page</a></p>";
echo "<p><a href='admin_access.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Admin Access Portal</a></p>";

echo "<h3>🔧 If still not working:</h3>";
echo "<ol>";
echo "<li>Clear browser cache and cookies</li>";
echo "<li>Try incognito/private browsing mode</li>";
echo "<li>Check if XAMPP Apache and MySQL services are running</li>";
echo "<li>Verify port 80/443 is not blocked</li>";
echo "</ol>";
?>