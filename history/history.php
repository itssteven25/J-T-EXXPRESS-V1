<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Fetch user's activity history
$history_sql = "SELECT * FROM user_history WHERE user_id = ? ORDER BY timestamp DESC LIMIT 50";
$stmt = $conn->prepare($history_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$history_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Activity History</h1>
            </div>
            
            <div class="history-container">
                <div class="history-intro">
                    <p>Your complete activity history with J&T Express. Track your shipments, pickups, and account activities.</p>
                </div>
                
                <div class="history-filters">
                    <div class="filter-group">
                        <label for="activity-type">Activity Type:</label>
                        <select id="activity-type">
                            <option value="all">All Activities</option>
                            <option value="shipment">Shipments</option>
                            <option value="pickup">Pickups</option>
                            <option value="account">Account</option>
                            <option value="tracking">Tracking</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date-range">Date Range:</label>
                        <select id="date-range">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">Last Week</option>
                            <option value="month">Last Month</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary">Apply Filters</button>
                </div>
                
                <div class="history-list">
                    <h3>Recent Activities</h3>
                    <?php if ($history_result->num_rows > 0): ?>
                        <div class="history-timeline">
                            <?php while($activity = $history_result->fetch_assoc()): ?>
                            <div class="history-item">
                                <div class="history-icon">
                                    <?php 
                                    switch($activity['activity_type']) {
                                        case 'shipment_created': echo '📦'; break;
                                        case 'shipment_updated': echo '🔄'; break;
                                        case 'shipment_tracked': echo '🔍'; break;
                                        case 'pickup_scheduled': echo '🚚'; break;
                                        case 'account_login': echo '👤'; break;
                                        case 'profile_updated': echo '⚙️'; break;
                                        default: echo 'ℹ️'; break;
                                    }
                                    ?>
                                </div>
                                <div class="history-content">
                                    <h4><?php echo $activity['activity_type']; ?></h4>
                                    <p><?php echo $activity['activity_description']; ?></p>
                                    <span class="history-time"><?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?></span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>No Activity History</h3>
                            <p>Your activity history will appear here once you start using our services.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="history-summary">
                    <div class="summary-grid">
                        <div class="summary-card">
                            <h3>Total Shipments</h3>
                            <p>12</p>
                        </div>
                        <div class="summary-card">
                            <h3>Pickup Requests</h3>
                            <p>5</p>
                        </div>
                        <div class="summary-card">
                            <h3>Tracking Events</h3>
                            <p>34</p>
                        </div>
                        <div class="summary-card">
                            <h3>Account Logins</h3>
                            <p>28</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        // Add history filtering functionality
        document.getElementById('activity-type').addEventListener('change', function() {
            filterHistory();
        });
        
        document.getElementById('date-range').addEventListener('change', function() {
            filterHistory();
        });
        
        function filterHistory() {
            // In a real application, this would filter the history items
            const type = document.getElementById('activity-type').value;
            const dateRange = document.getElementById('date-range').value;
            
            console.log('Filtering by type:', type, 'and date range:', dateRange);
        }
    </script>
</body>
</html>