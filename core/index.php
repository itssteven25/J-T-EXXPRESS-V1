<?php
// Core J&T Express System - 70% streamlined version
session_start();

// Include database connection
include '../includes/db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Redirect to core dashboard
header("Location: core-dashboard.php");
exit();
?>