<?php
// ERROR DIAGNOSIS TOOL
echo "<h1>🔍 Error Diagnosis Tool</h1>";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Current Error...</h2>";

// Test the exact query that was failing
include 'includes/db.php';

echo "<h3>Testing the problematic query:</h3>";
echo "<p>Query: SELECT id, username FROM users WHERE (role = 'courier' OR role = 'admin') AND status = 'active'</p>";

$test_query = "SELECT id, username FROM users WHERE (role = 'courier' OR role = 'admin') AND status = 'active'";
$result = $conn->query($test_query);

if ($result === FALSE) {
    echo "<p style='color: red; font-weight: bold;'>✗ Query FAILED with error:</p>";
    echo "<p style='color: red; background: #fee2e2; padding: 10px; border-radius: 5px;'>" . $conn->error . "</p>";
} else {
    echo "<p style='color: green;'>✓ Query SUCCESSFUL</p>";
    echo "<p>Found " . $result->num_rows . " users with courier/admin roles</p>";
}

// Show current database structure
echo "<h3>Current Users Table Structure:</h3>";
$structure = $conn->query("DESCRIBE users");
if ($structure) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check if role column exists
echo "<h3>Role Column Check:</h3>";
$role_check = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($role_check && $role_check->num_rows > 0) {
    $role_info = $role_check->fetch_assoc();
    echo "<p style='color: green;'>✓ Role column exists:</p>";
    echo "<ul>";
    echo "<li><strong>Field:</strong> " . $role_info['Field'] . "</li>";
    echo "<li><strong>Type:</strong> " . $role_info['Type'] . "</li>";
    echo "<li><strong>Default:</strong> " . $role_info['Default'] . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Role column does NOT exist</p>";
    echo "<p>You need to run the fix script: <a href='direct_fix.php'>direct_fix.php</a></p>";
}

echo "<h2>Quick Actions:</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='direct_fix.php' style='background: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Apply Database Fix</a>";
echo "<a href='admin/bookings.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Test Bookings Page</a>";
echo "<a href='admin/login.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Test Admin Login</a>";
echo "</div>";
?>