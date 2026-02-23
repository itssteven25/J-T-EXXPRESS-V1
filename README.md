# J&T Express Logistics Dashboard System

A comprehensive logistics and parcel delivery web application with a modern dashboard UI for tracking shipments, managing deliveries, and accessing courier services.

## Features

### Authentication
- User login and registration system
- Session-based authentication
- Secure password handling

### Dashboard
- Overview statistics and metrics
- Quick access cards for main functions
- Recent shipments display
- Latest updates carousel

### Shipment Management
- All shipments table view with filtering
- My shipments with detailed timeline
- Status tracking (Pick Up, In Transit, Delivered)
- Search functionality

### Track & Trace
- Real-time shipment tracking
- Delivery progress timeline
- Status visualization
- Tracking number validation

### Responsive Design
- Mobile-friendly interface
- Collapsible sidebar navigation
- Adaptive layouts for all screen sizes
- Touch-friendly controls

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 8+
- **Database**: MySQL (via XAMPP)
- **Server**: Apache (via XAMPP)

## Setup Instructions

### Prerequisites
1. Install XAMPP (Apache, MySQL, PHP)
2. Start Apache and MySQL services in XAMPP Control Panel

### Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `jt_express`
3. Import the SQL schema from `config/database.sql`

### File Setup
1. Copy all project files to your XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\jt-express\
   ```
2. Ensure the directory structure matches:
   ```
   jt-express/
   ├── assets/
   ├── auth/
   ├── config/
   ├── dashboard/
   ├── includes/
   ├── tracking/
   ├── index.php
   └── ...
   ```

### Configuration
1. Update database credentials in `includes/db.php` if needed:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = ""; // Default XAMPP password is empty
   $dbname = "jt_express";
   ```

## Usage

### Default Login Credentials
- **Username**: admin
- **Password**: password
- **Email**: admin@jntexpress.com

### Navigation
1. Access the application: http://localhost/jt-express/
2. Login with default credentials
3. Explore the dashboard features:
   - View shipment statistics
   - Track packages using the search bar
   - Navigate through shipments
   - View detailed tracking information

### Key Pages
- **Login**: `/auth/login.php`
- **Dashboard**: `/dashboard/index.php`
- **All Shipments**: `/dashboard/shipments.php`
- **My Shipments**: `/dashboard/my-shipments.php`
- **Track & Trace**: `/tracking/track.php`

## Project Structure

```
jt-express/
├── assets/
│   ├── css/
│   │   ├── style.css          # Main styles
│   │   └── dashboard.css      # Dashboard-specific styles
│   └── js/
│       ├── main.js            # Core JavaScript
│       ├── dashboard.js       # Dashboard functionality
│       └── tracking.js        # Tracking features
├── auth/
│   ├── login.php              # Login page
│   ├── register.php           # Registration page
│   └── logout.php             # Logout functionality
├── config/
│   └── database.sql           # Database schema
├── dashboard/
│   ├── index.php              # Main dashboard
│   ├── shipments.php          # All shipments view
│   └── my-shipments.php       # My shipments timeline
├── includes/
│   ├── db.php                 # Database connection
│   ├── header.php             # Top navigation
│   ├── sidebar.php            # Sidebar navigation
│   └── footer.php             # Footer
├── tracking/
│   └── track.php              # Track & trace page
└── index.php                  # Main entry point
```

## Customization

### Styling
- Modify colors in `assets/css/style.css`
- Primary color: `#dc2626` (courier red)
- Update fonts and typography as needed

### Functionality
- Add new shipment statuses in database
- Extend tracking features
- Add new dashboard widgets
- Implement additional filters

## Security Notes

- This is a demonstration system
- In production, implement proper password hashing
- Add CSRF protection
- Implement input validation and sanitization
- Use prepared statements for all database queries
- Add rate limiting for login attempts

## Troubleshooting

### Common Issues
1. **Database Connection Error**: Check XAMPP MySQL service is running
2. **404 Errors**: Verify file paths and Apache configuration
3. **CSS/JS Not Loading**: Check file permissions and paths
4. **Login Issues**: Verify database connection and user table

### Debug Mode
Add this to the top of PHP files for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Future Enhancements

- Integration with real tracking APIs
- User role management
- Advanced reporting and analytics
- Mobile app development
- Multi-language support
- Integration with shipping carriers
- Automated notifications
- Barcode scanning functionality

## Support

For issues and questions, please contact the development team or check the documentation.

---
© 2026 J&T Express. All rights reserved.