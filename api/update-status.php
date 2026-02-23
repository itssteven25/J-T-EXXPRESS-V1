<?php
header('Content-Type: application/json');
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $tracking_number = $data['tracking_number'] ?? '';
    $new_status = $data['status'] ?? '';
    
    // Validate inputs
    if (empty($tracking_number) || empty($new_status)) {
        echo json_encode(['success' => false, 'message' => 'Tracking number and status are required']);
        exit;
    }
    
    // Validate status
    $valid_statuses = ['Pick Up', 'In Transit', 'Delivered'];
    if (!in_array($new_status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    // Update the shipment status
    $sql = "UPDATE shipments SET status = ?, updated_date = NOW() WHERE tracking_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $new_status, $tracking_number);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Status updated successfully',
            'tracking_number' => $tracking_number,
            'new_status' => $new_status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>