<?php
// Quick Database Fix for Role Column Issue
echo "<h1>🔧 Database Fix for Role Column</h1>";

include 'includes/db.php';

echo "<h2>Fixing Database Schema...</h2>";

// Add role column to users table if it doesn't exist
$check_role_column = "SHOW COLUMNS FROM users LIKE 'role'";
$role_result = $conn->query($check_role_column);

if ($role_result->num_rows == 0) {
    echo "<p>Adding 'role' column to users table...</p>";
    $add_role_column = "ALTER TABLE users ADD COLUMN role ENUM('user', 'courier', 'admin') DEFAULT 'user'";
    
    if ($conn->query($add_role_column)) {
        echo "<p style='color: green;'>✓ Role column added successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to add role column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Role column already exists</p>";
}

// Update existing users to have role = 'user' by default
$update_users = "UPDATE users SET role = 'user' WHERE role IS NULL OR role = ''";
if ($conn->query($update_users)) {
    echo "<p style='color: green;'>✓ Users updated with default role</p>";
}

// Test the fix
echo "<h2>Testing the Fix...</h2>";
$test_query = "SELECT id, username, role FROM users LIMIT 5";
$test_result = $conn->query($test_query);

if ($test_result) {
    echo "<p style='color: green;'>✓ Query with role column works!</p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Role</th></tr>";
    while ($row = $test_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . ($row['role'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Query still failing: " . $conn->error . "</p>";
}

echo "<h2>✅ Fix Complete</h2>";
echo "<p>The role column issue has been resolved. You can now access the admin pages without errors.</p>";

echo "<h3>Quick Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='admin/login.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Admin Login</a>";
echo "<a href='admin/bookings.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Bookings Page</a>";
echo "</div>";
?>