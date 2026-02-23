<?php
session_start();
include '../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

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
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Set admin session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_login_time'] = time();
                
                // Log admin activity
                $log_sql = "INSERT INTO admin_logs (admin_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)";
                $log_stmt = $conn->prepare($log_sql);
                $action = "admin_login";
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                $log_stmt->bind_param("isss", $admin['id'], $action, $ip_address, $user_agent);
                $log_stmt->execute();
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
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
        
        .admin-form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .admin-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .admin-form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .admin-form-group input:focus {
            outline: none;
            border-color: #dc2626;
        }
        
        .admin-btn {
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
        
        .admin-btn:hover {
            background: #b91c1c;
        }
        
        .admin-error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }
        
        .admin-footer {
            margin-top: 25px;
            color: #6b7280;
            font-size: 14px;
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
                <div class="admin-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="admin-form-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="admin-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="admin-btn">Login to Admin Panel</button>
            </form>
            
            <div class="admin-footer">
                <p>🔒 Secure Administrator Access Only</p>
            </div>
        </div>
    </div>
</body>
</html>