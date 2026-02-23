<?php
session_start();
include '../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $shipment_id = $_POST['shipment_id'] ?? 0;
    $new_status = $_POST['new_status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate status
    $valid_statuses = ['Pick Up', 'In Transit', 'At Sorting Hub', 'Out for Delivery', 'Delivered'];
    if (in_array($new_status, $valid_statuses)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update shipment status
            $sql = "UPDATE shipments SET status = ?, updated_date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_status, $shipment_id);
            $stmt->execute();
            
            // Add to shipment history
            $history_sql = "INSERT INTO shipment_history (shipment_id, status, notes, updated_by) VALUES (?, ?, ?, ?)";
            $history_stmt = $conn->prepare($history_sql);
            $updated_by = $_SESSION['admin_username'];
            $history_stmt->bind_param("isss", $shipment_id, $new_status, $notes, $updated_by);
            $history_stmt->execute();
            
            // If delivered, set delivery time
            if ($new_status == 'Delivered') {
                $delivery_sql = "UPDATE shipments SET delivery_time = NOW() WHERE id = ?";
                $delivery_stmt = $conn->prepare($delivery_sql);
                $delivery_stmt->bind_param("i", $shipment_id);
                $delivery_stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Log admin action
            $log_sql = "INSERT INTO admin_logs (admin_id, action, description) VALUES (?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $action_type = "update_shipment_status";
            $description = "Updated shipment ID: $shipment_id to status: $new_status";
            $log_stmt->bind_param("iss", $_SESSION['admin_id'], $action_type, $description);
            $log_stmt->execute();
            
            $success = "Shipment status updated successfully to: $new_status";
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error = "Error updating shipment status: " . $e->getMessage();
        }
    } else {
        $error = "Invalid status selected";
    }
}

// Get all shipments
$shipments_sql = "SELECT s.*, u.username as user_name, sr.receiver_name 
                 FROM shipments s 
                 LEFT JOIN users u ON s.user_id = u.id 
                 LEFT JOIN shipment_receivers sr ON s.id = sr.shipment_id 
                 ORDER BY s.updated_date DESC";
$shipments_result = $conn->query($shipments_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Status Update - J&T Express Admin</title>
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
        
        .status-pick-up { background: #fef3c7; color: #d97706; }
        .status-in-transit { background: #dbeafe; color: #2563eb; }
        .status-at-sorting-hub { background: #ddd6fe; color: #7c3aed; }
        .status-out-for-delivery { background: #bfdbfe; color: #1d4ed8; }
        .status-delivered { background: #d1fae5; color: #059669; }
        
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
        .admin-btn-warning { background: #f59e0b; }
        .admin-btn-warning:hover { background: #d97706; }
        
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
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 80%;
            max-width: 600px;
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
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .shipment-details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .shipment-details h3 {
            margin-top: 0;
            color: #1f2937;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            width: 150px;
            color: #6b7280;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        
        .status-progress {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            position: relative;
        }
        
        .status-step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        
        .status-step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: 1;
        }
        
        .status-step.active:after {
            background: #10b981;
        }
        
        .status-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e5e7eb;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        
        .status-step.active .status-circle {
            background: #10b981;
        }
        
        .status-step.completed .status-circle {
            background: #10b981;
        }
        
        .status-step.completed .status-circle:after {
            content: '✓';
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-logo">
                <h1>🚚 SHIPMENT STATUS UPDATE</h1>
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
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <div class="admin-section-header">
                <h2>All Shipments</h2>
                <div>
                    <a href="dashboard.php" class="admin-btn admin-btn-secondary">Back to Dashboard</a>
                </div>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Destination</th>
                        <th>User</th>
                        <th>Receiver</th>
                        <th>Current Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($shipment = $shipments_result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($shipment['tracking_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($shipment['destination']); ?></td>
                        <td><?php echo htmlspecialchars($shipment['user_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($shipment['receiver_name'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php 
                                echo strtolower(str_replace(' ', '-', $shipment['status'])); 
                            ?>">
                                <?php echo htmlspecialchars($shipment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($shipment['updated_date'])); ?></td>
                        <td>
                            <button class="admin-btn" onclick="viewShipment(<?php echo $shipment['id']; ?>)">View</button>
                            <button class="admin-btn admin-btn-warning" onclick="updateStatus(<?php echo $shipment['id']; ?>, '<?php echo $shipment['status']; ?>')">Update Status</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStatusModal()">&times;</span>
            <h2>Update Shipment Status</h2>
            
            <div class="shipment-details" id="shipmentDetails">
                <!-- Shipment details will be loaded here -->
            </div>
            
            <div class="status-progress" id="statusProgress">
                <!-- Status progress will be shown here -->
            </div>
            
            <form method="POST" id="statusForm">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="shipment_id" id="shipmentId">
                
                <div class="form-group">
                    <label for="new_status">New Status:</label>
                    <select name="new_status" id="new_status" required>
                        <option value="">Select new status</option>
                        <option value="Pick Up">📥 Pick Up</option>
                        <option value="In Transit">🚚 In Transit</option>
                        <option value="At Sorting Hub">🏢 At Sorting Hub</option>
                        <option value="Out for Delivery">📦 Out for Delivery</option>
                        <option value="Delivered">✅ Delivered</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes (Optional):</label>
                    <textarea name="notes" id="notes" placeholder="Add any notes about this status update..."></textarea>
                </div>
                
                <button type="submit" class="admin-btn">Update Status</button>
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeStatusModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function viewShipment(shipmentId) {
            // In a real implementation, this would show detailed shipment information
            alert('Viewing shipment ID: ' + shipmentId + '\n(In a full implementation, this would show detailed shipment information)');
        }
        
        function updateStatus(shipmentId, currentStatus) {
            document.getElementById('shipmentId').value = shipmentId;
            
            // Load shipment details (simplified for this example)
            document.getElementById('shipmentDetails').innerHTML = `
                <h3>Shipment #${shipmentId}</h3>
                <div class="detail-row">
                    <div class="detail-label">Current Status:</div>
                    <div>${currentStatus}</div>
                </div>
            `;
            
            // Show status progress
            showStatusProgress(currentStatus);
            
            document.getElementById('statusModal').style.display = 'block';
        }
        
        function showStatusProgress(currentStatus) {
            const statuses = ['Pick Up', 'In Transit', 'At Sorting Hub', 'Out for Delivery', 'Delivered'];
            const currentIndex = statuses.indexOf(currentStatus);
            
            let progressHtml = '';
            statuses.forEach((status, index) => {
                const isActive = index === currentIndex;
                const isCompleted = index < currentIndex;
                const statusClass = isCompleted ? 'completed' : (isActive ? 'active' : '');
                
                progressHtml += `
                    <div class="status-step ${statusClass}">
                        <div class="status-circle"></div>
                        <div style="font-size: 12px; color: #6b7280;">${status}</div>
                    </div>
                `;
            });
            
            document.getElementById('statusProgress').innerHTML = progressHtml;
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>