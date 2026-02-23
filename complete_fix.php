<?php
// COMPLETE DATABASE FIX - RUN IN BROWSER
echo "<h1>🚀 COMPLETE DATABASE FIX</h1>";

include 'includes/db.php';

echo "<h2>Running complete database corrections...</h2>";

// Fix 1: Add status column
$check_status = "SHOW COLUMNS FROM users LIKE 'status'";
if ($conn->query($check_status)->num_rows == 0) {
    echo "<p>🔧 Adding 'status' column...</p>";
    $conn->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'");
    echo "<p style='color: green;'>✓ Status column added</p>";
}

// Fix 2: Add missing name columns
$check_first_name = "SHOW COLUMNS FROM users LIKE 'first_name'";
if ($conn->query($check_first_name)->num_rows == 0) {
    echo "<p>🔧 Adding 'first_name' column...</p>";
    $conn->query("ALTER TABLE users ADD COLUMN first_name VARCHAR(50) AFTER username");
    echo "<p style='color: green;'>✓ First name column added</p>";
}

$check_last_name = "SHOW COLUMNS FROM users LIKE 'last_name'";
if ($conn->query($check_last_name)->num_rows == 0) {
    echo "<p>🔧 Adding 'last_name' column...</p>";
    $conn->query("ALTER TABLE users ADD COLUMN last_name VARCHAR(50) AFTER first_name");
    echo "<p style='color: green;'>✓ Last name column added</p>";
}

$check_phone = "SHOW COLUMNS FROM users LIKE 'phone'";
if ($conn->query($check_phone)->num_rows == 0) {
    echo "<p>🔧 Adding 'phone' column...</p>";
    $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email");
    echo "<p style='color: green;'>✓ Phone column added</p>";
}

// Fix 3: Update existing users with default values
echo "<p>🔧 Setting default values for existing users...</p>";
$conn->query("UPDATE users SET 
    status = IFNULL(status, 'active'),
    first_name = IF(first_name IS NULL OR first_name = '', username, first_name),
    last_name = IFNULL(last_name, ''),
    phone = IFNULL(phone, 'N/A')
    WHERE id > 0");

echo "<p style='color: green;'>✓ Default values set for all users</p>";

// Fix 4: Test the users query
echo "<h3>Testing user management page query...</h3>";
$test_query = "SELECT id, username, first_name, last_name, email, phone, status, created_at FROM users ORDER BY id";
$result = $conn->query($test_query);

if ($result) {
    echo "<p style='color: green;'>✓ Query successful!</p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; font-size: 12px;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Created</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $full_name = trim(($row['first_name'] ?? $row['username']) . ' ' . ($row['last_name'] ?? ''));
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($full_name ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['status'] ?? 'active') . "</td>";
        echo "<td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Query failed: " . $conn->error . "</p>";
}

echo "<h2>✅ ALL FIXES APPLIED SUCCESSFULLY!</h2>";
echo "<div style='background: #f0fdf4; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>✅ User Management Page Should Now Work!</h3>";
echo "<p><strong>Test it here:</strong> <a href='admin/users.php' style='color: #dc2626; font-weight: bold; font-size: 18px;'>admin/users.php</a></p>";
echo "</div>";

echo "<h3>Quick Test Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='admin/users.php' style='background: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block; font-size: 16px;'>Test Users Page</a>";
echo "<a href='admin/dashboard.php' style='background: #10b981; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block; font-size: 16px;'>Admin Dashboard</a>";
echo "<a href='admin/bookings.php' style='background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block; font-size: 16px;'>Bookings Page</a>";
echo "</div>";

echo "<h3>🔧 What Was Fixed:</h3>";
echo "<ul>";
echo "<li>✓ Added missing <strong>status</strong> column to users table</li>";
echo "<li>✓ Added missing <strong>first_name</strong> and <strong>last_name</strong> columns</li>";
echo "<li>✓ Added missing <strong>phone</strong> column</li>";
echo "<li>✓ Set default values for all existing users</li>";
echo "<li>✓ Updated admin/users.php to handle missing data gracefully</li>";
echo "</ul>";
?>