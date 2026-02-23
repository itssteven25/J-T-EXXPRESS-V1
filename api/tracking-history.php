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
        
        // Generate mock tracking history based on the status
        $history = [];
        $base_time = strtotime($shipment['created_date']);
        
        $history[] = [
            'status' => 'Package Received',
            'description' => 'Package has been received at our facility',
            'timestamp' => date('Y-m-d H:i:s', $base_time),
            'location' => 'Origin Facility'
        ];
        
        if ($shipment['status'] !== 'Pick Up') {
            $history[] = [
                'status' => 'Processing',
                'description' => 'Package is being processed for shipment',
                'timestamp' => date('Y-m-d H:i:s', $base_time + 3600),
                'location' => 'Sorting Center'
            ];
        }
        
        if ($shipment['status'] === 'In Transit' || $shipment['status'] === 'Delivered') {
            $history[] = [
                'status' => 'In Transit',
                'description' => 'Package is in transit to destination',
                'timestamp' => date('Y-m-d H:i:s', $base_time + 7200),
                'location' => 'Distribution Hub'
            ];
        }
        
        if ($shipment['status'] === 'Delivered') {
            $history[] = [
                'status' => 'Out for Delivery',
                'description' => 'Package is out for delivery',
                'timestamp' => date('Y-m-d H:i:s', strtotime($shipment['updated_date']) - 3600),
                'location' => 'Local Delivery Hub'
            ];
            
            $history[] = [
                'status' => 'Delivered',
                'description' => 'Package has been successfully delivered',
                'timestamp' => $shipment['updated_date'],
                'location' => $shipment['destination']
            ];
        }
        
        echo json_encode(array_reverse($history));
    } else {
        echo json_encode(['error' => 'Tracking number not found']);
    }
} else {
    echo json_encode(['error' => 'Tracking number not provided']);
}
?>