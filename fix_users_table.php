<?php
// FIX MISSING STATUS COLUMN IN USERS TABLE
echo "<h1>🔧 Fixing Missing Status Column</h1>";

include 'includes/db.php';

echo "<h2>Adding missing columns to users table...</h2>";

// Add status column if it doesn't exist
$check_status = "SHOW COLUMNS FROM users LIKE 'status'";
$result = $conn->query($check_status);

if ($result->num_rows == 0) {
    echo "<p>Adding 'status' column to users table...</p>";
    $add_status = "ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'";
    
    if ($conn->query($add_status)) {
        echo "<p style='color: green;'>✓ Status column added successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to add status column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Status column already exists</p>";
}

// Add first_name and last_name columns if they don't exist
$check_first_name = "SHOW COLUMNS FROM users LIKE 'first_name'";
if ($conn->query($check_first_name)->num_rows == 0) {
    echo "<p>Adding 'first_name' column...</p>";
    $conn->query("ALTER TABLE users ADD COLUMN first_name VARCHAR(50) AFTER username");
}

$check_last_name = "SHOW COLUMNS FROM users LIKE 'last_name'";
if ($conn->query($check_last_name)->num_rows == 0) {
    echo "<p>Adding 'last_name' column...</p>";
    $conn->query("ALTER TABLE users ADD COLUMN last_name VARCHAR(50) AFTER first_name");
}

$check_phone = "SHOW COLUMNS FROM users LIKE 'phone'";
if ($conn->query($check_phone)->num_rows == 0) {
    echo "<p>Adding 'phone' column...</p>";
    $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email");
}

// Set default values for existing users
echo "<h3>Setting default values for existing users...</h3>";
$update_defaults = "UPDATE users SET 
    status = 'active',
    first_name = IF(first_name IS NULL OR first_name = '', username, first_name),
    last_name = IF(last_name IS NULL, '', last_name),
    phone = IF(phone IS NULL, 'N/A', phone)
    WHERE status IS NULL OR status = ''";

if ($conn->query($update_defaults)) {
    echo "<p style='color: green;'>✓ Default values set for all users</p>";
}

// Test the fix by querying users
echo "<h3>Testing the fix...</h3>";
$test_query = "SELECT id, username, first_name, last_name, email, phone, status FROM users ORDER BY id LIMIT 10";
$result = $conn->query($test_query);

if ($result) {
    echo "<p style='color: green;'>✓ Query successful! Displaying user data:</p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Username</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Phone</th><th>Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['last_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['status'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Query failed: " . $conn->error . "</p>";
}

echo "<h2>✅ Fix Complete!</h2>";
echo "<div style='background: #f0fdf4; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Now try accessing:</h3>";
echo "<p><strong>Admin Users Page:</strong> <a href='admin/users.php' style='color: #dc2626; font-weight: bold;'>admin/users.php</a></p>";
echo "</div>";

echo "<h3>Quick Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='admin/users.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Test Users Page</a>";
echo "<a href='admin/dashboard.php' style='background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Admin Dashboard</a>";
echo "</div>";
?>