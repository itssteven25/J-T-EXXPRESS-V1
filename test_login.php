<?php
// Test login functionality
include 'includes/db.php';

echo "<h1>Login System Test</h1>";

// Check database connection
echo "<h2>1. Database Connection</h2>";
if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

// Check if users table exists
echo "<h2>2. Users Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Users table exists</p>";
} else {
    echo "<p style='color: red;'>✗ Users table not found</p>";
    exit();
}

// Check if admin user exists
echo "<h2>3. Admin User</h2>";
$result = $conn->query("SELECT id, username, password FROM users WHERE username = 'admin'");
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p style='color: green;'>✓ Admin user found</p>";
    echo "<p>Username: " . $user['username'] . "</p>";
    echo "<p>Password hash: " . substr($user['password'], 0, 20) . "...</p>";
    
    // Test password verification
    echo "<h2>4. Password Verification</h2>";
    if (password_verify('password', $user['password'])) {
        echo "<p style='color: green;'>✓ Password verification successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Password verification failed</p>";
        echo "<p>Recreating admin user with correct password...</p>";
        
        // Recreate admin user
        $hashed_password = password_hash('password', PASSWORD_DEFAULT);
        $conn->query("DELETE FROM users WHERE username = 'admin'");
        $conn->query("INSERT INTO users (username, password, email) VALUES ('admin', '$hashed_password', 'admin@jntexpress.com')");
        echo "<p style='color: green;'>✓ Admin user recreated</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Admin user not found, creating...</p>";
    $hashed_password = password_hash('password', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, email) VALUES ('admin', '$hashed_password', 'admin@jntexpress.com')");
    echo "<p style='color: green;'>✓ Admin user created</p>";
}

// Test login simulation
echo "<h2>5. Login Simulation</h2>";
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
        echo "<p>Login would redirect to: ../dashboard/index.php</p>";
    } else {
        echo "<p style='color: red;'>✗ Login simulation failed - password mismatch</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Login simulation failed - user not found</p>";
}

echo "<h2>6. Test Credentials</h2>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> password</p>";

echo "<h2>7. Quick Links</h2>";
echo "<p><a href='auth/login.php'>Go to Login Page</a></p>";
echo "<p><a href='core/index.php'>Go to Core System</a></p>";
?>