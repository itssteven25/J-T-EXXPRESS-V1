<?php
session_start();
include '../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $booking_id = $_POST['booking_id'] ?? 0;
    
    switch ($action) {
        case 'approve':
            $status = 'Scheduled';
            $sql = "UPDATE package_pickup SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $booking_id);
            if ($stmt->execute()) {
                // Log admin action
                $log_sql = "INSERT INTO admin_logs (admin_id, action, description) VALUES (?, ?, ?)";
                $log_stmt = $conn->prepare($log_sql);
                $action_desc = "approve_booking";
                $description = "Approved booking ID: $booking_id";
                $log_stmt->bind_param("iss", $_SESSION['admin_id'], $action_desc, $description);
                $log_stmt->execute();
                
                $success = "Booking approved successfully!";
            }
            break;
            
        case 'assign_rider':
            $rider_id = $_POST['rider_id'] ?? 0;
            $sql = "UPDATE package_pickup SET assigned_courier_id = ?, status = 'Scheduled', updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $rider_id, $booking_id);
            if ($stmt->execute()) {
                $success = "Rider assigned successfully!";
            }
            break;
            
        case 'cancel':
            $status = 'Cancelled';
            $sql = "UPDATE package_pickup SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $booking_id);
            if ($stmt->execute()) {
                $success = "Booking cancelled successfully!";
            }
            break;
    }
}

// Get all bookings
$bookings_sql = "SELECT pp.*, u.username, u.email 
                 FROM package_pickup pp 
                 LEFT JOIN users u ON pp.user_id = u.id 
                 ORDER BY pp.created_at DESC";
$bookings_result = $conn->query($bookings_sql);

// Get available riders/couriers
$riders_sql = "SELECT id, username FROM users WHERE (role = 'courier' OR role = 'admin') AND status = 'active'";
$riders_result = $conn->query($riders_sql);
$riders = [];
while ($rider = $riders_result->fetch_assoc()) {
    $riders[] = $rider;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - J&T Express Admin</title>
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
        
        .admin-content {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
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
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-scheduled { background: #dbeafe; color: #2563eb; }
        .status-in-transit { background: #ddd6fe; color: #7c3aed; }
        .status-completed { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        
        .admin-btn {
            background: #dc2626;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin: 2px;
        }
        
        .admin-btn:hover { background: #b91c1c; }
        .admin-btn-secondary { background: #6b7280; }
        .admin-btn-secondary:hover { background: #4b5563; }
        .admin-btn-success { background: #10b981; }
        .admin-btn-success:hover { background: #059669; }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover { color: #000; }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-logo">
                <h1>📋 BOOKING MANAGEMENT</h1>
            </div>
            <div class="admin-user-info">
                <span>Admin: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="dashboard.php" class="admin-btn" style="margin-left: 15px;">Dashboard</a>
                <a href="logout.php" class="admin-btn">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="admin-content">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <div class="admin-section-header">
                <h2>All Package Pickups</h2>
                <div>
                    <a href="dashboard.php" class="admin-btn admin-btn-secondary">Back to Dashboard</a>
                </div>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>User</th>
                        <th>Address</th>
                        <th>Pickup Date/Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($booking['pickup_tracking_number']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($booking['username'] ?? 'N/A'); ?><br>
                            <small><?php echo htmlspecialchars($booking['email'] ?? ''); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars(substr($booking['address'], 0, 50)) . '...'; ?></td>
                        <td>
                            <?php echo date('M j, Y', strtotime($booking['pickup_date'])); ?><br>
                            <small><?php echo date('g:i A', strtotime($booking['pickup_time'])); ?></small>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                <?php echo htmlspecialchars($booking['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($booking['status'] == 'Pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="admin-btn admin-btn-success">Approve</button>
                                </form>
                                
                                <button class="admin-btn" onclick="openAssignModal(<?php echo $booking['id']; ?>)">Assign Rider</button>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] != 'Cancelled' && $booking['status'] != 'Completed'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="admin-btn admin-btn-secondary" 
                                            onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</button>
                                </form>
                            <?php endif; ?>
                            
                            <button class="admin-btn admin-btn-secondary" 
                                    onclick="viewDetails(<?php echo $booking['id']; ?>)">View Details</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Assign Rider Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAssignModal()">&times;</span>
            <h2>Assign Rider</h2>
            <form method="POST" id="assignForm">
                <input type="hidden" name="action" value="assign_rider">
                <input type="hidden" name="booking_id" id="assignBookingId">
                
                <div class="form-group">
                    <label for="rider_id">Select Rider:</label>
                    <select name="rider_id" id="rider_id" required>
                        <option value="">Choose a rider</option>
                        <?php foreach ($riders as $rider): ?>
                            <option value="<?php echo $rider['id']; ?>">
                                <?php echo htmlspecialchars($rider['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="admin-btn">Assign Rider</button>
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeAssignModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function openAssignModal(bookingId) {
            document.getElementById('assignBookingId').value = bookingId;
            document.getElementById('assignModal').style.display = 'block';
        }
        
        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }
        
        function viewDetails(bookingId) {
            // In a real implementation, this would open a modal with full details
            alert('Viewing details for booking ID: ' + bookingId + '\n(In a full implementation, this would show detailed booking information)');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('assignModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>