<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Fetch shipping rates
$rates_sql = "SELECT * FROM shipping_rates ORDER BY service_type, weight_from";
$rates_result = $conn->query($rates_sql);

// Fetch locations for origin/destination
$locations_sql = "SELECT DISTINCT city FROM drop_points WHERE status = 'Active' ORDER BY city";
$locations_result = $conn->query($locations_sql);
$locations = [];
while ($row = $locations_result->fetch_assoc()) {
    $locations[] = $row['city'];
}

// Additional service options
$additional_services = [
    ['name' => 'Insurance Coverage', 'description' => 'Protect your valuable items', 'cost_type' => 'percentage', 'rate' => 3],
    ['name' => 'Cash on Delivery', 'description' => 'Accept cash payments upon delivery', 'cost_type' => 'fixed', 'rate' => 30],
    ['name' => 'Signature Required', 'description' => 'Ensure secure delivery with recipient signature', 'cost_type' => 'fixed', 'rate' => 0],
    ['name' => 'Fragile Handling', 'description' => 'Special handling for fragile items', 'cost_type' => 'fixed', 'rate' => 50],
    ['name' => 'Priority Handling', 'description' => 'Fast track processing', 'cost_type' => 'fixed', 'rate' => 100]
];

// Calculate shipping cost function
function calculateShippingCost($conn, $origin, $destination, $weight, $service_type, $additional_services = []) {
    // Get base rate
    $rate_sql = "SELECT price, estimated_days FROM shipping_rates WHERE service_type = ? AND weight_from <= ? AND weight_to >= ? LIMIT 1";
    $stmt = $conn->prepare($rate_sql);
    $stmt->bind_param("sdd", $service_type, $weight, $weight);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return ['error' => 'No rate found for the specified weight and service type'];
    }
    
    $rate = $result->fetch_assoc();
    $base_cost = $rate['price'];
    $estimated_days = $rate['estimated_days'];
    
    // Add additional service costs
    $additional_cost = 0;
    foreach ($additional_services as $service) {
        if ($service['selected']) {
            if ($service['cost_type'] == 'percentage') {
                $additional_cost += ($base_cost * $service['rate'] / 100);
            } else {
                $additional_cost += $service['rate'];
            }
        }
    }
    
    $total_cost = $base_cost + $additional_cost;
    
    // Calculate delivery date (business days)
    $delivery_date = new DateTime();
    $delivery_date->modify('+' . $estimated_days . ' weekdays');
    
    return [
        'base_cost' => $base_cost,
        'additional_cost' => $additional_cost,
        'total_cost' => $total_cost,
        'estimated_days' => $estimated_days,
        'delivery_date' => $delivery_date->format('F j, Y'),
        'breakdown' => [
            'base' => $base_cost,
            'insurance' => $additional_services['Insurance Coverage']['selected'] ? ($base_cost * 0.03) : 0,
            'cod' => $additional_services['Cash on Delivery']['selected'] ? 30 : 0,
            'fragile' => $additional_services['Fragile Handling']['selected'] ? 50 : 0,
            'priority' => $additional_services['Priority Handling']['selected'] ? 100 : 0
        ]
    ];
}

