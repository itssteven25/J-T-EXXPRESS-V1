<?php
session_start();
include '../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'] ?? 0;
    
    switch ($action) {
        case 'deactivate':
            $status = 'inactive';
            $sql = "UPDATE users SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $user_id);
            if ($stmt->execute()) {
                $success = "User deactivated successfully!";
            }
            break;
            
        case 'activate':
            $status = 'active';
            $sql = "UPDATE users SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $user_id);
            if ($stmt->execute()) {
                $success = "User activated successfully!";
            }
            break;
            
        case 'delete':
            // Check if user has any shipments or bookings first
            $check_sql = "SELECT COUNT(*) as count FROM shipments WHERE user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $shipment_count = $check_result->fetch_assoc()['count'];
            
            if ($shipment_count == 0) {
                $sql = "DELETE FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $success = "User deleted successfully!";
                }
            } else {
                $error = "Cannot delete user with existing shipments. Please deactivate instead.";
            }
            break;
            
        case 'update_profile':
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            
            // Update user profile
            $sql = "UPDATE user_profiles SET first_name = ?, last_name = ?, phone = ?, address = ?, updated_at = NOW() WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $address, $user_id);
            if ($stmt->execute()) {
                $success = "User profile updated successfully!";
            }
            break;
    }
}

// Get all users with profile information
$users_sql = "SELECT u.*, up.first_name, up.last_name, up.phone, up.address 
              FROM users u 
              LEFT JOIN user_profiles up ON u.id = up.user_id 
              ORDER BY u.created_at DESC";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - J&T Express Admin</title>
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
        
        .status-active { background: #d1fae5; color: #059669; }
        .status-inactive { background: #fee2e2; color: #dc2626; }
        
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
        .admin-btn-danger { background: #ef4444; }
        .admin-btn-danger:hover { background: #dc2626; }
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
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
        }
        
        .user-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        
        .detail-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }
        
        .detail-card h3 {
            margin-top: 0;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        
        .user-stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-card {
            flex: 1;
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #dc2626;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-logo">
                <h1>👥 USER MANAGEMENT</h1>
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
                <h2>Registered Users</h2>
                <div>
                    <a href="dashboard.php" class="admin-btn admin-btn-secondary">Back to Dashboard</a>
                </div>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars(($user['first_name'] ?? $user['username']) . ' ' . ($user['last_name'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $user['status'] ?? 'active'; ?>">
                                <?php echo ucfirst($user['status'] ?? 'active'); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="admin-btn" onclick="viewUser(<?php echo $user['id']; ?>)">View</button>
                            <button class="admin-btn admin-btn-warning" onclick="editUser(<?php echo $user['id']; ?>)">Edit</button>
                            
                            <?php if (($user['status'] ?? 'active') == 'active'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="admin-btn admin-btn-secondary" 
                                            onclick="return confirm('Deactivate this user?')">Deactivate</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="admin-btn admin-btn-success">Activate</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="admin-btn admin-btn-danger" 
                                        onclick="return confirm('Delete this user? This action cannot be undone and will only work if the user has no shipments.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- View User Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeViewModal()">&times;</span>
            <h2>User Details</h2>
            <div id="userDetailsContent">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit User Profile</h2>
            <form method="POST" id="editUserForm">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="user-details">
                    <div class="form-group">
                        <label for="edit_first_name">First Name:</label>
                        <input type="text" name="first_name" id="edit_first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_last_name">Last Name:</label>
                        <input type="text" name="last_name" id="edit_last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_phone">Phone:</label>
                        <input type="text" name="phone" id="edit_phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_address">Address:</label>
                        <textarea name="address" id="edit_address"></textarea>
                    </div>
                </div>
                
                <button type="submit" class="admin-btn">Update Profile</button>
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeEditModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function viewUser(userId) {
            // In a real implementation, this would fetch and display user details
            fetch(`get_user_details.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('userDetailsContent').innerHTML = `
                        <div class="user-stats">
                            <div class="stat-card">
                                <div class="stat-number">${data.shipment_count || 0}</div>
                                <div class="stat-label">Shipments</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">${data.booking_count || 0}</div>
                                <div class="stat-label">Bookings</div>
                            </div>
                        </div>
                        <div class="user-details">
                            <div class="detail-card">
                                <h3>Account Information</h3>
                                <p><strong>Username:</strong> ${data.username || 'N/A'}</p>
                                <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                                <p><strong>Status:</strong> ${data.status || 'N/A'}</p>
                                <p><strong>Registered:</strong> ${data.created_at || 'N/A'}</p>
                            </div>
                            <div class="detail-card">
                                <h3>Profile Information</h3>
                                <p><strong>Name:</strong> ${data.first_name || ''} ${data.last_name || ''}</p>
                                <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                            </div>
                        </div>
                    `;
                    document.getElementById('viewModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading user details');
                });
        }
        
        function editUser(userId) {
            // In a real implementation, this would pre-fill the form with user data
            document.getElementById('editUserId').value = userId;
            // You would fetch current user data and populate the form fields
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>