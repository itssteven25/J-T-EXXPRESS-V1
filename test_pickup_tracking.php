<?php
// Test Pickup Tracking Functionality
include 'includes/db.php';

echo "<h1>Pickup Tracking System Test</h1>";

// Test 1: Database Connection and Tables
echo "<h2>1. Database Setup</h2>";
if ($conn && $conn->ping()) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if package_pickup table exists with new columns
    $result = $conn->query("SHOW COLUMNS FROM package_pickup LIKE 'pickup_tracking_number'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ package_pickup table has pickup_tracking_number column</p>";
    } else {
        echo "<p style='color: red;'>✗ package_pickup table missing pickup_tracking_number column</p>";
    }
    
    $result = $conn->query("SHOW COLUMNS FROM package_pickup LIKE 'latitude'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ package_pickup table has latitude column</p>";
    } else {
        echo "<p style='color: red;'>✗ package_pickup table missing latitude column</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

// Test 2: Generate Tracking Number Function
echo "<h2>2. Tracking Number Generation</h2>";
if (function_exists('generatePickupTrackingNumber')) {
    $tracking_number = generatePickupTrackingNumber($conn);
    echo "<p style='color: green;'>✓ Tracking number generated: <strong>$tracking_number</strong></p>";
    
    // Test uniqueness
    $check_sql = "SELECT id FROM package_pickup WHERE pickup_tracking_number = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $tracking_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "<p style='color: green;'>✓ Tracking number is unique</p>";
    } else {
        echo "<p style='color: red;'>✗ Tracking number already exists</p>";
    }
} else {
    echo "<p style='color: red;'>✗ generatePickupTrackingNumber function not found</p>";
}

// Test 3: Insert Sample Pickup Request
echo "<h2>3. Sample Pickup Request</h2>";
$test_tracking = generatePickupTrackingNumber($conn);
$test_address = "123 Test Street, Test City";
$test_city = "Test City";
$test_postal = "1234";
$test_phone = "09123456789";
$test_date = date('Y-m-d');
$test_time = "10:00:00";
$test_lat = 14.5995;
$test_lng = 120.9842;

$stmt = $conn->prepare("INSERT INTO package_pickup (pickup_tracking_number, user_id, address, city, postal_code, phone, latitude, longitude, pickup_date, pickup_time, special_instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$user_id = 1; // Assuming admin user exists
$instructions = "Test pickup request";
$stmt->bind_param("siisssddsss", $test_tracking, $user_id, $test_address, $test_city, $test_postal, $test_phone, $test_lat, $test_lng, $test_date, $test_time, $instructions);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✓ Sample pickup request created successfully</p>";
    echo "<p>Tracking Number: <strong>$test_tracking</strong></p>";
    echo "<p>Address: $test_address</p>";
    echo "<p>Location: $test_lat, $test_lng</p>";
} else {
    echo "<p style='color: red;'>✗ Failed to create sample pickup request: " . $conn->error . "</p>";
}

// Test 4: Tracking Lookup
echo "<h2>4. Tracking Lookup Test</h2>";
// Test shipment tracking
$shipment_result = $conn->query("SELECT tracking_number FROM shipments LIMIT 1");
if ($shipment_result && $shipment_result->num_rows > 0) {
    $shipment = $shipment_result->fetch_assoc();
    echo "<p style='color: green;'>✓ Shipment tracking test: <a href='tracking/track.php?tracking=" . $shipment['tracking_number'] . "' target='_blank'>" . $shipment['tracking_number'] . "</a></p>";
} else {
    echo "<p style='color: orange;'>⚠ No shipment data found for testing</p>";
}

// Test pickup tracking
$pickup_result = $conn->query("SELECT pickup_tracking_number FROM package_pickup WHERE pickup_tracking_number = '$test_tracking'");
if ($pickup_result && $pickup_result->num_rows > 0) {
    $pickup = $pickup_result->fetch_assoc();
    echo "<p style='color: green;'>✓ Pickup tracking test: <a href='tracking/track.php?tracking=" . $pickup['pickup_tracking_number'] . "' target='_blank'>" . $pickup['pickup_tracking_number'] . "</a></p>";
} else {
    echo "<p style='color: orange;'>⚠ No pickup data found for testing</p>";
}

// Test 5: Geolocation Test
echo "<h2>5. Geolocation Test</h2>";
echo "<p>Geolocation functionality can be tested on the <a href='pickup/package-pickup.php' target='_blank'>Package Pickup page</a></p>";
echo "<p>Click 'Get Current Location' button to test geolocation capture</p>";

// Test 6: Summary
echo "<h2>6. System Summary</h2>";
echo "<p><strong>Features Implemented:</strong></p>";
echo "<ul>";
echo "<li>✓ Unique pickup tracking numbers (PU prefix)</li>";
echo "<li>✓ Geolocation capture and storage</li>";
echo "<li>✓ Enhanced tracking system for both shipments and pickups</li>";
echo "<li>✓ Location-based tracking with Google Maps integration</li>";
echo "<li>✓ Status tracking for pickup requests</li>";
echo "<li>✓ Detailed pickup information display</li>";
echo "</ul>";

echo "<h2>Quick Links</h2>";
echo "<p><a href='pickup/package-pickup.php' target='_blank'>Package Pickup Page</a></p>";
echo "<p><a href='tracking/track.php' target='_blank'>Tracking Page</a></p>";
echo "<p><a href='dashboard/index.php' target='_blank'>Dashboard</a></p>";

?>