// Process form submission
$calculation_result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $weight = floatval($_POST['weight'] ?? 0);
    $service_type = $_POST['service_type'] ?? 'Standard';
    
    // Process additional services
    $selected_services = [];
    foreach ($additional_services as $service) {
        $selected_services[$service['name']] = [
            'selected' => isset($_POST['services'][$service['name']]),
            'cost_type' => $service['cost_type'],
            'rate' => $service['rate']
        ];
    }
    
    // Validate input
    $errors = [];
    if (empty($origin)) {
        $errors[] = 'Please select origin location';
    }
    if (empty($destination)) {
        $errors[] = 'Please select destination location';
    }
    if ($weight <= 0) {
        $errors[] = 'Please enter a valid weight';
    }
    if (empty($service_type)) {
        $errors[] = 'Please select a service type';
    }
    
    if (empty($errors)) {
        $calculation_result = calculateShippingCost($conn, $origin, $destination, $weight, $service_type, $selected_services);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Rates - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Shipping Rates</h1>
            </div>
            
            <div class="rates-container">
                <div class="rates-intro">
                    <p>Find the best shipping rates for your parcels. Select the service type and weight to calculate your shipping cost.</p>
                </div>
                
                <div class="calculate-shipping">
                    <div class="calculate-card">
                        <h3>Calculate Shipping Cost</h3>
                        <form method="POST" id="shipping-calculator">
                            <?php if (!empty($errors)): ?>
                                <div class="error-message">
                                    <?php foreach ($errors as $error): ?>
                                        <p><?php echo htmlspecialchars($error); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="origin">Origin *</label>
                                    <select id="origin" name="origin" required>
                                        <option value="">Select Origin</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?php echo htmlspecialchars($location); ?>" 
                                                    <?php echo (($_POST['origin'] ?? '') === $location) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($location); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="destination">Destination *</label>
                                    <select id="destination" name="destination" required>
                                        <option value="">Select Destination</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?php echo htmlspecialchars($location); ?>" 
                                                    <?php echo (($_POST['destination'] ?? '') === $location) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($location); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="weight">Weight (kg) *</label>
                                    <input type="number" id="weight" name="weight" min="0.1" step="0.1" 
                                           value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>" 
                                           placeholder="Enter weight" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="service-type">Service Type *</label>
                                    <select id="service-type" name="service_type" required>
                                        <option value="">Select Service</option>
                                        <option value="Standard" <?php echo (($_POST['service_type'] ?? '') === 'Standard') ? 'selected' : ''; ?>>Standard (3-5 days)</option>
                                        <option value="Express" <?php echo (($_POST['service_type'] ?? '') === 'Express') ? 'selected' : ''; ?>>Express (1-2 days)</option>
                                        <option value="Overnight" <?php echo (($_POST['service_type'] ?? '') === 'Overnight') ? 'selected' : ''; ?>>Overnight (Same day)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="additional-services">
                                <h4>Additional Services</h4>
                                <div class="services-grid">
                                    <?php foreach ($additional_services as $service): ?>
                                        <div class="service-option">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="services[<?php echo $service['name']; ?>]" 
                                                       <?php echo (isset($_POST['services'][$service['name']]) ? 'checked' : ''); ?>>
                                                <div class="service-info">
                                                    <strong><?php echo $service['name']; ?></strong>
                                                    <p><?php echo $service['description']; ?></p>
                                                    <span class="service-cost">
                                                        <?php if ($service['cost_type'] == 'percentage'): ?>
                                                            <?php echo $service['rate']; ?>% of base cost
                                                        <?php else: ?>
                                                            ₱<?php echo number_format($service['rate'], 2); ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Calculate Rate</button>
                        </form>
                        
                        <?php if ($calculation_result): ?>
                            <?php if (isset($calculation_result['error'])): ?>
                                <div class="error-message">
                                    <p><?php echo htmlspecialchars($calculation_result['error']); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="calculation-result success">
                                    <h4>Shipping Cost Breakdown</h4>
                                    <div class="cost-breakdown">
                                        <div class="cost-item">
                                            <span>Base Cost:</span>
                                            <span>₱<?php echo number_format($calculation_result['base_cost'], 2); ?></span>
                                        </div>
                                        <?php if ($calculation_result['additional_cost'] > 0): ?>
                                            <div class="cost-item">
                                                <span>Additional Services:</span>
                                                <span>₱<?php echo number_format($calculation_result['additional_cost'], 2); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="cost-item total">
                                            <span><strong>Total Cost:</strong></span>
                                            <span><strong>₱<?php echo number_format($calculation_result['total_cost'], 2); ?></strong></span>
                                        </div>
                                    </div>
                                    
                                    <div class="delivery-info">
                                        <p><strong>Estimated Delivery:</strong> <?php echo $calculation_result['estimated_days']; ?> business day(s)</p>
                                        <p><strong>Expected Delivery Date:</strong> <?php echo $calculation_result['delivery_date']; ?></p>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <button class="btn btn-primary" onclick="bookShipment()">Book Shipment</button>
                                        <button class="btn btn-secondary" onclick="printQuote()">Print Quote</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="rates-table-container">
                    <h2>Rate Schedule</h2>
                    <table class="rates-table">
                        <thead>
                            <tr>
                                <th>Service Type</th>
                                <th>Weight Range (kg)</th>
                                <th>Price (₱)</th>
                                <th>Estimated Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($rate = $rates_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $rate['service_type']; ?></strong></td>
                                <td><?php echo $rate['weight_from']; ?> - <?php echo $rate['weight_to']; ?> kg</td>
                                <td>₱<?php echo number_format($rate['price'], 2); ?></td>
                                <td><?php echo $rate['estimated_days']; ?> day(s)</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="service-info">
                    <h3>Additional Services</h3>
                    <div class="info-grid">
                        <div class="info-card">
                            <h4>Insurance Coverage</h4>
                            <p>Protect your valuable items with our insurance coverage options. Additional 3% of shipping cost.</p>
                        </div>
                        <div class="info-card">
                            <h4>Cash on Delivery</h4>
                            <p>Accept cash payments upon delivery. Additional ₱30 fee applies.</p>
                        </div>
                        <div class="info-card">
                            <h4>Signature Required</h4>
                            <p>Ensure secure delivery with recipient signature. Free of charge.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        function bookShipment() {
            // Get form data
            const origin = document.getElementById('origin').value;
            const destination = document.getElementById('destination').value;
            const weight = document.getElementById('weight').value;
            const serviceType = document.getElementById('service-type').value;
            
            // Redirect to booking page with pre-filled data
            const params = new URLSearchParams({
                origin: origin,
                destination: destination,
                weight: weight,
                service_type: serviceType
            });
            
            window.location.href = '../pickup/package-pickup.php?' + params.toString();
        }
        
        function printQuote() {
            window.print();
        }
        
        // Auto-calculate when service type changes
        document.getElementById('service-type').addEventListener('change', function() {
            const weight = parseFloat(document.getElementById('weight').value);
            const serviceType = this.value;
            
            if (weight && serviceType) {
                // This would trigger a real-time calculation in a production environment
                console.log('Service changed to:', serviceType, 'Weight:', weight);
            }
        });
        
        // Weight validation
        document.getElementById('weight').addEventListener('input', function() {
            const weight = parseFloat(this.value);
            if (weight && weight > 100) {
                alert('Packages over 100kg may require special handling. Please contact customer service.');
            }
        });
    </script>
</body>
</html>