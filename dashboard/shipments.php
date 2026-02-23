<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Get all shipments
$shipments_sql = "SELECT * FROM shipments ORDER BY created_date DESC";
$shipments_result = $conn->query($shipments_sql);

// Get status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
if ($status_filter) {
    $shipments_sql = "SELECT * FROM shipments WHERE status = ? ORDER BY created_date DESC";
    $stmt = $conn->prepare($shipments_sql);
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $shipments_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Shipments - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>All Shipments</h1>
            </div>
            
            <!-- Filters -->
            <div class="filters-section">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="status-filter">Status:</label>
                        <select id="status-filter" onchange="filterShipments()">
                            <option value="">All Status</option>
                            <option value="Pick Up" <?php echo $status_filter == 'Pick Up' ? 'selected' : ''; ?>>Pick Up</option>
                            <option value="In Transit" <?php echo $status_filter == 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                            <option value="Delivered" <?php echo $status_filter == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button class="btn btn-secondary">Filter</button>
                    </div>
                </div>
            </div>
            
            <!-- Shipments Table -->
            <div class="shipments-table-container">
                <table class="shipments-table">
                    <thead>
                        <tr>
                            <th>Tracking Number</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $shipments_result->fetch_assoc()): ?>
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
                            <td>
                                <div class="action-buttons">
                                    <select class="status-update-select" onchange="updateShipmentStatus('<?php echo $row['tracking_number']; ?>', this.value)" style="margin-bottom: 10px;">
                                        <option value="" disabled selected>Change Status</option>
                                        <option value="Pick Up" <?php echo $row['status'] === 'Pick Up' ? 'selected' : ''; ?>>Pick Up</option>
                                        <option value="In Transit" <?php echo $row['status'] === 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                                        <option value="Delivered" <?php echo $row['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    </select>
                                    <a href="my-shipments.php?tracking=<?php echo $row['tracking_number']; ?>" class="action-link">View Details</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        function filterShipments() {
            const status = document.getElementById('status-filter').value;
            window.location.href = `shipments.php?status=${status}`;
        }
    </script>
</body>
</html>