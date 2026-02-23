<?php
header('Content-Type: application/json');
include '../includes/db.php';

// Get shipment statistics
$stats_sql = "SELECT 
    COUNT(*) as total_shipments,
    COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as delivered,
    COUNT(CASE WHEN status = 'In Transit' THEN 1 END) as in_transit,
    COUNT(CASE WHEN status = 'Pick Up' THEN 1 END) as pick_up
FROM shipments";

$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

echo json_encode($stats);
?>