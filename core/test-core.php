<?php
// Test script for Core J&T Express System
echo "<h1>J&T Express Core System Test</h1>\n";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>\n";
try {
    include '../includes/db.php';
    if ($conn && $conn->ping()) {
        echo "<p style='color: green;'>✓ Database connection successful</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection error: " . $e->getMessage() . "</p>\n";
}

// Test 2: Core Files Existence
echo "<h2>Test 2: Core Files</h2>\n";
$core_files = [
    'core-dashboard.php',
    'core-tracking.php', 
    'core-shipments.php',
    'core-account.php',
    'core-support.php',
    'core-header.php',
    'core-sidebar.php'
];

foreach ($core_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file exists</p>\n";
    } else {
        echo "<p style='color: red;'>✗ $file missing</p>\n";
    }
}

// Test 3: Database Tables
echo "<h2>Test 3: Required Database Tables</h2>\n";
$required_tables = [
    'users' => "SELECT COUNT(*) FROM users",
    'shipments' => "SELECT COUNT(*) FROM shipments",
    'user_profiles' => "SELECT COUNT(*) FROM user_profiles",
    'support_tickets' => "SELECT COUNT(*) FROM support_tickets"
];

foreach ($required_tables as $table => $query) {
    try {
        $result = $conn->query($query);
        if ($result) {
            echo "<p style='color: green;'>✓ Table '$table' accessible</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' error: " . $conn->error . "</p>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Table '$table' check error: " . $e->getMessage() . "</p>\n";
    }
}

// Test 4: Sample Data
echo "<h2>Test 4: Sample Data</h2>\n";
try {
    $shipments = $conn->query("SELECT * FROM shipments LIMIT 3");
    if ($shipments && $shipments->num_rows > 0) {
        echo "<p style='color: green;'>✓ Sample shipments exist:</p>\n";
        echo "<ul>\n";
        while ($shipment = $shipments->fetch_assoc()) {
            echo "<li>{$shipment['tracking_number']} - {$shipment['destination']} - {$shipment['status']}</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p style='color: orange;'>⚠ No sample shipments found</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Sample data check error: " . $e->getMessage() . "</p>\n";
}

echo "<h2>Test Summary</h2>\n";
echo "<p>The core system is ready for use if most tests show green checkmarks.</p>\n";
echo "<p>Access the core system at: <a href='index.php'>Core Dashboard</a></p>\n";
?>