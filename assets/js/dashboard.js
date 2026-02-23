// Dashboard-specific JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initDashboardCharts();
    initShipmentTimeline();
    initAutoRefresh();
});

// Initialize dashboard charts and visualizations
function initDashboardCharts() {
    // This would integrate with charting libraries like Chart.js
    // For now, we'll just add some basic visualization logic
    
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Shipment timeline animation
function initShipmentTimeline() {
    const timelineSteps = document.querySelectorAll('.timeline-step');
    
    timelineSteps.forEach((step, index) => {
        // Add delay for staggered animation
        setTimeout(() => {
            if (step.classList.contains('completed')) {
                step.style.opacity = '1';
                step.style.transform = 'translateX(0)';
            }
        }, index * 300);
    });
}

// Auto-refresh functionality for dashboard
function initAutoRefresh() {
    // Refresh dashboard data every 5 minutes
    setInterval(() => {
        if (window.location.pathname.includes('dashboard')) {
            refreshDashboardData();
        }
    }, 300000); // 5 minutes
}

function refreshDashboardData() {
    // Make AJAX calls to update dashboard data
    console.log('Refreshing dashboard data...');
    
    // Example: Update shipment counts
    fetch('../api/dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
            updateStats(data);
            showToast('Dashboard updated successfully', 'success');
        })
        .catch(error => {
            console.error('Error refreshing data:', error);
            showToast('Error refreshing dashboard', 'error');
        });
}

function updateStats(data) {
    // Update stat cards with new data
    const statCards = document.querySelectorAll('.stat-card .stat-info h3');
    if (statCards.length >= 4) {
        statCards[0].textContent = data.total_shipments || 0;
        statCards[1].textContent = data.in_transit || 0;
        statCards[2].textContent = data.delivered || 0;
        statCards[3].textContent = data.pick_up || 0;
    }
}

// Shipment selection functionality
function selectShipment(trackingNumber) {
    // Highlight selected shipment
    const shipmentItems = document.querySelectorAll('.shipment-item');
    shipmentItems.forEach(item => {
        item.classList.remove('active');
    });
    
    const selectedItem = document.querySelector(`[data-tracking="${trackingNumber}"]`);
    if (selectedItem) {
        selectedItem.classList.add('active');
    }
    
    // Load shipment details
    loadShipmentDetails(trackingNumber);
}

function loadShipmentDetails(trackingNumber) {
    showProgress();
    
    fetch(`../api/shipment-details.php?tracking=${trackingNumber}`)
        .then(response => response.json())
        .then(data => {
            hideProgress();
            displayShipmentDetails(data);
        })
        .catch(error => {
            hideProgress();
            showToast('Error loading shipment details', 'error');
            console.error('Error:', error);
        });
}

function displayShipmentDetails(data) {
    // Update the details panel with shipment data
    const detailsPanel = document.querySelector('.shipment-details');
    if (detailsPanel && data) {
        detailsPanel.innerHTML = `
            <div class="details-header">
                <h2>Shipment Details</h2>
                <div class="tracking-display">
                    Tracking Number: <strong>${data.tracking_number}</strong>
                </div>
            </div>
            <!-- Add more details here -->
        `;
    }
}

// Export dashboard functions
window.JTExpressDashboard = {
    selectShipment,
    loadShipmentDetails,
    refreshDashboardData
};