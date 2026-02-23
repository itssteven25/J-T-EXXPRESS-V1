<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Mark notification as read if ID is provided
if (isset($_GET['read'])) {
    $notification_id = $_GET['read'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
    $stmt->execute();
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

// Fetch user notifications
$notifications_sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($notifications_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Notifications</h1>
            </div>
            
            <div class="notifications-container">
                <div class="notifications-header">
                    <div class="header-left">
                        <h2>All Notifications</h2>
                        <span class="notification-count"><?php echo $notifications_result->num_rows; ?> notifications</span>
                    </div>
                    <div class="header-right">
                        <a href="?mark_all_read=1" class="btn btn-secondary">Mark All as Read</a>
                    </div>
                </div>
                
                <div class="notifications-list">
                    <?php if ($notifications_result->num_rows > 0): ?>
                        <?php while($notification = $notifications_result->fetch_assoc()): ?>
                        <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                            <div class="notification-icon">
                                <?php 
                                switch($notification['type']) {
                                    case 'success': echo '✅'; break;
                                    case 'warning': echo '⚠️'; break;
                                    case 'error': echo '❌'; break;
                                    case 'info': 
                                    default: echo '🔔'; break;
                                }
                                ?>
                            </div>
                            <div class="notification-content">
                                <div class="notification-header">
                                    <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                    <span class="notification-time"><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <div class="notification-actions">
                                    <?php if (!$notification['is_read']): ?>
                                        <a href="?read=<?php echo $notification['id']; ?>" class="mark-read">Mark as Read</a>
                                    <?php endif; ?>
                                    <a href="#" class="dismiss-notification">Dismiss</a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🔔</div>
                            <h3>No Notifications</h3>
                            <p>You don't have any notifications at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="notification-settings">
                    <h3>Notification Settings</h3>
                    <div class="settings-options">
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" checked>
                                <span class="checkmark"></span>
                                Push notifications
                            </label>
                        </div>
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" checked>
                                <span class="checkmark"></span>
                                Email notifications
                            </label>
                        </div>
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                SMS notifications
                            </label>
                        </div>
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox" checked>
                                <span class="checkmark"></span>
                                Shipment updates
                            </label>
                        </div>
                        <div class="setting-item">
                            <label class="checkbox-label">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Promotional offers
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        // Dismiss notification
        document.querySelectorAll('.dismiss-notification').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationItem = this.closest('.notification-item');
                notificationItem.style.opacity = '0';
                setTimeout(() => {
                    notificationItem.remove();
                }, 300);
            });
        });
    </script>
</body>
</html>