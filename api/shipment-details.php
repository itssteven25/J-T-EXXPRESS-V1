<?php
header('Content-Type: application/json');
include '../includes/db.php';

$tracking_number = isset($_GET['tracking']) ? $_GET['tracking'] : '';

if ($tracking_number) {
    $sql = "SELECT * FROM shipments WHERE tracking_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tracking_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $shipment = $result->fetch_assoc();
        echo json_encode($shipment);
    } else {
        echo json_encode(['error' => 'Shipment not found']);
    }
} else {
    echo json_encode(['error' => 'Tracking number not provided']);
}
?>