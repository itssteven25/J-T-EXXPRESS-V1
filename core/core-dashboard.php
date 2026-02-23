<?php
include 'core-header.php';
include 'core-sidebar.php';
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

// Get user profile info
$profile_sql = "SELECT up.first_name, up.last_name, up.phone, u.email FROM user_profiles up 
                JOIN users u ON up.user_id = u.id 
                WHERE up.user_id = ?";
$stmt = $conn->prepare($profile_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$profile_result = $stmt->get_result();
$profile = $profile_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Dashboard - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <!-- Welcome Banner -->
            <section class="banner-section">
                <div class="banner-card">
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                    <p>Manage your shipments and track deliveries efficiently</p>
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
            
            <div class="two-column-layout">
                <!-- Left Column - Quick Actions -->
                <div class="shipment-list-column">
                    <h2>Quick Actions</h2>
                    <div class="quick-actions-grid">
                        <div class="action-card" onclick="window.location.href='../tracking/track.php'">
                            <div class="action-icon">🔍</div>
                            <h3>Track Shipment</h3>
                            <p>Find your package by tracking number</p>
                        </div>
                        
                        <div class="action-card" onclick="window.location.href='../dashboard/shipments.php'">
                            <div class="action-icon">📋</div>
                            <h3>My Shipments</h3>
                            <p>View all your shipments</p>
                        </div>
                        
                        <div class="action-card" onclick="window.location.href='../account/my-account.php'">
                            <div class="action-icon">👤</div>
                            <h3>My Profile</h3>
                            <p>Manage your account settings</p>
                        </div>
                        
                        <div class="action-card" onclick="window.location.href='../support/support.php'">
                            <div class="action-icon">❓</div>
                            <h3>Get Help</h3>
                            <p>Contact support team</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Recent Activity -->
                <div class="shipment-details-column">
                    <div class="recent-activity">
                        <h3>Recent Shipments</h3>
                        <div class="recent-shipments-list">
                            <?php if ($recent_result->num_rows > 0): ?>
                                <?php while($row = $recent_result->fetch_assoc()): ?>
                                <div class="recent-item">
                                    <div class="item-header">
                                        <span class="package-icon">📦</span>
                                        <strong><?php echo $row['tracking_number']; ?></strong>
                                    </div>
                                    <div class="item-details">
                                        <p>Destination: <?php echo $row['destination']; ?></p>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                        <span class="item-date"><?php echo date('M j, Y', strtotime($row['created_date'])); ?></span>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="no-shipments">
                                    <p>No shipments found. Create your first shipment to get started.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="user-profile-summary">
                        <h3>Your Profile</h3>
                        <div class="profile-info">
                            <div class="profile-avatar">
                                <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                            </div>
                            <div class="profile-details">
                                <h4><?php echo htmlspecialchars($profile['first_name'] ?? $_SESSION['username']) . ' ' . htmlspecialchars($profile['last_name'] ?? ''); ?></h4>
                                <p><?php echo htmlspecialchars($profile['email'] ?? ''); ?></p>
                                <p><?php echo htmlspecialchars($profile['phone'] ?? 'No phone number'); ?></p>
                            </div>
                        </div>
                        <a href="../account/my-account.php" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>