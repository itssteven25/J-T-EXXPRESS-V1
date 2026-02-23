# J&T Express Core System (70% Version)

This is a streamlined 70% version of the J&T Express logistics system, focusing on the essential features for day-to-day operations.

## Core Features Included (70%)

### 1. Authentication System ✅
- User login/logout functionality
- Session management
- Secure password handling

### 2. Dashboard ✅
- Overview statistics and metrics
- Quick access cards for main functions
- Recent shipments display
- User profile summary

### 3. Shipment Tracking ✅
- Package tracking by number
- Delivery progress timeline
- Status visualization
- Tracking number validation

### 4. Shipment Management ✅
- View all shipments with filtering
- Status tracking (Pick Up, In Transit, Delivered)
- Update shipment status
- Direct tracking links

### 5. User Account Management ✅
- Profile information editing
- Contact details management
- Account summary statistics
- Personal information storage

### 6. Support System ✅
- Contact options (Phone, Email)
- Support ticket submission
- Ticket history tracking
- Priority levels
- FAQ section

## What's Excluded (30%)

The following advanced features are not included in this 70% version:
- Shipping rates calculator
- Package pickup scheduling
- Drop points locator
- Detailed service information
- Activity history tracking
- Advanced notifications
- Comprehensive settings

## File Structure

```
core/
├── index.php              # Main entry point
├── core-dashboard.php     # Main dashboard
├── core-tracking.php      # Shipment tracking
├── core-shipments.php     # Shipment management
├── core-account.php       # User account
├── core-support.php       # Support center
├── core-header.php        # Simplified header
├── core-sidebar.php       # Simplified navigation
└── README.md             # This file
```

## How to Use

1. Access the core system at: `http://localhost/J&T%20XXPRESS%20V1/core/`
2. Login with your credentials
3. Use the simplified navigation to access core features
4. All essential shipment management functions are available

## Key Benefits

- **Faster Performance**: Streamlined codebase with reduced complexity
- **Easier Maintenance**: Simplified architecture and fewer dependencies
- **Focus on Essentials**: Core business functionality prioritized
- **User-Friendly**: Clean, intuitive interface
- **Fully Functional**: All core features work as expected

## Database Integration

The core system uses the same database as the full system:
- User authentication and profiles
- Shipment tracking and management
- Support ticket system
- All data persists between systems

## Migration Notes

Data created in the core system is fully compatible with the full system and vice versa. You can switch between systems without losing any information.