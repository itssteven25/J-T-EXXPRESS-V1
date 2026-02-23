<?php
// Quick Fix for Authentication Issues
echo "<h1>🔧 Quick Authentication Fix</h1>";

// Force database recreation and user setup
include 'includes/db.php';

echo "<h2>Fixing Authentication Issues...</h2>";

// Ensure all tables exist
echo "<p>1. Ensuring database tables exist...</p>";
$conn->query($users_table);
$conn->query($user_accounts_table);
$conn->query($history_table);

// Check if admin user exists
echo "<p>2. Checking admin user...</p>";
$check_admin = "SELECT id, username, password FROM users WHERE username='admin'";
$result = $conn->query($check_admin);

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p>✓ Admin user found: " . $admin['username'] . "</p>";
    
    // Verify password
    if (!password_verify('password', $admin['password'])) {
        echo "<p>⚠ Admin password needs update...</p>";
        $new_hash = password_hash('password', PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password = '$new_hash' WHERE username = 'admin'");
        echo "<p>✓ Admin password updated</p>";
    } else {
        echo "<p>✓ Admin password is correct</p>";
    }
} else {
    echo "<p>⚠ Admin user not found, creating...</p>";
    $admin_hash = password_hash('password', PASSWORD_DEFAULT);
    $insert = $conn->query("INSERT INTO users (username, password, email) VALUES ('admin', '$admin_hash', 'admin@jntexpress.com')");
    if ($insert) {
        echo "<p>✓ Admin user created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create admin: " . $conn->error . "</p>";
    }
}

// Test the login process
echo "<h2>3. Testing Login Process</h2>";
$username = 'admin';
$password = 'password';

$sql = "SELECT id, username, password, email FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        echo "<p style='color: green;'>✅ Login test PASSED!</p>";
        echo "<p>User ID: " . $user['id'] . "</p>";
        echo "<p>Username: " . htmlspecialchars($user['username']) . "</p>";
        echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification FAILED</p>";
    }
} else {
    echo "<p style='color: red;'>❌ User not found</p>";
}

echo "<h2>4. Common Issues and Solutions</h2>";
echo "<ul>";
echo "<li><strong>XAMPP not running:</strong> Start XAMPP Control Panel and ensure Apache and MySQL are running</li>";
echo "<li><strong>Database connection:</strong> Make sure MySQL service is started in XAMPP</li>";
echo "<li><strong>Port conflicts:</strong> Check if port 80/443 (Apache) and 3306 (MySQL) are available</li>";
echo "<li><strong>File permissions:</strong> Ensure PHP has write permissions to the project folder</li>";
echo "</ul>";

echo "<h2>✅ Quick Access Links</h2>";
echo "<p><a href='auth/login.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Login Page</a></p>";
echo "<p><a href='auth/register.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Register Page</a></p>";
echo "<p><a href='index.php' style='background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Homepage</a></p>";

echo "<h3>Default Login Credentials:</h3>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> password</p>";

echo "<h3>If still not working:</h3>";
echo "<ol>";
echo "<li>Restart XAMPP services</li>";
echo "<li>Clear browser cache and cookies</li>";
echo "<li>Try incognito/private browsing mode</li>";
echo "<li>Check PHP error logs in XAMPP</li>";
echo "</ol>";
?>