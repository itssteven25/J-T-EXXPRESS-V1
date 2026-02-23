<?php
session_start();
include 'includes/db.php';

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access - J&T Express</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-access-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            padding: 20px;
        }
        
        .admin-access-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .admin-logo {
            margin-bottom: 30px;
        }
        
        .admin-logo h1 {
            color: #dc2626;
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .admin-logo p {
            color: #6b7280;
            font-size: 18px;
        }
        
        .admin-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 30px 0;
        }
        
        .admin-btn {
            padding: 16px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }
        
        .admin-btn:hover {
            background: #b91c1c;
        }
        
        .admin-btn-secondary {
            background: #6b7280;
        }
        
        .admin-btn-secondary:hover {
            background: #4b5563;
        }
        
        .admin-info {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: left;
        }
        
        .admin-info h3 {
            color: #1f2937;
            margin-top: 0;
        }
        
        .credentials {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .credential-item {
            margin: 8px 0;
            font-family: monospace;
        }
        
        .credential-label {
            font-weight: 600;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="admin-access-container">
        <div class="admin-access-card">
            <div class="admin-logo">
                <h1>🔧 J&T EXPRESS</h1>
                <p>Administrator Portal</p>
            </div>
            
            <div class="admin-options">
                <a href="admin/login.php" class="admin-btn">🔐 Admin Login</a>
                <a href="auth/login.php" class="admin-btn admin-btn-secondary">👤 User Login</a>
            </div>
            
            <div class="admin-info">
                <h3>Admin Access Information</h3>
                <p>Secure administrator portal for managing the J&T Express logistics system.</p>
                
                <div class="credentials">
                    <h4>Default Admin Credentials:</h4>
                    <div class="credential-item">
                        <span class="credential-label">Username:</span> admin
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password:</span> admin123
                    </div>
                </div>
                
                <p><strong>Features Available:</strong></p>
                <ul>
                    <li>📊 Dashboard with real-time statistics</li>
                    <li>📋 Booking management and approval</li>
                    <li>🚚 Shipment status updates</li>
                    <li>👥 User account management</li>
                    <li>🏢 Branch location management</li>
                    <li>💰 Shipping rate configuration</li>
                    <li>📊 Reports and analytics</li>
                    <li>🔒 Secure session management</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>