<?php
// DIRECT DATABASE FIX FOR STATUS COLUMN
// RUN THIS SCRIPT IN YOUR BROWSER
echo "<h1>🔧 DIRECT DATABASE FIX FOR STATUS COLUMN</h1>";

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "jnt_express";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color: green;'>✓ Database connected successfully</p>";

// Add status column to users table
echo "<h2>Adding status column to users table...</h2>";
$check_status = "SHOW COLUMNS FROM users LIKE 'status'";
$result = $conn->query($check_status);

if ($result->num_rows == 0) {
    $add_status = "ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'";
    if ($conn->query($add_status) === TRUE) {
        echo "<p style='color: green; font-size: 18px;'>✅ STATUS COLUMN ADDED SUCCESSFULLY!</p>";
    } else {
        echo "<p style='color: red;'>Error adding status column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Status column already exists</p>";
}

// Add other missing columns
$columns_to_add = [
    'first_name' => "ALTER TABLE users ADD COLUMN first_name VARCHAR(50) AFTER username",
    'last_name' => "ALTER TABLE users ADD COLUMN last_name VARCHAR(50) AFTER first_name", 
    'phone' => "ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email"
];

foreach ($columns_to_add as $column => $sql) {
    $check = "SHOW COLUMNS FROM users LIKE '$column'";
    if ($conn->query($check)->num_rows == 0) {
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Added $column column</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ $column column already exists</p>";
    }
}

// Set default values
echo "<h2>Setting default values...</h2>";
$update_sql = "UPDATE users SET 
    status = IFNULL(status, 'active'),
    first_name = IF(first_name IS NULL OR first_name = '', username, first_name),
    last_name = IFNULL(last_name, ''),
    phone = IFNULL(phone, 'N/A')
    WHERE id > 0";

if ($conn->query($update_sql) === TRUE) {
    echo "<p style='color: green;'>✅ Default values set for all users</p>";
}

// Test the bookings query that was failing
echo "<h2>Testing the problematic query...</h2>";
$test_query = "SELECT id, username FROM users WHERE (role = 'courier' OR role = 'admin') AND status = 'active' LIMIT 5";
$result = $conn->query($test_query);

if ($result !== FALSE) {
    echo "<p style='color: green; font-size: 18px;'>✅ BOOKINGS QUERY WORKING!</p>";
    echo "<p>Found " . $result->num_rows . " active users with courier/admin roles</p>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>active</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>❌ Query still failing: " . $conn->error . "</p>";
}

echo "<h2>✅ FIX COMPLETE!</h2>";
echo "<div style='background: #f0fdf4; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>✅ Now try accessing:</h3>";
echo "<p><strong>Admin Bookings Page:</strong> <a href='admin/bookings.php' style='color: #dc2626; font-weight: bold; font-size: 18px;'>admin/bookings.php</a></p>";
echo "<p><strong>Admin Login:</strong> <a href='admin/login.php' style='color: #007cba; font-weight: bold; font-size: 18px;'>admin/login.php</a></p>";
echo "</div>";

$conn->close();
?>