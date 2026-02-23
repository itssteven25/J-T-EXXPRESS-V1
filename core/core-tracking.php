<?php
include 'core-header.php';
include 'core-sidebar.php';
include '../includes/db.php';

$tracking_number = isset($_GET['tracking']) ? $_GET['tracking'] : '';
$shipment = null;
$error = '';

if ($tracking_number) {
    $sql = "SELECT * FROM shipments WHERE tracking_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tracking_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $shipment = $result->fetch_assoc();
    } else {
        $error = "Tracking number not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Tracking - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Track Your Shipment</h1>
            </div>
            
            <!-- Tracking Search -->
            <div class="tracking-search-section">
                <div class="search-card">
                    <h2>Enter Tracking Number</h2>
                    <form method="GET" class="tracking-form">
                        <div class="form-group">
                            <label for="tracking-input">Tracking Number:</label>
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
                <?php if ($shipment): ?>
                    <div class="tracking-results">
                        <div class="results-header">
                            <h2>Tracking Results</h2>
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
                                        <h4>Processed</h4>
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
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tracking-error">
                        <div class="error-icon">❌</div>
                        <h3>Tracking Number Not Found</h3>
                        <p>We couldn't find any shipment with tracking number: <strong><?php echo htmlspecialchars($tracking_number); ?></strong></p>
                        <p>Please check the tracking number and try again.</p>
                    </div>
                <?php endif; ?>
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
</body>
</html>