<?php
session_start();
include '../includes/db.php';

$error = '';
$login_attempts = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;
$lockout_time = isset($_SESSION['lockout_time']) ? $_SESSION['lockout_time'] : 0;

// Check if account is locked
if ($login_attempts >= 5 && time() < $lockout_time) {
    $remaining_time = ceil(($lockout_time - time()) / 60);
    $error = "Account temporarily locked. Please try again in $remaining_time minutes.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $sql = "SELECT id, username, password, email FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Reset login attempts on successful login
                unset($_SESSION['login_attempts']);
                unset($_SESSION['lockout_time']);
                
                // Set session variables
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
                
                // Redirect to dashboard
                header("Location: ../dashboard/index.php");
                exit();
            } else {
                // Invalid password
                $error = "Invalid username or password";
            }
        } else {
            // User not found
            $error = "Invalid username or password";
        }
        
        // Increment login attempts on failure
        if (!empty($error)) {
            $_SESSION['login_attempts'] = $login_attempts + 1;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lockout_time'] = time() + (15 * 60); // 15 minute lockout
                $error = "Too many failed attempts. Account locked for 15 minutes.";
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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <h1>J&T EXPRESS</h1>
                </div>
                <div class="profile-placeholder">
                    <div class="profile-circle"></div>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group forgot-password">
                    <a href="#">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
                <a href="register.php" class="btn btn-secondary">Signup</a>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <button class="btn btn-facebook">
                Login with Facebook
            </button>
        </div>
    </div>
</body>
</html>