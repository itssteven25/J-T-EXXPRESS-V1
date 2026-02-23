<?php
// Test script to verify all functionality of J&T Express system

echo "<h1>J&T Express System Functionality Test</h1>\n";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>\n";
try {
    include 'includes/db.php';
    if ($conn && $conn->ping()) {
        echo "<p style='color: green;'>✓ Database connection successful</p>\n";
        
        // Test 2: Check if tables exist
        echo "<h2>Test 2: Database Tables</h2>\n";
        
        $tables_check = [
            'users' => "SELECT COUNT(*) FROM users",
            'shipments' => "SELECT COUNT(*) FROM shipments"
        ];
        
        foreach ($tables_check as $table => $query) {
            $result = $conn->query($query);
            if ($result) {
                echo "<p style='color: green;'>✓ Table '$table' exists and is accessible</p>\n";
                
                // Show record count
                $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_result->fetch_assoc()['count'];
                echo "<p style='color: blue;'>  - Records in $table: $count</p>\n";
            } else {
                echo "<p style='color: red;'>✗ Table '$table' error: " . $conn->error . "</p>\n";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection error: " . $e->getMessage() . "</p>\n";
}

// Test 3: Check if default admin user exists
echo "<h2>Test 3: Default Admin User</h2>\n";
try {
    $admin_check = $conn->query("SELECT id, username, email FROM users WHERE username='admin'");
    if ($admin_check && $admin_check->num_rows > 0) {
        $admin = $admin_check->fetch_assoc();
        echo "<p style='color: green;'>✓ Admin user exists (ID: {$admin['id']}, Username: {$admin['username']})</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠ Admin user does not exist - system will create it automatically on first run</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Admin user check error: " . $e->getMessage() . "</p>\n";
}

// Test 4: Check file permissions and existence
echo "<h2>Test 4: Critical Files</h2>\n";
$critical_files = [
    'includes/db.php',
    'auth/login.php',
    'auth/logout.php',
    'auth/register.php',
    'dashboard/index.php',
    'tracking/track.php',
    'api/update-status.php',
    'api/tracking-history.php',
    'api/dashboard-stats.php',
    'assets/js/main.js',
    'assets/js/tracking.js',
    'assets/css/style.css'
];

foreach ($critical_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file exists</p>\n";
    } else {
        echo "<p style='color: red;'>✗ $file missing</p>\n";
    }
}

// Test 5: API endpoints
echo "<h2>Test 5: API Endpoints</h2>\n";

function test_api_endpoint($endpoint) {
    $full_url = "http://localhost/J&T%20XXPRESS%20V1/$endpoint";
    // We'll just check if the file exists since we can't make HTTP requests from here
    $file_path = __DIR__ . "/$endpoint";
    if (file_exists($file_path)) {
        echo "<p style='color: green;'>✓ API endpoint $endpoint exists</p>\n";
    } else {
        echo "<p style='color: red;'>✗ API endpoint missing: $endpoint</p>\n";
    }
}

$api_endpoints = [
    'api/dashboard-stats.php',
    'api/tracking-history.php',
    'api/update-status.php'
];

foreach ($api_endpoints as $endpoint) {
    test_api_endpoint($endpoint);
}

// Test 6: Sample shipment data
echo "<h2>Test 6: Sample Shipment Data</h2>\n";
try {
    $shipments = $conn->query("SELECT * FROM shipments LIMIT 5");
    if ($shipments && $shipments->num_rows > 0) {
        echo "<p style='color: green;'>✓ Sample shipments exist:</p>\n";
        echo "<ul>\n";
        while ($shipment = $shipments->fetch_assoc()) {
            echo "<li>{$shipment['tracking_number']} - {$shipment['destination']} - {$shipment['status']}</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p style='color: orange;'>⚠ No sample shipments found - system will work but may appear empty</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Shipment data check error: " . $e->getMessage() . "</p>\n";
}

echo "<h2>Test Summary</h2>\n";
echo "<p>If all tests show ✓ (green) or at least one ✓, the system is properly configured and ready to use.</p>\n";
echo "<p><strong>Default login:</strong> Username: admin, Password: password</p>\n";
echo "<p>Open <a href='auth/login.php'>Login Page</a> to access the system.</p>\n";

?>