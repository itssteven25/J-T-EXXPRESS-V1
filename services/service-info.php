<?php
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Fetch active service info
$services_sql = "SELECT * FROM service_info WHERE is_active = 1 ORDER BY category, title";
$services_result = $conn->query($services_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Info - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Service Information</h1>
            </div>
            
            <div class="services-container">
                <div class="services-intro">
                    <p>Learn about our services, policies, and how we can help with your shipping needs.</p>
                </div>
                
                <div class="services-categories">
                    <div class="category-filters">
                        <button class="filter-btn active" data-category="all">All Services</button>
                        <button class="filter-btn" data-category="services">Services</button>
                        <button class="filter-btn" data-category="promos">Promotions</button>
                        <button class="filter-btn" data-category="policies">Policies</button>
                    </div>
                    
                    <div class="services-grid">
                        <?php while($service = $services_result->fetch_assoc()): ?>
                        <div class="service-card" data-category="<?php echo strtolower($service['category']); ?>">
                            <div class="service-icon">📦</div>
                            <h3><?php echo $service['title']; ?></h3>
                            <p><?php echo $service['content']; ?></p>
                            <span class="service-tag"><?php echo $service['category']; ?></span>
                        </div>
                        <?php endwhile; ?>
                        
                        <!-- Additional service info that wasn't in the DB -->
                        <div class="service-card" data-category="policies">
                            <div class="service-icon">🔒</div>
                            <h3>Return Policy</h3>
                            <p>Items can be returned within 7 days of delivery. Contact customer support for return instructions.</p>
                            <span class="service-tag">Policies</span>
                        </div>
                        
                        <div class="service-card" data-category="services">
                            <div class="service-icon">🚚</div>
                            <h3>Overnight Delivery</h3>
                            <p>Get your packages delivered the next business day. Available for Metro Manila areas only.</p>
                            <span class="service-tag">Services</span>
                        </div>
                        
                        <div class="service-card" data-category="policies">
                            <div class="service-icon">⚖️</div>
                            <h3>Terms of Service</h3>
                            <p>By using our services, you agree to our terms and conditions. Please read carefully before shipping.</p>
                            <span class="service-tag">Policies</span>
                        </div>
                    </div>
                </div>
                
                <div class="faq-section">
                    <h3>Frequently Asked Questions</h3>
                    <div class="faq-item">
                        <h4>How long does shipping take?</h4>
                        <p>Standard shipping takes 3-5 business days, while express shipping takes 1-2 business days depending on the destination.</p>
                    </div>
                    <div class="faq-item">
                        <h4>What items cannot be shipped?</h4>
                        <p>Perishable goods, hazardous materials, and illegal items cannot be shipped through our service.</p>
                    </div>
                    <div class="faq-item">
                        <h4>Do you offer insurance for valuable items?</h4>
                        <p>Yes, we offer insurance coverage for valuable items at an additional 3% of the declared value.</p>
                    </div>
                    <div class="faq-item">
                        <h4>Can I track my shipment?</h4>
                        <p>Yes, all shipments can be tracked using the tracking number provided in your confirmation email.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        // Service category filtering
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                const category = this.getAttribute('data-category');
                
                // Show/hide service cards based on category
                document.querySelectorAll('.service-card').forEach(card => {
                    if (category === 'all' || card.getAttribute('data-category') === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>