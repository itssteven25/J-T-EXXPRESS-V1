<?php
session_start();
include 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit();
}

$error = '';
$login_type = $_GET['type'] ?? 'user'; // Default to user login

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $login_type = $_POST['login_type'] ?? 'user';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        if ($login_type == 'admin') {
            // Admin login
            $sql = "SELECT id, username, password, email, role FROM admin_users WHERE username = ? AND status = 'active'";
            $stmt = $conn->prepare($sql);
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
                    
                    // Log admin activity
                    $log_sql = "INSERT INTO admin_logs (admin_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $action = "admin_login";
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                    $log_stmt->bind_param("isss", $admin['id'], $action, $ip_address, $user_agent);
                    $log_stmt->execute();
                    
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid admin credentials";
                }
            } else {
                $error = "Admin account not found or inactive";
            }
        } else {
            // User login
            $sql = "SELECT id, username, password, email FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Set user session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['login_time'] = time();
                    
                    // Add to user history
                    $history_sql = "INSERT INTO user_history (user_id, activity_type, activity_description) VALUES (?, ?, ?)";
                    $history_stmt = $conn->prepare($history_sql);
                    $activity_type = "login";
                    $activity_description = "User logged in successfully";
                    $history_stmt->bind_param("iss", $user['id'], $activity_type, $activity_description);
                    $history_stmt->execute();
                    
                    header("Location: dashboard/index.php");
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Invalid username or password";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - J&T Express</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #dc2626;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #6b7280;
            font-size: 16px;
        }
        
        .login-type-selector {
            display: flex;
            margin-bottom: 25px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .login-type-option {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .login-type-option.active {
            background: #dc2626;
            color: white;
        }
        
        .login-type-option:not(.active) {
            background: white;
            color: #6b7280;
        }
        
        .login-type-option:not(.active):hover {
            background: #f9fafb;
        }
        
        .form-group {
            margin-bottom: 20px;
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
        
        .btn-secondary {
            background: #6b7280;
            margin-top: 15px;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #6b7280;
        }
        
        .signup-link a {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .admin-credentials {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .admin-credentials h4 {
            color: #92400e;
            margin-top: 0;
        }
        
        .credential-item {
            margin: 8px 0;
            font-family: monospace;
            font-size: 14px;
        }
        
        .credential-label {
            font-weight: 600;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>J&T EXPRESS</h1>
                <p>Fast & Reliable Courier Services</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="login_type" id="login_type" value="<?php echo $login_type; ?>">
                
                <div class="login-type-selector">
                    <div class="login-type-option <?php echo $login_type == 'user' ? 'active' : ''; ?>" 
                         onclick="setLoginType('user')">
                        👤 User Login
                    </div>
                    <div class="login-type-option <?php echo $login_type == 'admin' ? 'active' : ''; ?>" 
                         onclick="setLoginType('admin')">
                        🔧 Admin Login
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
                
                <div id="adminCredentials" class="admin-credentials" style="display: <?php echo $login_type == 'admin' ? 'block' : 'none'; ?>;">
                    <h4>Admin Credentials:</h4>
                    <div class="credential-item">
                        <span class="credential-label">Username:</span> admin
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password:</span> admin123
                    </div>
                </div>
            </form>
            
            <div class="signup-link">
                <p>Don't have an account? <a href="auth/register.php">Sign up here</a></p>
            </div>
        </div>
    </div>
    
    <script>
        function setLoginType(type) {
            document.getElementById('login_type').value = type;
            
            // Update active state
            const options = document.querySelectorAll('.login-type-option');
            options.forEach(option => option.classList.remove('active'));
            
            if (type === 'user') {
                options[0].classList.add('active');
                document.getElementById('adminCredentials').style.display = 'none';
            } else {
                options[1].classList.add('active');
                document.getElementById('adminCredentials').style.display = 'block';
            }
            
            // Update form action or other elements if needed
            updateFormForType(type);
        }
        
        function updateFormForType(type) {
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            
            // Clear fields when switching
            usernameField.value = '';
            passwordField.value = '';
            
            // You can add specific behavior for each login type here
            if (type === 'admin') {
                usernameField.placeholder = 'Admin username';
                passwordField.placeholder = 'Admin password';
            } else {
                usernameField.placeholder = 'Username or email';
                passwordField.placeholder = 'Password';
            }
        }
    </script>
</body>
</html>