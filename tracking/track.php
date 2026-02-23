<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

$tracking_number = isset($_GET['tracking']) ? trim($_GET['tracking']) : '';
$shipment = null;
$pickup = null;
$tracking_type = '';
$error = '';
$sender_details = null;
$receiver_details = null;

// Validate tracking number format
if ($tracking_number && !preg_match('/^[A-Z0-9]{8,15}$/', $tracking_number)) {
    $error = "Invalid tracking number format. Tracking numbers should be 8-15 characters long and contain only letters and numbers.";
}

if ($tracking_number && !$error) {
    // First check if it's a shipment
    $sql = "SELECT s.*, u.email as sender_email, up.first_name as sender_first_name, up.last_name as sender_last_name 
            FROM shipments s 
            LEFT JOIN users u ON s.user_id = u.id 
            LEFT JOIN user_profiles up ON u.id = up.user_id 
            WHERE s.tracking_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tracking_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $shipment = $result->fetch_assoc();
        $tracking_type = 'shipment';
        
        // Get sender details
        if ($shipment['user_id']) {
            $sender_sql = "SELECT u.username, u.email, up.first_name, up.last_name, up.phone, up.address 
                          FROM users u 
                          LEFT JOIN user_profiles up ON u.id = up.user_id 
                          WHERE u.id = ?";
            $sender_stmt = $conn->prepare($sender_sql);
            $sender_stmt->bind_param("i", $shipment['user_id']);
            $sender_stmt->execute();
            $sender_result = $sender_stmt->get_result();
            $sender_details = $sender_result->fetch_assoc();
        }
        
        // Get receiver details (if stored)
        $receiver_sql = "SELECT * FROM shipment_receivers WHERE shipment_id = ?";
        $receiver_stmt = $conn->prepare($receiver_sql);
        $receiver_stmt->bind_param("i", $shipment['id']);
        $receiver_stmt->execute();
        $receiver_result = $receiver_stmt->get_result();
        if ($receiver_result->num_rows > 0) {
            $receiver_details = $receiver_result->fetch_assoc();
        }
    } else {
        // Check if it's a pickup request
        $sql = "SELECT pp.*, u.username, u.email, up.first_name, up.last_name 
                FROM package_pickup pp 
                LEFT JOIN users u ON pp.user_id = u.id 
                LEFT JOIN user_profiles up ON u.id = up.user_id 
                WHERE pp.pickup_tracking_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $pickup = $result->fetch_assoc();
            $tracking_type = 'pickup';
            $sender_details = [
                'name' => $pickup['first_name'] . ' ' . $pickup['last_name'],
                'email' => $pickup['email'],
                'phone' => $pickup['phone'],
                'address' => $pickup['address']
            ];
        } else {
            $error = "Tracking number not found. Please check the number and try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track & Trace - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Track & Trace</h1>
            </div>
            
            <!-- Tracking Search -->
            <div class="tracking-search-section">
                <div class="search-card">
                    <h2>Track Your Shipment</h2>
                    <form method="GET" class="tracking-form">
                        <div class="form-group">
                            <label for="tracking-input">Enter Tracking Number:</label>
                            <div class="input-group">
                                <input type="text" id="tracking-input" name="tracking" 
                                       value="<?php echo htmlspecialchars($tracking_number); ?>" 
                                       placeholder="JT123456789" required>
                                <button type="submit" class="btn btn-primary">Track</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tracking Results -->
            <?php if ($tracking_number): ?>
                <?php if ($tracking_type === 'shipment' && $shipment): ?>
                    <div class="tracking-results">
                        <div class="results-header">
                            <h2>Shipment Tracking Results</h2>
                            <div class="tracking-display">
                                Tracking Number: <strong><?php echo $shipment['tracking_number']; ?></strong>
                            </div>
                        </div>
                        
                        <!-- Status Overview -->
                        <div class="status-overview">
                            <div class="status-card">
                                <div class="status-icon">
                                    <?php 
                                    switch($shipment['status']) {
                                        case 'Pick Up': echo '📍'; break;
                                        case 'In Transit': echo '🚚'; break;
                                        case 'Delivered': echo '✅'; break;
                                    }
                                    ?>
                                </div>
                                <div class="status-info">
                                    <h3><?php echo $shipment['status']; ?></h3>
                                    <p>Current shipment status</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Delivery Timeline -->
                        <div class="delivery-timeline">
                            <h3>Delivery Progress</h3>
                            <div class="timeline">
                                <div class="timeline-step <?php echo in_array($shipment['status'], ['Pick Up', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                    <div class="step-circle">1</div>
                                    <div class="step-content">
                                        <h4>Processed and Approved</h4>
                                        <p>Package has been received and processed</p>
                                        <?php if (in_array($shipment['status'], ['Pick Up', 'In Transit', 'Delivered'])): ?>
                                            <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($shipment['created_date'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="timeline-step <?php echo in_array($shipment['status'], ['In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                    <div class="step-circle">2</div>
                                    <div class="step-content">
                                        <h4>In Transit</h4>
                                        <p>Package is on its way to destination</p>
                                        <?php if (in_array($shipment['status'], ['In Transit', 'Delivered'])): ?>
                                            <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($shipment['created_date']) + 86400); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="timeline-step <?php echo $shipment['status'] == 'Delivered' ? 'completed' : ''; ?>">
                                    <div class="step-circle">3</div>
                                    <div class="step-content">
                                        <h4>Out for Delivery</h4>
                                        <p>Package is out for final delivery</p>
                                        <?php if ($shipment['status'] == 'Delivered'): ?>
                                            <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($shipment['updated_date']) - 43200); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="timeline-step <?php echo $shipment['status'] == 'Delivered' ? 'completed' : ''; ?>">
                                    <div class="step-circle">4</div>
                                    <div class="step-content">
                                        <h4>Delivered</h4>
                                        <p>Package has been successfully delivered</p>
                                        <?php if ($shipment['status'] == 'Delivered'): ?>
                                            <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($shipment['updated_date'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Shipment Details -->
                        <div class="shipment-details-card">
                            <h3>Shipment Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Tracking Number:</label>
                                    <span><?php echo $shipment['tracking_number']; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Destination:</label>
                                    <span><?php echo $shipment['destination']; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Status:</label>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $shipment['status'])); ?>">
                                        <?php echo $shipment['status']; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Created Date:</label>
                                    <span><?php echo date('F j, Y g:i A', strtotime($shipment['created_date'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Last Updated:</label>
                                    <span><?php echo date('F j, Y g:i A', strtotime($shipment['updated_date'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Service Type:</label>
                                    <span><?php echo $shipment['service_type'] ?? 'Standard'; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Weight:</label>
                                    <span><?php echo $shipment['weight'] ?? 'N/A'; ?> kg</span>
                                </div>
                                <div class="info-item">
                                    <label>Declared Value:</label>
                                    <span>₱<?php echo number_format($shipment['declared_value'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sender and Receiver Details -->
                        <div class="party-details">
                            <div class="party-grid">
                                <div class="party-card">
                                    <h3>Sender Information</h3>
                                    <?php if ($sender_details): ?>
                                        <div class="party-info">
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($sender_details['first_name'] . ' ' . $sender_details['last_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($sender_details['email']); ?></p>
                                            <?php if (!empty($sender_details['phone'])): ?>
                                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($sender_details['phone']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($sender_details['address'])): ?>
                                                <p><strong>Address:</strong> <?php echo htmlspecialchars($sender_details['address']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <p>Sender information not available</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="party-card">
                                    <h3>Receiver Information</h3>
                                    <?php if ($receiver_details): ?>
                                        <div class="party-info">
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($receiver_details['receiver_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($receiver_details['receiver_email']); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($receiver_details['receiver_phone']); ?></p>
                                            <p><strong>Address:</strong> <?php echo htmlspecialchars($receiver_details['receiver_address']); ?></p>
                                        </div>
                                    <?php else: ?>
                                        <p>Receiver information not available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($tracking_type === 'pickup' && $pickup): ?>
                    <div class="tracking-results">
                        <div class="results-header">
                            <h2>Pickup Request Tracking</h2>
                            <div class="tracking-display">
                                Pickup Tracking Number: <strong><?php echo $pickup['pickup_tracking_number']; ?></strong>
                            </div>
                        </div>
                        
                        <!-- Status Overview -->
                        <div class="status-overview">
                            <div class="status-card">
                                <div class="status-icon">
                                    <?php 
                                    switch($pickup['status']) {
                                        case 'Pending': echo '⏳'; break;
                                        case 'Scheduled': echo '📅'; break;
                                        case 'In Transit': echo '🚚'; break;
                                        case 'Completed': echo '✅'; break;
                                        case 'Cancelled': echo '❌'; break;
                                    }
                                    ?>
                                </div>
                                <div class="status-info">
                                    <h3><?php echo $pickup['status']; ?></h3>
                                    <p>Current pickup request status</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pickup Timeline -->
                        <div class="delivery-timeline">
                            <h3>Pickup Progress</h3>
                            <div class="timeline">
                                <div class="timeline-step <?php echo in_array($pickup['status'], ['Scheduled', 'In Transit', 'Completed']) ? 'completed' : ''; ?>">
                                    <div class="step-circle">1</div>
                                    <div class="step-content">
                                        <h4>Request Submitted</h4>
                                        <p>Pickup request has been submitted</p>
                                        <?php if (in_array($pickup['status'], ['Scheduled', 'In Transit', 'Completed'])): ?>
                                            <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($pickup['created_at'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="timeline-step <?php echo in_array($pickup['status'], ['In Transit', 'Completed']) ? 'completed' : ''; ?>">
                                    <div class="step-circle">2</div>
                                    <div class="step-content">
                                        <h4>Courier Assigned</h4>
                                        <p>Courier has been assigned to your pickup</p>
                                        <?php if (in_array($pickup['status'], ['In Transit', 'Completed']) && $pickup['assigned_courier_id']): ?>
                                            <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($pickup['updated_at'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="timeline-step <?php echo in_array($pickup['status'], ['In Transit', 'Completed']) ? 'completed' : ''; ?>">
                                    <div class="step-circle">3</div>
                                    <div class="step-content">
                                        <h4>En Route to Pickup</h4>
                                        <p>Courier is on the way to collect your package</p>
                                        <?php if ($pickup['status'] == 'In Transit'): ?>
                                            <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($pickup['updated_at'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="timeline-step <?php echo $pickup['status'] == 'Completed' ? 'completed' : ''; ?>">
                                    <div class="step-circle">4</div>
                                    <div class="step-content">
                                        <h4>Pickup Completed</h4>
                                        <p>Package has been successfully picked up</p>
                                        <?php if ($pickup['status'] == 'Completed' && $pickup['actual_pickup_time']): ?>
                                            <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($pickup['actual_pickup_time'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pickup Details -->
                        <div class="shipment-details-card">
                            <h3>Pickup Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Pickup Tracking Number:</label>
                                    <span><?php echo $pickup['pickup_tracking_number']; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Pickup Address:</label>
                                    <span><?php echo $pickup['address']; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>City:</label>
                                    <span><?php echo $pickup['city']; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Postal Code:</label>
                                    <span><?php echo $pickup['postal_code']; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Phone:</label>
                                    <span><?php echo $pickup['phone']; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Status:</label>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $pickup['status'])); ?>">
                                        <?php echo $pickup['status']; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Pickup Date:</label>
                                    <span><?php echo date('F j, Y', strtotime($pickup['pickup_date'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Pickup Time:</label>
                                    <span><?php echo date('g:i A', strtotime($pickup['pickup_time'])); ?></span>
                                </div>
                                <?php if ($pickup['special_instructions']): ?>
                                <div class="info-item full-width">
                                    <label>Special Instructions:</label>
                                    <span><?php echo $pickup['special_instructions']; ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($pickup['latitude'] && $pickup['longitude']): ?>
                                <div class="info-item full-width">
                                    <label>Location:</label>
                                    <span>
                                        <a href="https://www.google.com/maps?q=<?php echo $pickup['latitude']; ?>,<?php echo $pickup['longitude']; ?>" target="_blank">
                                            View on Google Maps
                                        </a>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tracking-error">
                        <div class="error-icon">❌</div>
                        <h3>Tracking Number Not Found</h3>
                        <p>We couldn't find any shipment or pickup request with tracking number: <strong><?php echo htmlspecialchars($tracking_number); ?></strong></p>
                        <p>Please check the tracking number and try again.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($shipment): ?>
            <script>
                // Load tracking history when page loads
                document.addEventListener('DOMContentLoaded', function() {
                    showTrackingHistory('<?php echo $shipment['tracking_number']; ?>');
                });
            </script>
            <?php endif; ?>
            
            <!-- Quick Tracking Tips -->
            <div class="tracking-tips">
                <h3>Tracking Tips</h3>
                <ul>
                    <li>Make sure you enter the complete tracking number</li>
                    <li>Tracking numbers are usually 9-12 characters long</li>
                    <li>Allow 24 hours for tracking information to update</li>
                    <li>Contact support if you have issues with your shipment</li>
                </ul>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/tracking.js"></script>
</body>
</html>