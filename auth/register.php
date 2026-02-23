<?php
session_start();
include '../includes/db.php';

$error = '';
$success = '';
$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form validation
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Required field validation
    $required_fields = ['username', 'password', 'confirm_password', 'email', 'first_name', 'last_name'];
    foreach ($required_fields as $field) {
        if (empty($$field)) {
            $form_errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // Username validation
    if (!empty($username)) {
        if (strlen($username) < 3) {
            $form_errors['username'] = 'Username must be at least 3 characters long';
        } elseif (strlen($username) > 50) {
            $form_errors['username'] = 'Username must be less than 50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $form_errors['username'] = 'Username can only contain letters, numbers, and underscores';
        }
    }
    
    // Email validation
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form_errors['email'] = 'Please enter a valid email address';
        }
    }
    
    // Password validation
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $form_errors['password'] = 'Password must be at least 8 characters long';
        }
    }
    
    // Password confirmation
    if (!empty($password) && !empty($confirm_password) && $password !== $confirm_password) {
        $form_errors['confirm_password'] = 'Passwords do not match';
    }
    
    // Check if username already exists
    if (empty($form_errors['username'])) {
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $form_errors['username'] = "Username already exists";
        }
    }
    
    // Check if email already exists
    if (empty($form_errors['email']) && !empty($email)) {
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $form_errors['email'] = "Email already registered";
        }
    }
    
    // If no validation errors, create account
    if (empty($form_errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert new user
            $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $hashed_password, $email);
            $stmt->execute();
            $user_id = $conn->insert_id;
            
            // Insert user profile
            $profile_sql = "INSERT INTO user_profiles (user_id, first_name, last_name, phone) VALUES (?, ?, ?, ?)";
            $profile_stmt = $conn->prepare($profile_sql);
            $profile_stmt->bind_param("isss", $user_id, $first_name, $last_name, $phone);
            $profile_stmt->execute();
            
            // Add to user history
            $history_sql = "INSERT INTO user_history (user_id, activity_type, activity_description) VALUES (?, ?, ?)";
            $history_stmt = $conn->prepare($history_sql);
            $activity_type = "account_created";
            $activity_description = "Account created successfully";
            $history_stmt->bind_param("iss", $user_id, $activity_type, $activity_description);
            $history_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $success = "Account created successfully! You can now login.";
            
            // Clear form data
            $_POST = [];
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error = "Error creating account: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - J&T Express</title>
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
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                        <?php if (isset($form_errors['first_name'])): ?>
                            <span class="error-text"><?php echo $form_errors['first_name']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                        <?php if (isset($form_errors['last_name'])): ?>
                            <span class="error-text"><?php echo $form_errors['last_name']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    <?php if (isset($form_errors['username'])): ?>
                        <span class="error-text"><?php echo $form_errors['username']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    <?php if (isset($form_errors['email'])): ?>
                        <span class="error-text"><?php echo $form_errors['email']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    <?php if (isset($form_errors['phone'])): ?>
                        <span class="error-text"><?php echo $form_errors['phone']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                    <small class="form-hint">Must be at least 8 characters long</small>
                    <?php if (isset($form_errors['password'])): ?>
                        <span class="error-text"><?php echo $form_errors['password']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <?php if (isset($form_errors['confirm_password'])): ?>
                        <span class="error-text"><?php echo $form_errors['confirm_password']; ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
                <a href="login.php" class="btn btn-secondary">Back to Login</a>
            </form>
        </div>
    </div>
</body>
</html>