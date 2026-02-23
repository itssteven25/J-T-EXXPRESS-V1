<?php
session_start();
include '../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Log the logout action
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $log_sql = "INSERT INTO admin_logs (admin_id, action, description) VALUES (?, ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    $action = "admin_logout";
    $description = "Admin logged out";
    $log_stmt->bind_param("iss", $admin_id, $action, $description);
    $log_stmt->execute();
}

// Destroy all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);
unset($_SESSION['admin_login_time']);

// Destroy the entire session
session_destroy();

// Redirect to login page
header("Location: login.php?logout=success");
exit();
?>