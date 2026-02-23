<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Get shipment statistics
$stats_sql = "SELECT 
    COUNT(*) as total_shipments,
    COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as delivered,
    COUNT(CASE WHEN status = 'In Transit' THEN 1 END) as in_transit,
    COUNT(CASE WHEN status = 'Pick Up' THEN 1 END) as pick_up
FROM shipments";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Get recent shipments
$recent_sql = "SELECT tracking_number, destination, status, created_date FROM shipments ORDER BY created_date DESC LIMIT 5";
$recent_result = $conn->query($recent_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <!-- Banner Section -->
            <section class="banner-section">
                <div class="banner-card">
                    <h2>Welcome to J&T Express Dashboard</h2>
                    <p>Track your shipments, manage deliveries, and access courier services efficiently</p>
                </div>
            </section>
            
            <!-- Stats Overview -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_shipments']; ?></h3>
                            <p>Total Shipments</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🚚</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['in_transit']; ?></h3>
                            <p>In Transit</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['delivered']; ?></h3>
                            <p>Delivered</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📍</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pick_up']; ?></h3>
                            <p>Pick Up</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Quick Access Section -->
            <section class="quick-access-section">
                <h2>Quick Access</h2>
                <div class="quick-access-grid">
                    <div class="quick-card">
                        <div class="card-icon">🔍</div>
                        <h3>Track & Trace</h3>
                        <p>Track your shipments in real-time</p>
                        <a href="../tracking/track.php" class="card-btn">Track Now</a>
                    </div>
                    <div class="quick-card">
                        <div class="card-icon">💰</div>
                        <h3>Shipping Rates</h3>
                        <p>Calculate shipping costs</p>
                        <button class="card-btn">View Rates</button>
                    </div>
                    <div class="quick-card">
                        <div class="card-icon">📍</div>
                        <h3>Nearby Drop Points</h3>
                        <p>Find the nearest drop-off locations</p>
                        <button class="card-btn">Find Locations</button>
                    </div>
                </div>
            </section>
            
            <!-- Latest Updates -->
            <section class="updates-section">
                <div class="section-header">
                    <h2>Latest Updates</h2>
                    <a href="#" class="see-all-link">See all</a>
                </div>
                <div class="updates-carousel">
                    <div class="update-card">
                        <div class="update-image"></div>
                        <div class="update-content">
                            <h4>Import Services Expansion</h4>
                            <p>New international shipping routes now available</p>
                        </div>
                    </div>
                    <div class="update-card">
                        <div class="update-image"></div>
                        <div class="update-content">
                            <h4>Major System Updates</h4>
                            <p>Enhanced tracking system and mobile app improvements</p>
                        </div>
                    </div>
                    <div class="update-card">
                        <div class="update-image"></div>
                        <div class="update-content">
                            <h4>Other Shipments</h4>
                            <p>Special handling services for fragile items</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Recent Shipments -->
            <section class="recent-shipments-section">
                <div class="section-header">
                    <h2>Recent Shipments</h2>
                    <a href="shipments.php" class="see-all-link">View All</a>
                </div>
                <div class="shipments-table-container">
                    <table class="shipments-table">
                        <thead>
                            <tr>
                                <th>Tracking Number</th>
                                <th>Destination</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $recent_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="package-icon">📦</div>
                                    <strong><?php echo $row['tracking_number']; ?></strong>
                                </td>
                                <td><?php echo $row['destination']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($row['created_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>