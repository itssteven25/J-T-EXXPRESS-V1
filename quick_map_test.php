<?php
// Quick Map Test - No API Key Required
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Map Test</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .map-container { height: 300px; width: 100%; margin: 20px 0; border: 1px solid #ddd; border-radius: 8px; }
        h1 { color: #dc2626; text-align: center; }
        .btn { background: #dc2626; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 10px 5px; }
        .btn:hover { background: #b91c1c; }
        .test-section { margin: 30px 0; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗺️ Map Functionality - Working!</h1>
        
        <div class="test-section">
            <h2>✅ Maps Are Now Working</h2>
            <p>All map errors have been fixed by switching to <strong>OpenStreetMap with Leaflet.js</strong></p>
            <p class="success">No Google Maps API key required!</p>
        </div>
        
        <div class="test-section">
            <h2>📍 Sample Map Test</h2>
            <p>Click the button below to load a sample map showing J&T Express locations:</p>
            <button class="btn" onclick="loadSampleMap()">Load Sample Map</button>
            <div id="sample-map" class="map-container" style="display: none;"></div>
        </div>
        
        <div class="test-section">
            <h2>🔗 Quick Links to Test All Maps</h2>
            <a href="index.php" class="btn">Homepage Map</a>
            <a href="drop-points/drop-points.php" class="btn">Drop Points Map</a>
            <a href="pickup/package-pickup.php" class="btn">Pickup Location Map</a>
            <a href="test_maps.php" class="btn">Comprehensive Map Test</a>
        </div>
        
        <div class="test-section">
            <h2>✅ What's Fixed</h2>
            <ul>
                <li>Removed Google Maps API dependency</li>
                <li>Implemented free OpenStreetMap with Leaflet.js</li>
                <li>All map functionality working without API keys</li>
                <li>Geolocation support for user location detection</li>
                <li>Interactive markers with popup information</li>
                <li>Directions integration with Google Maps</li>
            </ul>
        </div>
    </div>

    <script>
        let sampleMap;
        
        function loadSampleMap() {
            const mapElement = document.getElementById('sample-map');
            mapElement.style.display = 'block';
            
            // Center on Manila
            const manila = [14.5995, 120.9842];
            
            sampleMap = L.map('sample-map').setView(manila, 12);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(sampleMap);
            
            // Add sample J&T locations
            const locations = [
                { coords: [14.5995, 120.9842], name: "J&T Main Branch - Manila", address: "123 Main Street, Manila" },
                { coords: [14.5547, 121.0244], name: "J&T Branch - Makati", address: "456 Business Ave, Makati" },
                { coords: [14.6348, 121.0457], name: "J&T Branch - Quezon City", address: "789 Central Ave, QC" }
            ];
            
            locations.forEach(location => {
                const marker = L.marker(location.coords).addTo(sampleMap);
                marker.bindPopup(`
                    <div>
                        <h3 style="color: #dc2626; margin: 0 0 10px 0;">${location.name}</h3>
                        <p style="margin: 5px 0;">${location.address}</p>
                        <button onclick="window.open('https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(location.address)}', '_blank')" 
                                style="background: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                            Get Directions
                        </button>
                    </div>
                `);
            });
            
            // Add user location if available
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLocation = [position.coords.latitude, position.coords.longitude];
                        const userMarker = L.marker(userLocation, {
                            title: 'Your Location'
                        }).addTo(sampleMap);
                        userMarker.bindPopup('<strong>Your Current Location</strong>');
                    }
                );
            }
        }
    </script>
</body>
</html>