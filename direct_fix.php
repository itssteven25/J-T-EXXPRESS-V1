<?php
// DIRECT DATABASE FIX - RUN THIS IN YOUR BROWSER
echo "<h1>🔧 DIRECT DATABASE FIX</h1>";
echo "<h2>Running database corrections...</h2>";

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "jnt_express";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color: green;'>✓ Database connection successful</p>";

// Fix 1: Add role column to users table
echo "<h3>Fix 1: Adding role column to users table</h3>";
$check_role = "SHOW COLUMNS FROM users LIKE 'role'";
$result = $conn->query($check_role);

if ($result->num_rows == 0) {
    $add_role = "ALTER TABLE users ADD COLUMN role ENUM('user', 'courier', 'admin') DEFAULT 'user'";
    if ($conn->query($add_role) === TRUE) {
        echo "<p style='color: green;'>✓ Role column added successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding role column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Role column already exists</p>";
}

// Fix 2: Ensure admin user exists
echo "<h3>Fix 2: Checking admin user</h3>";
$check_admin = "SELECT id, username FROM admin_users WHERE username='admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$admin_password', 'admin@jntexpress.com', 'super_admin')";
    if ($conn->query($insert_admin) === TRUE) {
        echo "<p style='color: green;'>✓ Admin user created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating admin user: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Admin user already exists</p>";
}

// Fix 3: Test the bookings query that was failing
echo "<h3>Fix 3: Testing bookings query</h3>";
$test_query = "SELECT id, username FROM users WHERE (role = 'courier' OR role = 'admin') AND status = 'active' LIMIT 1";
$result = $conn->query($test_query);

if ($result !== FALSE) {
    echo "<p style='color: green;'>✓ Bookings query test successful</p>";
} else {
    echo "<p style='color: red;'>✗ Bookings query still failing: " . $conn->error . "</p>";
}

// Fix 4: Update any NULL roles
echo "<h3>Fix 4: Updating NULL roles</h3>";
$update_roles = "UPDATE users SET role = 'user' WHERE role IS NULL OR role = ''";
if ($conn->query($update_roles) === TRUE) {
    echo "<p style='color: green;'>✓ NULL roles updated to 'user'</p>";
}

echo "<h2>✅ ALL FIXES APPLIED</h2>";
echo "<div style='background: #f0fdf4; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Now try accessing:</h3>";
echo "<p><strong>Admin Login:</strong> <a href='admin/login.php' style='color: #dc2626; font-weight: bold;'>admin/login.php</a></p>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "</div>";

echo "<h3>Quick Test Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='admin/login.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Try Admin Login</a>";
echo "<a href='admin/bookings.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Try Bookings Page</a>";
echo "</div>";

$conn->close();
?>