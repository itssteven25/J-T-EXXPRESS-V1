<?php
session_start();
include '../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Default date range (last 30 days)
$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$to_date = $_GET['to_date'] ?? date('Y-m-d');

// Get report data
// Total shipments
$total_shipments_sql = "SELECT COUNT(*) as total FROM shipments WHERE DATE(created_date) BETWEEN ? AND ?";
$stmt = $conn->prepare($total_shipments_sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$total_shipments = $stmt->get_result()->fetch_assoc()['total'];

// Shipments by status
$status_breakdown_sql = "SELECT status, COUNT(*) as count FROM shipments 
                        WHERE DATE(created_date) BETWEEN ? AND ? 
                        GROUP BY status";
$stmt = $conn->prepare($status_breakdown_sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$status_result = $stmt->get_result();
$status_breakdown = [];
while ($row = $status_result->fetch_assoc()) {
    $status_breakdown[$row['status']] = $row['count'];
}

// Revenue calculation (if COD implemented)
$revenue_sql = "SELECT SUM(declared_value) as total_revenue FROM shipments 
                WHERE DATE(created_date) BETWEEN ? AND ? AND status = 'Delivered'";
$stmt = $conn->prepare($revenue_sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$total_revenue = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

// Daily shipment count
$daily_shipments_sql = "SELECT DATE(created_date) as date, COUNT(*) as count 
                       FROM shipments 
                       WHERE DATE(created_date) BETWEEN ? AND ? 
                       GROUP BY DATE(created_date) 
                       ORDER BY DATE(created_date)";
$stmt = $conn->prepare($daily_shipments_sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$daily_result = $stmt->get_result();
$daily_shipments = [];
while ($row = $daily_result->fetch_assoc()) {
    $daily_shipments[] = $row;
}

// Top destinations
$destinations_sql = "SELECT destination, COUNT(*) as count 
                    FROM shipments 
                    WHERE DATE(created_date) BETWEEN ? AND ? 
                    GROUP BY destination 
                    ORDER BY count DESC 
                    LIMIT 10";
$stmt = $conn->prepare($destinations_sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$destinations_result = $stmt->get_result();

// Performance metrics
$on_time_deliveries_sql = "SELECT COUNT(*) as on_time FROM shipments 
                          WHERE DATE(created_date) BETWEEN ? AND ? 
                          AND status = 'Delivered' 
                          AND DATEDIFF(delivery_time, created_date) <= 3";
$stmt = $conn->prepare($on_time_deliveries_sql);
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$on_time_deliveries = $stmt->get_result()->fetch_assoc()['on_time'] ?? 0;

$delivery_rate = $total_shipments > 0 ? ($status_breakdown['Delivered'] ?? 0) / $total_shipments * 100 : 0;
$on_time_rate = ($status_breakdown['Delivered'] ?? 0) > 0 ? $on_time_deliveries / ($status_breakdown['Delivered'] ?? 1) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - J&T Express Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .date-filter {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .date-filter input {
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
        }
        
        .admin-btn {
            background: #dc2626;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .admin-btn:hover { background: #b91c1c; }
        .admin-btn-secondary { background: #6b7280; }
        .admin-btn-secondary:hover { background: #4b5563; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        
        .stat-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #dc2626;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #dc2626;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 16px;
        }
        
        .chart-container {
            height: 300px;
            margin: 25px 0;
        }
        
        .performance-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }
        
        .metric-card {
            background: #f0f9ff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #bae6fd;
        }
        
        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: #0284c7;
            margin: 10px 0;
        }
        
        .metric-label {
            color: #0369a1;
            font-size: 14px;
            font-weight: 600;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-logo">
                <h1>📊 REPORTS & ANALYTICS</h1>
            </div>
            <div class="admin-user-info">
                <span>Admin: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="dashboard.php" class="admin-btn" style="margin-left: 15px;">Dashboard</a>
                <a href="logout.php" class="admin-btn">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="admin-content">
        <!-- Date Filter -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h2>Report Filters</h2>
                <form method="GET" class="date-filter">
                    <label>From:</label>
                    <input type="date" name="from_date" value="<?php echo $from_date; ?>">
                    <label>To:</label>
                    <input type="date" name="to_date" value="<?php echo $to_date; ?>">
                    <button type="submit" class="admin-btn">Generate Report</button>
                    <button type="button" class="admin-btn admin-btn-secondary" onclick="exportReport()">Export</button>
                </form>
            </div>
        </div>
        
        <!-- Key Statistics -->
        <div class="admin-section">
            <h2>Key Statistics (<?php echo date('M j, Y', strtotime($from_date)); ?> - <?php echo date('M j, Y', strtotime($to_date)); ?>)</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Shipments</div>
                    <div class="stat-number"><?php echo $total_shipments; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-number">₱<?php echo number_format($total_revenue, 2); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Delivered Shipments</div>
                    <div class="stat-number"><?php echo $status_breakdown['Delivered'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending Shipments</div>
                    <div class="stat-number"><?php echo ($status_breakdown['Pick Up'] ?? 0) + ($status_breakdown['In Transit'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Performance Metrics -->
        <div class="admin-section">
            <h2>Performance Metrics</h2>
            <div class="performance-metrics">
                <div class="metric-card">
                    <div class="metric-label">Delivery Rate</div>
                    <div class="metric-value"><?php echo number_format($delivery_rate, 1); ?>%</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">On-Time Delivery</div>
                    <div class="metric-value"><?php echo number_format($on_time_rate, 1); ?>%</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Avg. Daily Shipments</div>
                    <div class="metric-value"><?php echo count($daily_shipments) > 0 ? number_format(array_sum(array_column($daily_shipments, 'count')) / count($daily_shipments), 1) : 0; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="admin-section">
            <h2>Shipment Trends</h2>
            <div class="chart-container">
                <canvas id="shipmentsChart"></canvas>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>Shipment Status Distribution</h2>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        
        <!-- Top Destinations -->
        <div class="admin-section">
            <h2>Top Destinations</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Destination</th>
                        <th>Shipment Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($dest = $destinations_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dest['destination']); ?></td>
                        <td><?php echo $dest['count']; ?></td>
                        <td><?php echo number_format(($dest['count'] / $total_shipments) * 100, 1); ?>%</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // Daily Shipments Chart
        const ctx1 = document.getElementById('shipmentsChart').getContext('2d');
        const shipmentsChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: [<?php 
                    $dates = array_column($daily_shipments, 'date');
                    echo '"' . implode('","', $dates) . '"';
                ?>],
                datasets: [{
                    label: 'Daily Shipments',
                    data: [<?php 
                        $counts = array_column($daily_shipments, 'count');
                        echo implode(',', $counts);
                    ?>],
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Status Distribution Chart
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: [<?php 
                    $statuses = array_keys($status_breakdown);
                    echo '"' . implode('","', $statuses) . '"';
                ?>],
                datasets: [{
                    data: [<?php 
                        $counts = array_values($status_breakdown);
                        echo implode(',', $counts);
                    ?>],
                    backgroundColor: [
                        '#fef3c7',
                        '#dbeafe',
                        '#d1fae5',
                        '#ddd6fe',
                        '#fee2e2'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        function exportReport() {
            // In a real implementation, this would generate and download a PDF/Excel report
            alert('Export functionality would generate a detailed report in PDF or Excel format');
        }
    </script>
</body>
</html>