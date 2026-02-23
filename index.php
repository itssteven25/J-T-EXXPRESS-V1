<?php
// J&T Express Main Homepage - Public Access
session_start();

// Include database connection
include 'includes/db.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>J&T Express - Fast & Reliable Courier Services</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="assets/css/additional_styles.css">
</head>
<body onload="initHomepageMap()">
    <!-- Header Navigation -->
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <h1>J&T EXPRESS</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if ($is_logged_in): ?>
                        <li><a href="dashboard/index.php" class="btn btn-primary">Dashboard</a></li>
                        <li><a href="auth/logout.php" class="btn btn-secondary">Logout</a></li>
                    <?php else: ?>
                        <li><a href="auth/login.php" class="btn btn-primary">Login</a></li>
                        <li><a href="auth/register.php" class="btn btn-secondary">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Fast & Reliable Courier Services</h1>
                <p>Experience seamless logistics with J&T Express. Track your shipments, book pickups, and calculate rates in real-time.</p>
                
                <!-- Quick Access Cards -->
                <div class="quick-access-grid">
                    <div class="quick-card" onclick="window.location.href='tracking/track.php'">
                        <div class="card-icon">🔍</div>
                        <h3>Track Shipment</h3>
                        <p>Real-time tracking of your packages</p>
                    </div>
                    
                    <div class="quick-card" onclick="window.location.href='pickup/package-pickup.php'">
                        <div class="card-icon">📦</div>
                        <h3>Book Pickup</h3>
                        <p>Schedule package collection</p>
                    </div>
                    
                    <div class="quick-card" onclick="window.location.href='rates/shipping-rates.php'">
                        <div class="card-icon">💰</div>
                        <h3>Shipping Rates</h3>
                        <p>Calculate shipping costs</p>
                    </div>
                    
                    <div class="quick-card" onclick="window.location.href='drop-points/drop-points.php'">
                        <div class="card-icon">📍</div>
                        <h3>Branch Locator</h3>
                        <p>Find nearest drop points</p>
                    </div>
                    
                    <div class="quick-card" onclick="window.location.href='auth/login.php'">
                        <div class="card-icon">👤</div>
                        <h3>User Login</h3>
                        <p>Access your account</p>
                    </div>
                    
                    <div class="quick-card" onclick="window.location.href='unified_login.php?type=admin'">
                        <div class="card-icon">🔧</div>
                        <h3>Admin Login</h3>
                        <p>Administrator access</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="section-container">
            <h2>Our Services</h2>
            <div class="services-grid">
                <div class="service-item">
                    <div class="service-icon">🚚</div>
                    <h3>Express Delivery</h3>
                    <p>Fast and reliable delivery within 1-2 business days</p>
                </div>
                
                <div class="service-item">
                    <div class="service-icon">🌍</div>
                    <h3>International Shipping</h3>
                    <p>Global shipping services to over 200 countries</p>
                </div>
                
                <div class="service-item">
                    <div class="service-icon">🛡️</div>
                    <h3>Insurance Coverage</h3>
                    <p>Protect your valuable items with our insurance options</p>
                </div>
                
                <div class="service-item">
                    <div class="service-icon">📱</div>
                    <h3>Mobile Tracking</h3>
                    <p>Track your shipments anytime, anywhere</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tracking Section -->
    <section class="tracking-section">
        <div class="section-container">
            <h2>Track Your Shipment</h2>
            <div class="tracking-card">
                <form method="GET" action="tracking/track.php" class="tracking-form">
                    <div class="input-group">
                        <input type="text" name="tracking" placeholder="Enter tracking number (e.g. JT123456789)" required>
                        <button type="submit" class="btn btn-primary">Track Now</button>
                    </div>
                </form>
                <div class="tracking-tips">
                    <p>💡 Tracking numbers are usually 9-12 characters long</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Nearby Drop Points Map -->
    <section class="map-section">
        <div class="section-container">
            <h2>Find Nearest Drop Points</h2>
            <div class="map-card">
                <div class="map-header">
                    <p>Locate the closest J&T Express drop-off points near you</p>
                    <button class="btn btn-secondary" onclick="findNearbyPoints()">Find Nearby Points</button>
                </div>
                <div id="homepage-map" style="height: 300px; width: 100%; border-radius: 12px; margin-top: 20px;"></div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>J&T Express</h3>
                    <p>Fast, reliable, and secure courier services for all your shipping needs.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="tracking/track.php">Track Shipment</a></li>
                        <li><a href="rates/shipping-rates.php">Shipping Rates</a></li>
                        <li><a href="drop-points/drop-points.php">Branch Locator</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p>📞 Customer Service: 1-800-JT-EXPRESS</p>
                    <p>📧 Email: support@jntexpress.com</p>
                    <p>🕒 Operating Hours: 24/7</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 J&T Express. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    
    <script>
        // Initialize homepage map
        let homepageMap;
        let userMarker;
        let pointMarkers = [];
        
        function initHomepageMap() {
            // Default center (Manila, Philippines)
            const defaultCenter = [14.5995, 120.9842];
            
            homepageMap = L.map('homepage-map').setView(defaultCenter, 11);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(homepageMap);
            
            // Add sample drop point markers
            const samplePoints = [
                { lat: 14.5995, lng: 120.9842, name: "J&T Main Branch - Manila", address: "123 Main Street, Manila" },
                { lat: 14.5547, lng: 121.0244, name: "J&T Branch - Makati", address: "456 Business Ave, Makati City" },
                { lat: 14.6348, lng: 121.0457, name: "J&T Branch - Quezon City", address: "789 Central Ave, Quezon City" },
                { lat: 14.5361, lng: 121.0221, name: "J&T Hub - Pasay", address: "101 Airport Road, Pasay City" },
                { lat: 14.4987, lng: 121.0143, name: "J&T Terminal - Paranaque", address: "202 Harbor Blvd, Paranaque City" }
            ];
            
            samplePoints.forEach(point => {
                addPointMarker(point);
            });
        }
        
        function addPointMarker(point) {
            const marker = L.marker([point.lat, point.lng], {
                title: point.name
            }).addTo(homepageMap);
            
            marker.bindPopup(`
                <div style="padding: 10px; max-width: 200px;">
                    <h4 style="margin: 0 0 8px 0; color: #dc2626;">${point.name}</h4>
                    <p style="margin: 0 0 8px 0; font-size: 14px;">${point.address}</p>
                    <button onclick="getHomepageDirections('${point.address.replace(/'/g, "\\'")}')" 
                            style="background: #dc2626; color: white; border: none; padding: 6px 12px; 
                                   border-radius: 4px; cursor: pointer; font-size: 12px;">
                        Get Directions
                    </button>
                </div>
            `);
            
            pointMarkers.push(marker);
        }
        
        function findNearbyPoints() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLocation = [position.coords.latitude, position.coords.longitude];
                        
                        // Center map on user location
                        homepageMap.setView(userLocation, 13);
                        
                        // Add user marker
                        if (userMarker) {
                            homepageMap.removeLayer(userMarker);
                        }
                        
                        userMarker = L.marker(userLocation, {
                            title: 'Your Location'
                        }).addTo(homepageMap);
                        
                        userMarker.bindPopup('<div style="padding: 10px;"><strong>Your Current Location</strong></div>');
                        
                    },
                    function(error) {
                        alert('Unable to get your location. Please enable location services.');
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        }
        
        function getHomepageDirections(address) {
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
</body>
</html>