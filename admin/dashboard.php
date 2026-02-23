<?php
session_start();
include '../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];

// Get dashboard statistics
// Total Shipments
$total_shipments_sql = "SELECT COUNT(*) as total FROM shipments";
$total_shipments_result = $conn->query($total_shipments_sql);
$total_shipments = $total_shipments_result->fetch_assoc()['total'];

// Pending Pickup
$pending_pickup_sql = "SELECT COUNT(*) as total FROM shipments WHERE status = 'Pick Up'";
$pending_pickup_result = $conn->query($pending_pickup_sql);
$pending_pickup = $pending_pickup_result->fetch_assoc()['total'];

// In Transit
$in_transit_sql = "SELECT COUNT(*) as total FROM shipments WHERE status = 'In Transit'";
$in_transit_result = $conn->query($in_transit_sql);
$in_transit = $in_transit_result->fetch_assoc()['total'];

// Delivered
$delivered_sql = "SELECT COUNT(*) as total FROM shipments WHERE status = 'Delivered'";
$delivered_result = $conn->query($delivered_sql);
$delivered = $delivered_result->fetch_assoc()['total'];

// Registered Users
$registered_users_sql = "SELECT COUNT(*) as total FROM users";
$registered_users_result = $conn->query($registered_users_sql);
$registered_users = $registered_users_result->fetch_assoc()['total'];

// Recent Shipments (last 5)
$recent_shipments_sql = "SELECT s.*, u.username as user_name FROM shipments s 
                        LEFT JOIN users u ON s.user_id = u.id 
                        ORDER BY s.created_date DESC LIMIT 5";
$recent_shipments_result = $conn->query($recent_shipments_sql);

// Recent Users (last 5)
$recent_users_sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users_result = $conn->query($recent_users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .admin-header {
            background: #dc2626;
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .admin-logo h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        
        .admin-stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .admin-stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 16px;
        }
        
        .admin-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        
        .admin-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .admin-section-header h2 {
            margin: 0;
            color: #1f2937;
        }
        
        .admin-btn {
            background: #dc2626;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .admin-btn:hover {
            background: #b91c1c;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .admin-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        .admin-table tr:hover {
            background: #f9fafb;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pickup {
            background: #fef3c7;
            color: #d97706;
        }
        
        .status-transit {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .status-delivered {
            background: #d1fae5;
            color: #059669;
        }
        
        .admin-quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }
        
        .admin-action-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            cursor: pointer;
        }
        
        .admin-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .action-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }
        
        .action-label {
            font-weight: 600;
            color: #374151;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-logo">
                <h1>🔧 ADMIN DASHBOARD</h1>
            </div>
            <div class="admin-user-info">
                <span>Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
                <a href="logout.php" class="admin-btn">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="dashboard-container" style="max-width: 1200px; margin: 0 auto; padding: 30px 20px;">
        
        <!-- Dashboard Statistics -->
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $total_shipments; ?></div>
                <div class="stat-label">Total Shipments</div>
            </div>
            
            <div class="admin-stat-card">
                <div class="stat-icon">📥</div>
                <div class="stat-number"><?php echo $pending_pickup; ?></div>
                <div class="stat-label">Pending Pickup</div>
            </div>
            
            <div class="admin-stat-card">
                <div class="stat-icon">🚚</div>
                <div class="stat-number"><?php echo $in_transit; ?></div>
                <div class="stat-label">In Transit</div>
            </div>
            
            <div class="admin-stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $delivered; ?></div>
                <div class="stat-label">Delivered</div>
            </div>
            
            <div class="admin-stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $registered_users; ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h2>Quick Actions</h2>
            </div>
            <div class="admin-quick-actions">
                <a href="bookings.php" class="admin-action-card">
                    <div class="action-icon">📋</div>
                    <div class="action-label">Manage Bookings</div>
                </a>
                
                <a href="shipments.php" class="admin-action-card">
                    <div class="action-icon">📦</div>
                    <div class="action-label">Update Shipments</div>
                </a>
                
                <a href="users.php" class="admin-action-card">
                    <div class="action-icon">👤</div>
                    <div class="action-label">Manage Users</div>
                </a>
                
                <a href="branches.php" class="admin-action-card">
                    <div class="action-icon">🏢</div>
                    <div class="action-label">Branch Management</div>
                </a>
                
                <a href="rates.php" class="admin-action-card">
                    <div class="action-icon">💰</div>
                    <div class="action-label">Rate Management</div>
                </a>
                
                <a href="reports.php" class="admin-action-card">
                    <div class="action-icon">📊</div>
                    <div class="action-label">Reports</div>
                </a>
            </div>
        </div>
        
        <!-- Recent Shipments -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h2>Recent Shipments</h2>
                <a href="shipments.php" class="admin-btn">View All</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Destination</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($shipment = $recent_shipments_result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($shipment['tracking_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($shipment['destination']); ?></td>
                        <td><?php echo htmlspecialchars($shipment['user_name'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php 
                                echo strtolower(str_replace(' ', '-', $shipment['status'])); 
                            ?>">
                                <?php echo htmlspecialchars($shipment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($shipment['created_date'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Recent Users -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h2>New Registered Users</h2>
                <a href="users.php" class="admin-btn">View All</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $recent_users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>