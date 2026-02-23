<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

$selected_tracking = isset($_GET['tracking']) ? $_GET['tracking'] : '';

// Get all shipments for the list
$shipments_sql = "SELECT tracking_number, destination, status FROM shipments ORDER BY created_date DESC";
$shipments_result = $conn->query($shipments_sql);

// Get selected shipment details
$shipment_details = null;
if ($selected_tracking) {
    $details_sql = "SELECT * FROM shipments WHERE tracking_number = ?";
    $stmt = $conn->prepare($details_sql);
    $stmt->bind_param("s", $selected_tracking);
    $stmt->execute();
    $details_result = $stmt->get_result();
    $shipment_details = $details_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shipments - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>My Shipments</h1>
            </div>
            
            <div class="two-column-layout">
                <!-- Left Column - Shipment List -->
                <div class="shipment-list-column">
                    <h2>Your Shipments</h2>
                    <div class="shipment-list">
                        <?php while($row = $shipments_result->fetch_assoc()): ?>
                        <div class="shipment-item <?php echo ($selected_tracking == $row['tracking_number']) ? 'active' : ''; ?>" 
                             onclick="selectShipment('<?php echo $row['tracking_number']; ?>')">
                            <div class="shipment-icon">📦</div>
                            <div class="shipment-info">
                                <div class="tracking-number"><?php echo $row['tracking_number']; ?></div>
                                <div class="destination"><?php echo $row['destination']; ?></div>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- Right Column - Shipment Details -->
                <div class="shipment-details-column">
                    <?php if ($shipment_details): ?>
                        <div class="shipment-details">
                            <div class="details-header">
                                <h2>Shipment Details</h2>
                                <div class="tracking-display">
                                    Tracking Number: <strong><?php echo $shipment_details['tracking_number']; ?></strong>
                                </div>
                            </div>
                            
                            <!-- Delivery Timeline -->
                            <div class="delivery-timeline">
                                <h3>Delivery Progress</h3>
                                <div class="timeline">
                                    <div class="timeline-step <?php echo in_array($shipment_details['status'], ['Pick Up', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                        <div class="step-circle">1</div>
                                        <div class="step-content">
                                            <h4>Processed and Approved</h4>
                                            <p>Package has been received and processed</p>
                                            <?php if (in_array($shipment_details['status'], ['Pick Up', 'In Transit', 'Delivered'])): ?>
                                                <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($shipment_details['created_date'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-step <?php echo in_array($shipment_details['status'], ['In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                        <div class="step-circle">2</div>
                                        <div class="step-content">
                                            <h4>In Transit</h4>
                                            <p>Package is on its way to destination</p>
                                            <?php if (in_array($shipment_details['status'], ['In Transit', 'Delivered'])): ?>
                                                <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($shipment_details['created_date']) + 86400); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-step <?php echo $shipment_details['status'] == 'Delivered' ? 'completed' : ''; ?>">
                                        <div class="step-circle">3</div>
                                        <div class="step-content">
                                            <h4>Out for Delivery</h4>
                                            <p>Package is out for final delivery</p>
                                            <?php if ($shipment_details['status'] == 'Delivered'): ?>
                                                <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($shipment_details['updated_date']) - 43200); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-step <?php echo $shipment_details['status'] == 'Delivered' ? 'completed' : ''; ?>">
                                        <div class="step-circle">4</div>
                                        <div class="step-content">
                                            <h4>Delivered</h4>
                                            <p>Package has been successfully delivered</p>
                                            <?php if ($shipment_details['status'] == 'Delivered'): ?>
                                                <span class="step-date"><?php echo date('M j, Y g:i A', strtotime($shipment_details['updated_date'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Shipment Information -->
                            <div class="shipment-info-section">
                                <h3>Shipment Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Destination:</label>
                                        <span><?php echo $shipment_details['destination']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Status:</label>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $shipment_details['status'])); ?>">
                                            <?php echo $shipment_details['status']; ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <label>Created Date:</label>
                                        <span><?php echo date('F j, Y', strtotime($shipment_details['created_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Last Updated:</label>
                                        <span><?php echo date('F j, Y g:i A', strtotime($shipment_details['updated_date'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-selection">
                            <div class="placeholder-icon">📦</div>
                            <h3>Select a shipment</h3>
                            <p>Choose a shipment from the list to view detailed tracking information</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        function selectShipment(trackingNumber) {
            window.location.href = `my-shipments.php?tracking=${trackingNumber}`;
        }
    </script>
</body>
</html>