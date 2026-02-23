<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Get search parameters
$search_query = trim($_GET['search'] ?? '');
$city_filter = trim($_GET['city'] ?? '');

// Build query based on search parameters
$sql = "SELECT * FROM drop_points WHERE status = 'Active'";
$params = [];
$types = '';

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR address LIKE ? OR city LIKE ? OR postal_code LIKE ?)";
    $search_term = '%' . $search_query . '%';
    $params = [$search_term, $search_term, $search_term, $search_term];
    $types = 'ssss';
}

if (!empty($city_filter)) {
    $sql .= " AND city = ?";
    $params[] = $city_filter;
    $types .= 's';
}

$sql .= " ORDER BY city, name";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $drop_points_result = $stmt->get_result();
} else {
    $drop_points_result = $conn->query($sql);
}

// Get unique cities for filter dropdown
$cities_sql = "SELECT DISTINCT city FROM drop_points WHERE status = 'Active' ORDER BY city";
$cities_result = $conn->query($cities_sql);
$cities = [];
while ($row = $cities_result->fetch_assoc()) {
    $cities[] = $row['city'];
}

// Get total count
$total_count = $drop_points_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop Points - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body onload="initMap()">
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Nearest Drop Points</h1>
            </div>
            
            <div class="drop-points-container">
                <div class="drop-points-intro">
                    <p>Find the nearest J&T Express drop-off locations near you. Drop off your packages at any of our branches.</p>
                </div>
                
                <div class="find-drop-point">
                    <div class="find-card">
                        <h3>Find Drop Point Near You</h3>
                        <form method="GET" class="search-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="search">Search</label>
                                    <input type="text" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search_query); ?>"
                                           placeholder="Search by name, address, or postal code...">
                                </div>
                                
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <select id="city" name="city">
                                        <option value="">All Cities</option>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?php echo htmlspecialchars($city); ?>" 
                                                    <?php echo ($city_filter === $city) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($city); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group search-buttons">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="drop-points.php" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                        
                        <div class="search-results-info">
                            <p>Found <?php echo $total_count; ?> drop point(s)
                            <?php if (!empty($search_query) || !empty($city_filter)): ?>
                                matching your criteria
                            <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="drop-points-map">
                    <h3>Map View</h3>
                    <div class="map-container">
                        <div id="drop-points-map" style="height: 400px; width: 100%; border-radius: 12px;"></div>
                    </div>
                </div>
                
                <div class="drop-points-list">
                    <h3>Drop Points (<?php echo $total_count; ?> found)</h3>
                    <?php if ($total_count > 0): ?>
                        <div class="points-grid">
                            <?php while($point = $drop_points_result->fetch_assoc()): ?>
                            <div class="point-card">
                                <div class="point-header">
                                    <h4><?php echo htmlspecialchars($point['name']); ?></h4>
                                    <span class="status-badge status-active">Open</span>
                                </div>
                                <div class="point-details">
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($point['address']); ?></p>
                                    <p><strong>City:</strong> <?php echo htmlspecialchars($point['city']); ?></p>
                                    <p><strong>Postal Code:</strong> <?php echo htmlspecialchars($point['postal_code']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($point['phone']); ?></p>
                                    <p><strong>Operating Hours:</strong> <?php echo htmlspecialchars($point['operating_hours']); ?></p>
                                    <?php if ($point['latitude'] && $point['longitude']): ?>
                                        <p><strong>Coordinates:</strong> <?php echo $point['latitude']; ?>, <?php echo $point['longitude']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="point-actions">
                                    <button class="btn btn-primary" onclick="getDirections('<?php echo addslashes($point['address'] . ', ' . $point['city']); ?>')">Get Directions</button>
                                    <?php if ($point['latitude'] && $point['longitude']): ?>
                                        <button class="btn btn-secondary" onclick="viewOnMap(<?php echo $point['latitude']; ?>, <?php echo $point['longitude']; ?>)">View on Map</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <h3>No Drop Points Found</h3>
                            <p>Sorry, we couldn't find any drop points matching your search criteria.</p>
                            <a href="drop-points.php" class="btn btn-primary">View All Drop Points</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        function findNearestDropPoints() {
            // Check if geolocation is supported
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }
            
            // Get user's current location
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // In a real application, this would calculate distances to all drop points
                    // and sort them by proximity
                    alert(`Found your location: ${lat.toFixed(6)}, ${lng.toFixed(6)}\nIn production, this would show nearest drop points.`);
                },
                function(error) {
                    alert('Unable to get your location: ' + error.message);
                }
            );
        }
        
        function getDirections(address) {
            // Open Google Maps directions
            const encodedAddress = encodeURIComponent(address);
            window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodedAddress}`, '_blank');
        }
        
        function viewOnMap(lat, lng) {
            // Open Google Maps at specific coordinates
            window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
        }
        
        function shareLocation(location) {
            // Share location via Web Share API if supported
            if (navigator.share) {
                navigator.share({
                    title: 'J&T Express Drop Point',
                    text: `Check out this J&T Express drop point: ${location}`,
                    url: window.location.href
                }).catch(console.error);
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(location).then(() => {
                    alert('Location copied to clipboard!');
                }).catch(() => {
                    alert('Location: ' + location);
                });
            }
        }
        
        // Initialize map with drop points
        let map;
        let markers = [];
        
        function initMap() {
            // Default center (Manila, Philippines)
            const defaultCenter = [14.5995, 120.9842];
            
            map = L.map('drop-points-map').setView(defaultCenter, 11);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Add markers for all drop points
            <?php 
            mysqli_data_seek($drop_points_result, 0); // Reset result pointer
            while ($point = $drop_points_result->fetch_assoc()): 
                if ($point['latitude'] && $point['longitude']):
            ?>
            addMarker([<?php echo $point['latitude']; ?>, <?php echo $point['longitude']; ?>], 
                     '<?php echo addslashes($point['name']); ?>', 
                     '<?php echo addslashes($point['address']); ?>');
            <?php 
                endif;
            endwhile; 
            ?>
        }
        
        function addMarker(location, title, address) {
            const marker = L.marker(location, {
                title: title
            }).addTo(map);
            
            marker.bindPopup(`
                <div style="padding: 10px; max-width: 200px;">
                    <h4 style="margin: 0 0 8px 0; color: #dc2626;">${title}</h4>
                    <p style="margin: 0 0 8px 0; font-size: 14px;">${address}</p>
                    <button onclick="getDirections('${address.replace(/'/g, "\\'")}')" 
                            style="background: #dc2626; color: white; border: none; padding: 6px 12px; 
                                   border-radius: 4px; cursor: pointer; font-size: 12px;">
                        Get Directions
                    </button>
                </div>
            `);
            
            markers.push(marker);
        }
        
        function viewOnMap(lat, lng) {
            map.setView([lat, lng], 15);
        }
        
        function toggleMapView() {
            const mapContainer = document.getElementById('drop-points-map');
            if (mapContainer.style.display === 'none') {
                mapContainer.style.display = 'block';
                initMap();
            } else {
                mapContainer.style.display = 'none';
            }
        }
        
        function sortByDistance() {
            alert('Distance sorting would require user location and would be implemented in production.');
        }
        
        function getDirections(address) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const origin = position.coords.latitude + ',' + position.coords.longitude;
                        const destination = encodeURIComponent(address);
                        window.open(`https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}`, '_blank');
                    },
                    function(error) {
                        // Fallback to just destination
                        window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(address), '_blank');
                    }
                );
            } else {
                window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(address), '_blank');
            }
        }
        
        // Auto-focus search field on page load
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>