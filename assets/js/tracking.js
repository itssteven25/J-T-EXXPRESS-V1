// Tracking-specific JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initTrackingForm();
    initTrackingAnimations();
});

// Initialize tracking form functionality
function initTrackingForm() {
    const trackingForm = document.querySelector('.tracking-form');
    const trackingInput = document.getElementById('tracking-input');
    
    if (trackingForm) {
        trackingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const trackingNumber = trackingInput.value.trim();
            
            if (trackingNumber) {
                performTracking(trackingNumber);
            } else {
                showToast('Please enter a tracking number', 'warning');
                trackingInput.focus();
            }
        });
    }
    
    // Add input validation
    if (trackingInput) {
        trackingInput.addEventListener('input', function() {
            // Auto-format tracking number
            let value = this.value.toUpperCase();
            // Remove any non-alphanumeric characters except common separators
            value = value.replace(/[^A-Z0-9\-_]/g, '');
            this.value = value;
        });
        
        // Auto-submit on valid format
        trackingInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.length >= 8) {
                trackingForm.dispatchEvent(new Event('submit'));
            }
        });
    }
}

// Perform tracking lookup
function performTracking(trackingNumber) {
    showProgress();
    
    // Simulate API call
    setTimeout(() => {
        hideProgress();
        
        // In a real application, this would make an AJAX request
        // For demo purposes, we'll redirect to the tracking page
        window.location.href = `track.php?tracking=${encodeURIComponent(trackingNumber)}`;
    }, 1000);
}

// Tracking animations
function initTrackingAnimations() {
    // Animate status cards
    const statusCards = document.querySelectorAll('.status-card');
    statusCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Animate timeline steps
    const timelineSteps = document.querySelectorAll('.timeline-step');
    timelineSteps.forEach((step, index) => {
        step.style.opacity = '0';
        step.style.transform = 'translateX(-20px)';
        step.style.transition = 'all 0.5s ease';
        
        setTimeout(() => {
            step.style.opacity = '1';
            step.style.transform = 'translateX(0)';
        }, index * 200);
    });
}

// Validate tracking number format
function validateTrackingNumber(trackingNumber) {
    // Basic validation rules
    const minLength = 8;
    const maxLength = 20;
    const validPattern = /^[A-Z0-9\-_]+$/;
    
    if (trackingNumber.length < minLength) {
        return { valid: false, message: `Tracking number must be at least ${minLength} characters` };
    }
    
    if (trackingNumber.length > maxLength) {
        return { valid: false, message: `Tracking number cannot exceed ${maxLength} characters` };
    }
    
    if (!validPattern.test(trackingNumber)) {
        return { valid: false, message: 'Tracking number can only contain letters, numbers, hyphens, and underscores' };
    }
    
    return { valid: true, message: 'Valid tracking number' };
}

// Show tracking history
function showTrackingHistory(trackingNumber) {
    const historyContainer = document.getElementById('tracking-history');
    if (!historyContainer) return;
    
    showProgress();
    
    fetch(`../api/tracking-history.php?tracking=${trackingNumber}`)
        .then(response => response.json())
        .then(data => {
            hideProgress();
            displayTrackingHistory(data);
        })
        .catch(error => {
            hideProgress();
            showToast('Error loading tracking history', 'error');
            console.error('Error:', error);
        });
}

function displayTrackingHistory(historyData) {
    const historyContainer = document.getElementById('tracking-history');
    if (!historyContainer) return;
    
    if (!historyData || historyData.length === 0) {
        historyContainer.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>No Tracking History</h3>
                <p>Tracking history will be available once your shipment is processed.</p>
            </div>
        `;
        return;
    }
    
    let historyHTML = '<div class="history-timeline">';
    historyData.forEach((event, index) => {
        historyHTML += `
            <div class="history-event ${index === 0 ? 'current' : ''}">
                <div class="event-dot"></div>
                <div class="event-content">
                    <div class="event-title">${event.status}</div>
                    <div class="event-description">${event.description}</div>
                    <div class="event-time">${formatDateTime(event.timestamp)}</div>
                    <div class="event-location">${event.location}</div>
                </div>
            </div>
        `;
    });
    historyHTML += '</div>';
    
    historyContainer.innerHTML = historyHTML;
}

// Format date and time
function formatDateTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Share tracking information
function shareTrackingInfo(trackingNumber) {
    const shareData = {
        title: 'J&T Express Shipment Tracking',
        text: `Track your shipment: ${trackingNumber}`,
        url: `${window.location.origin}/tracking/track.php?tracking=${trackingNumber}`
    };
    
    if (navigator.share) {
        navigator.share(shareData)
            .catch(error => console.log('Error sharing:', error));
    } else {
        // Fallback: copy to clipboard
        copyToClipboard(shareData.url);
        showToast('Tracking link copied to clipboard', 'success');
    }
}

// Copy text to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
}

// Export tracking functions
window.JTExpressTracking = {
    performTracking,
    validateTrackingNumber,
    showTrackingHistory,
    shareTrackingInfo,
    copyToClipboard
};