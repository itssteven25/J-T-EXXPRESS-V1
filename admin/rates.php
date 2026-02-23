<?php
session_start();
include '../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle rate actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_rate':
            $service_type = $_POST['service_type'] ?? '';
            $weight_from = $_POST['weight_from'] ?? 0;
            $weight_to = $_POST['weight_to'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $estimated_days = $_POST['estimated_days'] ?? 0;
            
            $sql = "INSERT INTO shipping_rates (service_type, weight_from, weight_to, price, estimated_days) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdddi", $service_type, $weight_from, $weight_to, $price, $estimated_days);
            if ($stmt->execute()) {
                $success = "Rate added successfully!";
            }
            break;
            
        case 'edit_rate':
            $rate_id = $_POST['rate_id'] ?? 0;
            $service_type = $_POST['service_type'] ?? '';
            $weight_from = $_POST['weight_from'] ?? 0;
            $weight_to = $_POST['weight_to'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $estimated_days = $_POST['estimated_days'] ?? 0;
            
            $sql = "UPDATE shipping_rates SET service_type = ?, weight_from = ?, weight_to = ?, 
                    price = ?, estimated_days = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdddi", $service_type, $weight_from, $weight_to, $price, $estimated_days, $rate_id);
            if ($stmt->execute()) {
                $success = "Rate updated successfully!";
            }
            break;
            
        case 'delete_rate':
            $rate_id = $_POST['rate_id'] ?? 0;
            $sql = "DELETE FROM shipping_rates WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $rate_id);
            if ($stmt->execute()) {
                $success = "Rate deleted successfully!";
            }
            break;
    }
}

// Get all rates
$rates_sql = "SELECT * FROM shipping_rates ORDER BY service_type, weight_from";
$rates_result = $conn->query($rates_sql);

// Get unique service types for filter
$service_types_sql = "SELECT DISTINCT service_type FROM shipping_rates ORDER BY service_type";
$service_types_result = $conn->query($service_types_sql);
$service_types = [];
while ($row = $service_types_result->fetch_assoc()) {
    $service_types[] = $row['service_type'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Management - J&T Express Admin</title>
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
        
        .admin-table td {
            font-size: 14px;
        }
        
        .price {
            font-weight: 700;
            color: #dc2626;
            font-size: 16px;
        }
        
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
        .form-group select {
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
        
        .rate-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #dc2626;
        }
        
        .service-header {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .weight-range {
            font-weight: 600;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-logo">
                <h1>💰 RATE MANAGEMENT</h1>
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
                <h2>Shipping Rates</h2>
                <button class="admin-btn" onclick="openAddModal()">Add New Rate</button>
            </div>
            
            <?php foreach ($service_types as $service_type): ?>
                <div class="rate-card">
                    <div class="service-header"><?php echo htmlspecialchars($service_type); ?> Rates</div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Weight Range (kg)</th>
                                <th>Price (₱)</th>
                                <th>Estimated Days</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($rates_result, 0);
                            while ($rate = $rates_result->fetch_assoc()):
                                if ($rate['service_type'] == $service_type):
                            ?>
                            <tr>
                                <td>
                                    <span class="weight-range"><?php echo $rate['weight_from']; ?> - <?php echo $rate['weight_to']; ?> kg</span>
                                </td>
                                <td><span class="price">₱<?php echo number_format($rate['price'], 2); ?></span></td>
                                <td><?php echo $rate['estimated_days']; ?> day<?php echo $rate['estimated_days'] != 1 ? 's' : ''; ?></td>
                                <td>
                                    <button class="admin-btn" onclick="editRate(<?php echo $rate['id']; ?>)">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="rate_id" value="<?php echo $rate['id']; ?>">
                                        <input type="hidden" name="action" value="delete_rate">
                                        <button type="submit" class="admin-btn admin-btn-danger" 
                                                onclick="return confirm('Delete this rate?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php 
                                endif;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Add Rate Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2>Add New Rate</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_rate">
                
                <div class="form-group">
                    <label for="service_type">Service Type:</label>
                    <select name="service_type" id="service_type" required>
                        <option value="">Select service type</option>
                        <option value="Standard">Standard</option>
                        <option value="Express">Express</option>
                        <option value="Same Day">Same Day</option>
                        <option value="Overnight">Overnight</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="weight_from">Weight From (kg):</label>
                        <input type="number" name="weight_from" id="weight_from" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="weight_to">Weight To (kg):</label>
                        <input type="number" name="weight_to" id="weight_to" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (₱):</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="estimated_days">Estimated Days:</label>
                        <input type="number" name="estimated_days" id="estimated_days" min="0" required>
                    </div>
                </div>
                
                <button type="submit" class="admin-btn">Add Rate</button>
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeAddModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <!-- Edit Rate Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Rate</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit_rate">
                <input type="hidden" name="rate_id" id="edit_rate_id">
                
                <div class="form-group">
                    <label for="edit_service_type">Service Type:</label>
                    <select name="service_type" id="edit_service_type" required>
                        <option value="">Select service type</option>
                        <option value="Standard">Standard</option>
                        <option value="Express">Express</option>
                        <option value="Same Day">Same Day</option>
                        <option value="Overnight">Overnight</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_weight_from">Weight From (kg):</label>
                        <input type="number" name="weight_from" id="edit_weight_from" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_weight_to">Weight To (kg):</label>
                        <input type="number" name="weight_to" id="edit_weight_to" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_price">Price (₱):</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_estimated_days">Estimated Days:</label>
                        <input type="number" name="estimated_days" id="edit_estimated_days" min="0" required>
                    </div>
                </div>
                
                <button type="submit" class="admin-btn">Update Rate</button>
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
        
        function editRate(rateId) {
            // In a real implementation, this would fetch rate data and populate the form
            document.getElementById('edit_rate_id').value = rateId;
            // You would fetch current rate data and populate the form fields
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