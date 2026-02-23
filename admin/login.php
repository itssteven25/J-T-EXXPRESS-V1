<?php
// Quick Admin Login Fix
session_start();
include '../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Ensure admin tables exist
$admin_users_table = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('super_admin', 'admin', 'support') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
)";

$admin_logs_table = "CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id)
)";

$conn->query($admin_users_table);
$conn->query($admin_logs_table);

// Ensure admin user exists
$check_admin = "SELECT id, username, password FROM admin_users WHERE username='admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $insert = $conn->query("INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$admin_hash', 'admin@jntexpress.com', 'super_admin')");
    if ($insert) {
        $error = "Admin user created. Please login with username: admin, password: admin123";
    }
} else {
    $admin = $result->fetch_assoc();
    if (!password_verify('admin123', $admin['password'])) {
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("UPDATE admin_users SET password = '$new_hash' WHERE username = 'admin'");
        $error = "Admin password updated. Please login with username: admin, password: admin123";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        // Check admin credentials
        $sql = "SELECT id, username, password, email, role FROM admin_users WHERE username = ? AND status = 'active'";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    // Set admin session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['admin_login_time'] = time();
                    
                    // Update last login
                    $conn->query("UPDATE admin_users SET last_login = NOW() WHERE id = " . $admin['id']);
                    
                    // Log admin activity
                    $log_sql = "INSERT INTO admin_logs (admin_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    if ($log_stmt) {
                        $action = "admin_login";
                        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                        $log_stmt->bind_param("isss", $admin['id'], $action, $ip_address, $user_agent);
                        $log_stmt->execute();
                    }
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            padding: 20px;
        }
        
        .admin-login-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .admin-logo {
            margin-bottom: 30px;
        }
        
        .admin-logo h1 {
            color: #dc2626;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .admin-logo p {
            color: #6b7280;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #dc2626;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: #b91c1c;
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }
        
        .success-message {
            background: #d1fae5;
            color: #059669;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #a7f3d0;
        }
        
        .admin-footer {
            margin-top: 25px;
            color: #6b7280;
            font-size: 14px;
        }
        
        .credentials-box {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
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
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-logo">
                <h1>J&T EXPRESS</h1>
                <p>Administrator Portal</p>
            </div>
            
            <?php if ($error): ?>
                <?php if (strpos($error, 'created') !== false || strpos($error, 'updated') !== false): ?>
                    <div class="success-message"><?php echo htmlspecialchars($error); ?></div>
                <?php else: ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Login to Admin Panel</button>
            </form>
            
            <div class="credentials-box">
                <h4>Default Admin Credentials:</h4>
                <div class="credential-item">
                    <span class="credential-label">Username:</span> admin
                </div>
                <div class="credential-item">
                    <span class="credential-label">Password:</span> admin123
                </div>
            </div>
            
            <div class="admin-footer">
                <p>🔒 Secure Administrator Access Only</p>
            </div>
        </div>
    </div>
</body>
</html>