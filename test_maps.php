    <script>
        let dropPointsMap, pickupMap, homepageMap;
        let pickupMarker;
        
        // Test Drop Points Map
        function testDropPointsMap() {
            const mapElement = document.getElementById('drop-points-test-map');
            mapElement.style.display = 'block';
            
            const defaultCenter = [14.5995, 120.9842];
            
            dropPointsMap = L.map('drop-points-test-map').setView(defaultCenter, 11);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(dropPointsMap);
            
            // Sample drop points
            const points = [
                { lat: 14.5995, lng: 120.9842, name: "Main Branch - Manila" },
                { lat: 14.5547, lng: 121.0244, name: "Branch - Makati" },
                { lat: 14.6348, lng: 121.0457, name: "Branch - Quezon City" }
            ];
            
            points.forEach(point => {
                L.marker([point.lat, point.lng], {
                    title: point.name
                }).addTo(dropPointsMap);
            });
        }
        
        // Test Pickup Map
        function testPickupMap() {
            const mapElement = document.getElementById('pickup-test-map');
            const infoElement = document.getElementById('location-info');
            
            if (navigator.geolocation) {
                infoElement.innerHTML = '<p>Getting your location...</p>';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const location = [position.coords.latitude, position.coords.longitude];
                        
                        mapElement.style.display = 'block';
                        infoElement.innerHTML = `<p>Your location: ${location[0].toFixed(6)}, ${location[1].toFixed(6)}</p>`;
                        
                        pickupMap = L.map('pickup-test-map').setView(location, 15);
                        
                        // Add OpenStreetMap tiles
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(pickupMap);
                        
                        pickupMarker = L.marker(location, {
                            title: 'Your Location'
                        }).addTo(pickupMap);
                        
                        pickupMarker.bindPopup('<div style="padding: 10px;"><strong>Your Location</strong></div>');
                    },
                    function(error) {
                        infoElement.innerHTML = `<p style="color: red;">Error getting location: ${error.message}</p>`;
                    }
                );
            } else {
                infoElement.innerHTML = '<p style="color: red;">Geolocation not supported by your browser</p>';
            }
        }
        
        // Test Homepage Map
        function testHomepageMap() {
            const mapElement = document.getElementById('homepage-test-map');
            mapElement.style.display = 'block';
            
            const defaultCenter = [14.5995, 120.9842];
            
            homepageMap = L.map('homepage-test-map').setView(defaultCenter, 11);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(homepageMap);
            
            // Sample points with directions
            const points = [
                { lat: 14.5995, lng: 120.9842, name: "J&T Main Branch - Manila", address: "123 Main Street, Manila" },
                { lat: 14.5547, lng: 121.0244, name: "J&T Branch - Makati", address: "456 Business Ave, Makati City" },
                { lat: 14.6348, lng: 121.0457, name: "J&T Branch - Quezon City", address: "789 Central Ave, Quezon City" }
            ];
            
            points.forEach(point => {
                const marker = L.marker([point.lat, point.lng], {
                    title: point.name
                }).addTo(homepageMap);
                
                marker.bindPopup(`
                    <div style="padding: 10px;">
                        <h4 style="margin: 0 0 8px 0; color: #dc2626;">${point.name}</h4>
                        <p style="margin: 0 0 8px 0;">${point.address}</p>
                        <button onclick="getTestDirections('${point.address}')" 
                                style="background: #dc2626; color: white; border: none; padding: 6px 12px; 
                                       border-radius: 4px; cursor: pointer;">
                            Get Directions
                        </button>
                    </div>
                `);
            });
        }
        
        function getTestDirections(address) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const origin = position.coords.latitude + ',' + position.coords.longitude;
                        const destination = encodeURIComponent(address);
                        window.open(`https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}`, '_blank');
                    },
                    function(error) {
                        window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(address), '_blank');
                    }
                );
            } else {
                window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(address), '_blank');
            }
        }
    </script>