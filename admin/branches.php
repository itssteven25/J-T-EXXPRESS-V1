<?php
session_start();
include '../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle branch actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_branch':
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $city = $_POST['city'] ?? '';
            $postal_code = $_POST['postal_code'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $operating_hours = $_POST['operating_hours'] ?? '';
            $latitude = $_POST['latitude'] ?? null;
            $longitude = $_POST['longitude'] ?? null;
            
            $sql = "INSERT INTO drop_points (name, address, city, postal_code, phone, operating_hours, latitude, longitude, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $name, $address, $city, $postal_code, $phone, $operating_hours, $latitude, $longitude);
            if ($stmt->execute()) {
                $success = "Branch added successfully!";
            }
            break;
            
        case 'edit_branch':
            $branch_id = $_POST['branch_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $city = $_POST['city'] ?? '';
            $postal_code = $_POST['postal_code'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $operating_hours = $_POST['operating_hours'] ?? '';
            $latitude = $_POST['latitude'] ?? null;
            $longitude = $_POST['longitude'] ?? null;
            
            $sql = "UPDATE drop_points SET name = ?, address = ?, city = ?, postal_code = ?, phone = ?, 
                    operating_hours = ?, latitude = ?, longitude = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $name, $address, $city, $postal_code, $phone, $operating_hours, $latitude, $longitude, $branch_id);
            if ($stmt->execute()) {
                $success = "Branch updated successfully!";
            }
            break;
            
        case 'delete_branch':
            $branch_id = $_POST['branch_id'] ?? 0;
            $sql = "DELETE FROM drop_points WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $branch_id);
            if ($stmt->execute()) {
                $success = "Branch deleted successfully!";
            }
            break;
            
        case 'toggle_status':
            $branch_id = $_POST['branch_id'] ?? 0;
            $current_status = $_POST['current_status'] ?? 'Active';
            $new_status = $current_status == 'Active' ? 'Inactive' : 'Active';
            
            $sql = "UPDATE drop_points SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_status, $branch_id);
            if ($stmt->execute()) {
                $success = "Branch status updated to: $new_status";
            }
            break;
    }
}

// Get all branches
$branches_sql = "SELECT * FROM drop_points ORDER BY city, name";
$branches_result = $conn->query($branches_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Management - J&T Express Admin</title>
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
                <h1>🏢 BRANCH MANAGEMENT</h1>
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
                <h2>Branch Locations</h2>
                <button class="admin-btn" onclick="openAddModal()">Add New Branch</button>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Branch Name</th>
                        <th>Location</th>
                        <th>Phone</th>
                        <th>Hours</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($branch = $branches_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $branch['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($branch['name']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($branch['address']); ?><br>
                            <small><?php echo htmlspecialchars($branch['city']); ?> <?php echo htmlspecialchars($branch['postal_code']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($branch['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($branch['operating_hours'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($branch['status']); ?>">
                                <?php echo htmlspecialchars($branch['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="admin-btn" onclick="editBranch(<?php echo $branch['id']; ?>)">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="branch_id" value="<?php echo $branch['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $branch['status']; ?>">
                                <input type="hidden" name="action" value="toggle_status">
                                <button type="submit" class="admin-btn <?php echo $branch['status'] == 'Active' ? 'admin-btn-secondary' : 'admin-btn-success'; ?>">
                                    <?php echo $branch['status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="branch_id" value="<?php echo $branch['id']; ?>">
                                <input type="hidden" name="action" value="delete_branch">
                                <button type="submit" class="admin-btn admin-btn-danger" 
                                        onclick="return confirm('Delete this branch? This action cannot be undone.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Branch Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2>Add New Branch</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_branch">
                
                <div class="form-group">
                    <label for="name">Branch Name:</label>
                    <input type="text" name="name" id="name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" name="city" id="city" required>
                    </div>
                    <div class="form-group">
                        <label for="postal_code">Postal Code:</label>
                        <input type="text" name="postal_code" id="postal_code" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Full Address:</label>
                    <textarea name="address" id="address" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="text" name="phone" id="phone">
                    </div>
                    <div class="form-group">
                        <label for="operating_hours">Operating Hours:</label>
                        <input type="text" name="operating_hours" id="operating_hours" placeholder="e.g., 8:00 AM - 8:00 PM">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="latitude">Latitude (Optional):</label>
                        <input type="text" name="latitude" id="latitude">
                    </div>
                    <div class="form-group">
                        <label for="longitude">Longitude (Optional):</label>
                        <input type="text" name="longitude" id="longitude">
                    </div>
                </div>
                
                <button type="submit" class="admin-btn">Add Branch</button>
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeAddModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <!-- Edit Branch Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Branch</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit_branch">
                <input type="hidden" name="branch_id" id="edit_branch_id">
                
                <div class="form-group">
                    <label for="edit_name">Branch Name:</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_city">City:</label>
                        <input type="text" name="city" id="edit_city" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_postal_code">Postal Code:</label>
                        <input type="text" name="postal_code" id="edit_postal_code" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_address">Full Address:</label>
                    <textarea name="address" id="edit_address" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_phone">Phone Number:</label>
                        <input type="text" name="phone" id="edit_phone">
                    </div>
                    <div class="form-group">
                        <label for="edit_operating_hours">Operating Hours:</label>
                        <input type="text" name="operating_hours" id="edit_operating_hours">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_latitude">Latitude:</label>
                        <input type="text" name="latitude" id="edit_latitude">
                    </div>
                    <div class="form-group">
                        <label for="edit_longitude">Longitude:</label>
                        <input type="text" name="longitude" id="edit_longitude">
                    </div>
                </div>
                
                <button type="submit" class="admin-btn">Update Branch</button>
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeEditModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        function editBranch(branchId) {
            // In a real implementation, this would fetch branch data and populate the form
            document.getElementById('edit_branch_id').value = branchId;
            // You would fetch current branch data and populate the form fields
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>