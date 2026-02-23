<?php
// XAMPP Service Status Checker
echo "<h1>🖥️ XAMPP Service Status Checker</h1>";

echo "<h2>Checking System Requirements...</h2>";

// Check if we can connect to localhost
echo "<h3>1. Localhost Connection Test</h3>";
$connection = @fsockopen("localhost", 80, $errno, $errstr, 5);
if ($connection) {
    echo "<p style='color: green;'>✓ Apache appears to be running (port 80 accessible)</p>";
    fclose($connection);
} else {
    echo "<p style='color: red;'>✗ Apache may not be running (port 80 not accessible)</p>";
}

// Check MySQL connection
echo "<h3>2. MySQL Connection Test</h3>";
$mysql_connection = @fsockopen("localhost", 3306, $errno, $errstr, 5);
if ($mysql_connection) {
    echo "<p style='color: green;'>✓ MySQL appears to be running (port 3306 accessible)</p>";
    fclose($mysql_connection);
} else {
    echo "<p style='color: red;'>✗ MySQL may not be running (port 3306 not accessible)</p>";
}

echo "<h3>3. Database Connection Test</h3>";
try {
    include 'includes/db.php';
    if ($conn && $conn->ping()) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        
        // Test a simple query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            echo "<p style='color: green;'>✓ Database queries working</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>🔧 Quick Fix Commands</h2>";
echo "<p>If services aren't running, try these steps:</p>";
echo "<ol>";
echo "<li><strong>Start XAMPP Control Panel</strong></li>";
echo "<li><strong>Start Apache service</strong></li>";
echo "<li><strong>Start MySQL service</strong></li>";
echo "<li><strong>Refresh this page</strong></li>";
echo "</ol>";

echo "<h2>✅ Direct Links to Test</h2>";
echo "<p><a href='fix_auth.php' style='background: #ff6b35; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Run Authentication Fix</a></p>";
echo "<p><a href='auth/login.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Login Page</a></p>";
echo "<p><a href='auth/register.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; display: inline-block;'>Register Page</a></p>";

echo "<h3>Need XAMPP?</h3>";
echo "<p>Download XAMPP from: <a href='https://www.apachefriends.org/download.html' target='_blank'>https://www.apachefriends.org/download.html</a></p>";
?>