<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Initialize variables
$success_message = '';
$error_message = '';
$form_errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form validation
    $required_fields = ['sender_name', 'sender_email', 'sender_phone', 'receiver_name', 'receiver_email', 'receiver_phone', 'receiver_address', 'receiver_city', 'receiver_postal_code', 'parcel_description', 'weight', 'address', 'city', 'postal_code', 'phone', 'pickup_date', 'pickup_time'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $form_errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // Validate email format
    if (!empty($_POST['sender_email']) && !filter_var($_POST['sender_email'], FILTER_VALIDATE_EMAIL)) {
        $form_errors['sender_email'] = 'Please enter a valid sender email address';
    }
    
    if (!empty($_POST['receiver_email']) && !filter_var($_POST['receiver_email'], FILTER_VALIDATE_EMAIL)) {
        $form_errors['receiver_email'] = 'Please enter a valid receiver email address';
    }
    
    // Validate phone numbers
    if (!empty($_POST['sender_phone']) && !preg_match('/^[0-9+\s\-()]{10,15}$/', $_POST['sender_phone'])) {
        $form_errors['sender_phone'] = 'Please enter a valid sender phone number';
    }
    
    if (!empty($_POST['receiver_phone']) && !preg_match('/^[0-9+\s\-()]{10,15}$/', $_POST['receiver_phone'])) {
        $form_errors['receiver_phone'] = 'Please enter a valid receiver phone number';
    }
    
    if (!empty($_POST['phone']) && !preg_match('/^[0-9+\s\-()]{10,15}$/', $_POST['phone'])) {
        $form_errors['phone'] = 'Please enter a valid pickup phone number';
    }
    
    // Validate weight
    if (!empty($_POST['weight']) && (!is_numeric($_POST['weight']) || $_POST['weight'] <= 0)) {
        $form_errors['weight'] = 'Please enter a valid weight (positive number)';
    }
    
    // Validate pickup date (must be today or future)
    if (!empty($_POST['pickup_date'])) {
        $pickup_date = new DateTime($_POST['pickup_date']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($pickup_date < $today) {
            $form_errors['pickup_date'] = 'Pickup date must be today or in the future';
        }
    }
    
    // If no validation errors, process the form
    if (empty($form_errors)) {
        // Sanitize input data
        $sender_name = trim($_POST['sender_name']);
        $sender_email = trim($_POST['sender_email']);
        $sender_phone = trim($_POST['sender_phone']);
        $receiver_name = trim($_POST['receiver_name']);
        $receiver_email = trim($_POST['receiver_email']);
        $receiver_phone = trim($_POST['receiver_phone']);
        $receiver_address = trim($_POST['receiver_address']);
        $receiver_city = trim($_POST['receiver_city']);
        $receiver_postal_code = trim($_POST['receiver_postal_code']);
        $parcel_description = trim($_POST['parcel_description']);
        $weight = floatval($_POST['weight']);
        $declared_value = !empty($_POST['declared_value']) ? floatval($_POST['declared_value']) : 0;
        $service_type = $_POST['service_type'] ?? 'Standard';
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $postal_code = trim($_POST['postal_code']);
        $phone = trim($_POST['phone']);
        $pickup_date = $_POST['pickup_date'];
        $pickup_time = $_POST['pickup_time'];
        $instructions = trim($_POST['special_instructions'] ?? '');
        $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
        $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
        
        // Generate unique tracking number
        $tracking_number = generatePickupTrackingNumber($conn);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert shipment record
            $shipment_stmt = $conn->prepare("INSERT INTO shipments (tracking_number, user_id, destination, service_type, weight, declared_value, sender_name, sender_email, sender_phone, sender_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending Pickup')");
            $shipment_stmt->bind_param("sisssdssss", $tracking_number, $_SESSION['user_id'], $receiver_city, $service_type, $weight, $declared_value, $sender_name, $sender_email, $sender_phone, $address);
            $shipment_stmt->execute();
            $shipment_id = $conn->insert_id;
            
            // Insert receiver details
            $receiver_stmt = $conn->prepare("INSERT INTO shipment_receivers (shipment_id, receiver_name, receiver_email, receiver_phone, receiver_address, receiver_city, receiver_postal_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $receiver_stmt->bind_param("issssss", $shipment_id, $receiver_name, $receiver_email, $receiver_phone, $receiver_address, $receiver_city, $receiver_postal_code);
            $receiver_stmt->execute();
            
            // Insert pickup request
            $pickup_stmt = $conn->prepare("INSERT INTO package_pickup (pickup_tracking_number, user_id, shipment_id, address, city, postal_code, phone, latitude, longitude, pickup_date, pickup_time, special_instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $pickup_stmt->bind_param("siissssssdss", $tracking_number, $_SESSION['user_id'], $shipment_id, $address, $city, $postal_code, $phone, $latitude, $longitude, $pickup_date, $pickup_time, $instructions);
            $pickup_stmt->execute();
            
            // Add to user history
            $history_stmt = $conn->prepare("INSERT INTO user_history (user_id, activity_type, activity_description) VALUES (?, ?, ?)");
            $activity_type = "booking_created";
            $activity_description = "Created booking with tracking number: $tracking_number";
            $history_stmt->bind_param("iss", $_SESSION['user_id'], $activity_type, $activity_description);
            $history_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $success_message = "Booking request submitted successfully! Your tracking number is: <strong>$tracking_number</strong><br>Estimated pickup date: " . date('F j, Y', strtotime($pickup_date));
            
            // Clear form data
            $_POST = [];
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error_message = "Error submitting booking request: " . $e->getMessage();
        }
    }
}

// Fetch user's pickup requests
$pickups_sql = "SELECT * FROM package_pickup WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($pickups_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pickups_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Pickup - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Package Pickup</h1>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="pickup-container">
                <div class="pickup-intro">
                    <p>Request a pickup for your package. Our courier will collect your item from your specified location.</p>
                </div>
                
                <div class="pickup-form-container">
                    <div class="pickup-card">
                        <h3>Book Package Pickup</h3>
                        <form method="POST" id="booking-form">
                            <!-- Sender Information -->
                            <div class="form-section">
                                <h4>Sender Information</h4>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="sender_name">Full Name *</label>
                                        <input type="text" id="sender_name" name="sender_name" 
                                               value="<?php echo htmlspecialchars($_POST['sender_name'] ?? ''); ?>"
                                               placeholder="Enter sender's full name" required>
                                        <?php if (isset($form_errors['sender_name'])): ?>
                                            <span class="error-text"><?php echo $form_errors['sender_name']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="sender_email">Email *</label>
                                        <input type="email" id="sender_email" name="sender_email" 
                                               value="<?php echo htmlspecialchars($_POST['sender_email'] ?? ''); ?>"
                                               placeholder="Enter sender's email" required>
                                        <?php if (isset($form_errors['sender_email'])): ?>
                                            <span class="error-text"><?php echo $form_errors['sender_email']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="sender_phone">Phone Number *</label>
                                        <input type="tel" id="sender_phone" name="sender_phone" 
                                               value="<?php echo htmlspecialchars($_POST['sender_phone'] ?? ''); ?>"
                                               placeholder="Enter sender's phone number" required>
                                        <?php if (isset($form_errors['sender_phone'])): ?>
                                            <span class="error-text"><?php echo $form_errors['sender_phone']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Receiver Information -->
                            <div class="form-section">
                                <h4>Receiver Information</h4>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="receiver_name">Full Name *</label>
                                        <input type="text" id="receiver_name" name="receiver_name" 
                                               value="<?php echo htmlspecialchars($_POST['receiver_name'] ?? ''); ?>"
                                               placeholder="Enter receiver's full name" required>
                                        <?php if (isset($form_errors['receiver_name'])): ?>
                                            <span class="error-text"><?php echo $form_errors['receiver_name']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="receiver_email">Email *</label>
                                        <input type="email" id="receiver_email" name="receiver_email" 
                                               value="<?php echo htmlspecialchars($_POST['receiver_email'] ?? ''); ?>"
                                               placeholder="Enter receiver's email" required>
                                        <?php if (isset($form_errors['receiver_email'])): ?>
                                            <span class="error-text"><?php echo $form_errors['receiver_email']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="receiver_phone">Phone Number *</label>
                                        <input type="tel" id="receiver_phone" name="receiver_phone" 
                                               value="<?php echo htmlspecialchars($_POST['receiver_phone'] ?? ''); ?>"
                                               placeholder="Enter receiver's phone number" required>
                                        <?php if (isset($form_errors['receiver_phone'])): ?>
                                            <span class="error-text"><?php echo $form_errors['receiver_phone']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="receiver_address">Full Address *</label>
                                    <textarea id="receiver_address" name="receiver_address" 
                                              placeholder="Enter complete delivery address" required><?php echo htmlspecialchars($_POST['receiver_address'] ?? ''); ?></textarea>
                                    <?php if (isset($form_errors['receiver_address'])): ?>
                                        <span class="error-text"><?php echo $form_errors['receiver_address']; ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="receiver_city">City *</label>
                                        <input type="text" id="receiver_city" name="receiver_city" 
                                               value="<?php echo htmlspecialchars($_POST['receiver_city'] ?? ''); ?>"
                                               placeholder="Enter delivery city" required>
                                        <?php if (isset($form_errors['receiver_city'])): ?>
                                            <span class="error-text"><?php echo $form_errors['receiver_city']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="receiver_postal_code">Postal Code *</label>
                                        <input type="text" id="receiver_postal_code" name="receiver_postal_code" 
                                               value="<?php echo htmlspecialchars($_POST['receiver_postal_code'] ?? ''); ?>"
                                               placeholder="Enter postal code" required>
                                        <?php if (isset($form_errors['receiver_postal_code'])): ?>
                                            <span class="error-text"><?php echo $form_errors['receiver_postal_code']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Parcel Details -->
                            <div class="form-section">
                                <h4>Parcel Details</h4>
                                <div class="form-group full-width">
                                    <label for="parcel_description">Parcel Description *</label>
                                    <textarea id="parcel_description" name="parcel_description" 
                                              placeholder="Describe the contents of your package" required><?php echo htmlspecialchars($_POST['parcel_description'] ?? ''); ?></textarea>
                                    <?php if (isset($form_errors['parcel_description'])): ?>
                                        <span class="error-text"><?php echo $form_errors['parcel_description']; ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="weight">Weight (kg) *</label>
                                        <input type="number" id="weight" name="weight" min="0.1" step="0.1"
                                               value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>"
                                               placeholder="Enter weight in kg" required>
                                        <?php if (isset($form_errors['weight'])): ?>
                                            <span class="error-text"><?php echo $form_errors['weight']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="declared_value">Declared Value (₱)</label>
                                        <input type="number" id="declared_value" name="declared_value" min="0" step="0.01"
                                               value="<?php echo htmlspecialchars($_POST['declared_value'] ?? ''); ?>"
                                               placeholder="Enter declared value">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="service_type">Service Type</label>
                                    <select id="service_type" name="service_type">
                                        <option value="Standard" <?php echo (($_POST['service_type'] ?? '') === 'Standard') ? 'selected' : ''; ?>>Standard (3-5 days)</option>
                                        <option value="Express" <?php echo (($_POST['service_type'] ?? '') === 'Express') ? 'selected' : ''; ?>>Express (1-2 days)</option>
                                        <option value="Overnight" <?php echo (($_POST['service_type'] ?? '') === 'Overnight') ? 'selected' : ''; ?>>Overnight (Same day)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Pickup Information -->
                            <div class="form-section">
                                <h4>Pickup Information</h4>
                                <div class="form-group full-width">
                                    <label for="address">Pickup Address *</label>
                                    <textarea id="address" name="address" 
                                              placeholder="Enter complete pickup address" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                    <?php if (isset($form_errors['address'])): ?>
                                        <span class="error-text"><?php echo $form_errors['address']; ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city">City *</label>
                                        <input type="text" id="city" name="city" 
                                               value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>"
                                               placeholder="Enter pickup city" required>
                                        <?php if (isset($form_errors['city'])): ?>
                                            <span class="error-text"><?php echo $form_errors['city']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="postal_code">Postal Code *</label>
                                        <input type="text" id="postal_code" name="postal_code" 
                                               value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>"
                                               placeholder="Enter pickup postal code" required>
                                        <?php if (isset($form_errors['postal_code'])): ?>
                                            <span class="error-text"><?php echo $form_errors['postal_code']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Pickup Contact Phone *</label>
                                        <input type="tel" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                               placeholder="Enter pickup contact number" required>
                                        <?php if (isset($form_errors['phone'])): ?>
                                            <span class="error-text"><?php echo $form_errors['phone']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="pickup_date">Pickup Date *</label>
                                        <input type="date" id="pickup_date" name="pickup_date" 
                                               value="<?php echo htmlspecialchars($_POST['pickup_date'] ?? ''); ?>"
                                               required>
                                        <?php if (isset($form_errors['pickup_date'])): ?>
                                            <span class="error-text"><?php echo $form_errors['pickup_date']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="pickup_time">Preferred Time *</label>
                                        <select id="pickup_time" name="pickup_time" required>
                                            <option value="">Select Time</option>
                                            <option value="09:00:00" <?php echo (($_POST['pickup_time'] ?? '') === '09:00:00') ? 'selected' : ''; ?>>9:00 AM</option>
                                            <option value="10:00:00" <?php echo (($_POST['pickup_time'] ?? '') === '10:00:00') ? 'selected' : ''; ?>>10:00 AM</option>
                                            <option value="11:00:00" <?php echo (($_POST['pickup_time'] ?? '') === '11:00:00') ? 'selected' : ''; ?>>11:00 AM</option>
                                            <option value="13:00:00" <?php echo (($_POST['pickup_time'] ?? '') === '13:00:00') ? 'selected' : ''; ?>>1:00 PM</option>
                                            <option value="14:00:00" <?php echo (($_POST['pickup_time'] ?? '') === '14:00:00') ? 'selected' : ''; ?>>2:00 PM</option>
                                            <option value="15:00:00" <?php echo (($_POST['pickup_time'] ?? '') === '15:00:00') ? 'selected' : ''; ?>>3:00 PM</option>
                                            <option value="16:00:00" <?php echo (($_POST['pickup_time'] ?? '') === '16:00:00') ? 'selected' : ''; ?>>4:00 PM</option>
                                            <option value="17:00:00" <?php echo (($_POST['pickup_time'] ?? '') === '17:00:00') ? 'selected' : ''; ?>>5:00 PM</option>
                                        </select>
                                        <?php if (isset($form_errors['pickup_time'])): ?>
                                            <span class="error-text"><?php echo $form_errors['pickup_time']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="special_instructions">Special Instructions</label>
                                <textarea id="special_instructions" name="special_instructions" 
                                          placeholder="Any special instructions for the courier..."><?php echo htmlspecialchars($_POST['special_instructions'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group full-width">
                                <label>Location</label>
                                <div class="location-controls">
                                    <button type="button" class="btn btn-secondary" onclick="getLocation()">Get Current Location</button>
                                    <span id="location-status" class="location-status"></span>
                                </div>
                                <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($_POST['latitude'] ?? ''); ?>">
                                <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($_POST['longitude'] ?? ''); ?>">
                                <div id="map-container" style="display:none; margin-top: 10px;">
                                    <div id="map" style="height: 200px; border: 1px solid #ddd; border-radius: 5px;"></div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Booking Request</button>
                        </form>
                    </div>
                </div>
                
                <div class="pickup-history">
                    <h3>Your Pickup Requests</h3>
                    <?php if ($pickups_result->num_rows > 0): ?>
                        <table class="pickup-table">
                            <thead>
                                <tr>
                                    <th>Tracking Number</th>
                                    <th>Address</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($pickup = $pickups_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $pickup['pickup_tracking_number']; ?></strong></td>
                                    <td><?php echo substr($pickup['address'], 0, 30) . '...'; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($pickup['pickup_date'])); ?> at <?php echo date('g:i A', strtotime($pickup['pickup_time'])); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $pickup['status'])); ?>"><?php echo $pickup['status']; ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="../tracking/track.php?tracking=<?php echo $pickup['pickup_tracking_number']; ?>" class="btn btn-sm btn-primary">Track</a>
                                            <?php if ($pickup['latitude'] && $pickup['longitude']): ?>
                                                <a href="https://www.google.com/maps?q=<?php echo $pickup['latitude']; ?>,<?php echo $pickup['longitude']; ?>" target="_blank" class="btn btn-sm btn-secondary">View Location</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No pickup requests found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        // Set minimum date to today
        document.getElementById('pickup_date').min = new Date().toISOString().split('T')[0];
        
        // Initialize map
        let map;
        let marker;
        
        function initMap(lat, lng) {
            const location = [lat, lng];
            
            map = L.map('map').setView(location, 15);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            marker = L.marker(location, {
                title: 'Your Location'
            }).addTo(map);
            
            marker.bindPopup('<div style="padding: 10px;"><strong>Your Pickup Location</strong></div>');
        }
            const statusElement = document.getElementById('location-status');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const mapContainer = document.getElementById('map-container');
            
            statusElement.textContent = 'Getting location...';
            statusElement.className = 'location-status loading';
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        latInput.value = lat;
                        lngInput.value = lng;
                        
                        statusElement.textContent = `Location captured: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        statusElement.className = 'location-status success';
                        
                        // Show map
                        mapContainer.style.display = 'block';
                        initMap(lat, lng);
                    },
                    function(error) {
                        statusElement.textContent = 'Unable to get location: ' + error.message;
                        statusElement.className = 'location-status error';
                    }
                );
            } else {
                statusElement.textContent = 'Geolocation is not supported by this browser.';
                statusElement.className = 'location-status error';
            }
        }
        
        function initMap(lat, lng) {
            // Simple map display using Google Static Maps API
            const mapElement = document.getElementById('map');
            const apiKey = 'YOUR_GOOGLE_MAPS_API_KEY'; // Replace with actual API key
            const mapUrl = `https://maps.googleapis.com/maps/api/staticmap?center=${lat},${lng}&zoom=15&size=400x200&markers=color:red%7C${lat},${lng}&key=${apiKey}`;
            
            mapElement.innerHTML = `<img src="${mapUrl}" alt="Location Map" style="width:100%; height:100%; border-radius:5px;">`;
            mapElement.innerHTML += `<p style="text-align:center; margin:5px 0; font-size:12px;">Location: ${lat.toFixed(6)}, ${lng.toFixed(6)}</p>`;
        }
    </script>
</body>
</html>