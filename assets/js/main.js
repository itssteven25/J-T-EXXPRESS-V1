// Main JavaScript for J&T Express Dashboard

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSidebar();
    initSearch();
    initFilters();
    initMobileMenu();
    initTooltips();
    
    // Add fade-in animation to main content
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.classList.add('fade-in');
    }
});

// Sidebar functionality
function initSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarLinks = document.querySelectorAll('.nav-link');
    
    // Add active state to current page
    sidebarLinks.forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
    
    // Sidebar hover effects for collapsed state
    if (sidebar) {
        sidebar.addEventListener('mouseenter', function() {
            if (window.innerWidth <= 1024) {
                this.style.width = '250px';
                const navTexts = this.querySelectorAll('.nav-text');
                navTexts.forEach(text => text.style.display = 'inline');
            }
        });
        
        sidebar.addEventListener('mouseleave', function() {
            if (window.innerWidth <= 1024) {
                this.style.width = '70px';
                const navTexts = this.querySelectorAll('.nav-text');
                navTexts.forEach(text => text.style.display = 'none');
            }
        });
    }
}

// Search functionality
function initSearch() {
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.querySelector('.search-btn');
    
    if (searchInput && searchBtn) {
        // Search on button click
        searchBtn.addEventListener('click', function() {
            performSearch(searchInput.value);
        });
        
        // Search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch(this.value);
            }
        });
        
        // Live search suggestions (optional)
        searchInput.addEventListener('input', function() {
            // Could implement autocomplete here
        });
    }
}

function performSearch(query) {
    if (query.trim()) {
        // Redirect to tracking page with search query
        window.location.href = `../tracking/track.php?tracking=${encodeURIComponent(query)}`;
    }
}

// Filter functionality
function initFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const statusFilter = document.getElementById('status-filter');
    
    // Status filter dropdown
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterShipmentsByStatus(this.value);
        });
    }
    
    // Other filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterType = this.dataset.filter;
            applyFilter(filterType);
        });
    });
}

function filterShipmentsByStatus(status) {
    const tableRows = document.querySelectorAll('.shipments-table tbody tr');
    
    tableRows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(3) .status-badge');
        if (status === '' || statusCell.textContent.trim() === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function applyFilter(filterType) {
    // Implement custom filter logic
    console.log('Applying filter:', filterType);
}

// Mobile menu functionality
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    
    if (menuToggle && sidebar) {
        // Add overlay to DOM
        document.body.appendChild(overlay);
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        });
        
        // Close menu when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            this.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        // Close menu when clicking nav links on mobile
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

// Tooltip functionality
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            showTooltip(this, tooltipText);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

function showTooltip(element, text) {
    // Create tooltip element
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    tooltip.style.position = 'absolute';
    tooltip.style.background = '#374151';
    tooltip.style.color = 'white';
    tooltip.style.padding = '6px 12px';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '12px';
    tooltip.style.zIndex = '1000';
    tooltip.style.pointerEvents = 'none';
    
    document.body.appendChild(tooltip);
    
    // Position tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
}

function hideTooltip() {
    const tooltip = document.querySelector('.custom-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Form validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input, select, textarea');
    let isValid = true;
    
    inputs.forEach(input => {
        if (input.hasAttribute('required') && !input.value.trim()) {
            showError(input, 'This field is required');
            isValid = false;
        } else if (input.type === 'email' && input.value && !isValidEmail(input.value)) {
            showError(input, 'Please enter a valid email address');
            isValid = false;
        } else if (input.type === 'password' && input.value && input.value.length < 6) {
            showError(input, 'Password must be at least 6 characters');
            isValid = false;
        } else {
            clearError(input);
        }
    });
    
    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showError(input, message) {
    clearError(input);
    input.classList.add('error');
    input.parentNode.style.position = 'relative';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.style.position = 'absolute';
    errorDiv.style.left = '0';
    errorDiv.style.top = '100%';
    
    input.parentNode.appendChild(errorDiv);
}

function clearError(input) {
    input.classList.remove('error');
    const existingError = input.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
}

// Toast notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type} show`;
    toast.innerHTML = `
        <div class="toast-content">
            <span>${message}</span>
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Progress indicator for AJAX requests
function showProgress() {
    const progress = document.createElement('div');
    progress.id = 'progress-indicator';
    progress.className = 'loading-spinner';
    progress.style.position = 'fixed';
    progress.style.top = '50%';
    progress.style.left = '50%';
    progress.style.transform = 'translate(-50%, -50%)';
    progress.style.zIndex = '9999';
    document.body.appendChild(progress);
}

function hideProgress() {
    const progress = document.getElementById('progress-indicator');
    if (progress) {
        progress.remove();
    }
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Function to update shipment status
function updateShipmentStatus(trackingNumber, newStatus) {
    showProgress();
    
    fetch('../api/update-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            tracking_number: trackingNumber,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        if (data.success) {
            showToast(data.message, 'success');
            // Reload the page or update the UI as needed
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        hideProgress();
        showToast('Error updating status: ' + error.message, 'error');
        console.error('Error:', error);
    });
}

// Export functions for global use
window.JTExpress = {
    validateForm,
    showToast,
    showProgress,
    hideProgress,
    formatDate,
    formatTime,
    debounce,
    updateShipmentStatus
};