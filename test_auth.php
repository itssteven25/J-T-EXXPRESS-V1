<?php
// Test Authentication System
echo "<h1>Authentication System Test</h1>";

// Include database connection
include 'includes/db.php';

echo "<h2>1. Database Connection Test</h2>";
if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if database exists and is selected
    $result = $conn->query("SELECT DATABASE()");
    $db = $result->fetch_row();
    echo "<p>Current database: <strong>" . ($db[0] ?? 'None') . "</strong></p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

echo "<h2>2. Users Table Check</h2>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Users table exists</p>";
    
    // Check table structure
    $result = $conn->query("DESCRIBE users");
    echo "<p>Users table structure:</p><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Users table does not exist</p>";
}

echo "<h2>3. Check Existing Users</h2>";
$result = $conn->query("SELECT id, username, email, created_at FROM users");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Found " . $result->num_rows . " user(s)</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠ No users found in database</p>";
}

echo "<h2>4. Test Password Verification</h2>";
// Test with default admin password
$test_password = 'password';
$test_hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "<p>Test password: <strong>$test_password</strong></p>";
echo "<p>Generated hash: <code>" . substr($test_hash, 0, 30) . "...</code></p>";
echo "<p>Verification test: " . (password_verify($test_password, $test_hash) ? "<span style='color: green;'>✓ PASS</span>" : "<span style='color: red;'>✗ FAIL</span>") . "</p>";

echo "<h2>5. Check Default Admin User</h2>";
$result = $conn->query("SELECT id, username, password FROM users WHERE username = 'admin'");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p style='color: green;'>✓ Admin user found</p>";
    echo "<p>Username: <strong>" . $admin['username'] . "</strong></p>";
    echo "<p>Password hash: <code>" . substr($admin['password'], 0, 30) . "...</code></p>";
    
    // Test password verification
    if (password_verify('password', $admin['password'])) {
        echo "<p style='color: green;'>✓ Admin password verification successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Admin password verification failed</p>";
        echo "<p>Creating new admin user with correct password...</p>";
        
        // Update admin password
        $new_hash = password_hash('password', PASSWORD_DEFAULT);
        $update = $conn->query("UPDATE users SET password = '$new_hash' WHERE username = 'admin'");
        if ($update) {
            echo "<p style='color: green;'>✓ Admin password updated successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to update admin password</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ Admin user not found</p>";
    echo "<p>Creating default admin user...</p>";
    
    // Create admin user
    $admin_hash = password_hash('password', PASSWORD_DEFAULT);
    $insert = $conn->query("INSERT INTO users (username, password, email) VALUES ('admin', '$admin_hash', 'admin@jntexpress.com')");
    if ($insert) {
        echo "<p style='color: green;'>✓ Admin user created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create admin user: " . $conn->error . "</p>";
    }
}

echo "<h2>6. Test Login Simulation</h2>";
// Simulate login
$username = 'admin';
$password = 'password';

$sql = "SELECT id, username, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        echo "<p style='color: green;'>✓ Login simulation successful</p>";
        echo "<p>User ID: " . $user['id'] . "</p>";
        echo "<p>Username: " . htmlspecialchars($user['username']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Password verification failed in simulation</p>";
    }
} else {
    echo "<p style='color: red;'>✗ User not found in simulation</p>";
}

echo "<h2>✅ Test Complete</h2>";
echo "<p><a href='auth/login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Login Now</a></p>";
echo "<p><a href='auth/register.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Try Register Now</a></p>";
?>