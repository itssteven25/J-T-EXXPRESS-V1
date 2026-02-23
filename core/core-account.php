<?php
include 'core-header.php';
include 'core-sidebar.php';
include '../includes/db.php';

// Fetch user profile
$profile_sql = "SELECT * FROM user_profiles WHERE user_id = ?";
$stmt = $conn->prepare($profile_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$profile_result = $stmt->get_result();
$profile = $profile_result->fetch_assoc();

// If no profile exists, create a default one
if (!$profile) {
    $insert_profile = "INSERT INTO user_profiles (user_id) VALUES (?)";
    $stmt = $conn->prepare($insert_profile);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    
    // Fetch the newly created profile
    $profile_result = $stmt = $conn->prepare($profile_sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $profile_result = $stmt->get_result();
    $profile = $profile_result->fetch_assoc();
}

// Fetch user info
$user_sql = "SELECT username, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    
    // Update profile
    $update_sql = "UPDATE user_profiles SET first_name=?, last_name=?, phone=?, address=?, city=?, postal_code=? WHERE user_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $address, $city, $postal_code, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
        
        // Refresh profile data
        $profile_result = $stmt = $conn->prepare($profile_sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $profile_result = $stmt->get_result();
        $profile = $profile_result->fetch_assoc();
    } else {
        $error_message = "Error updating profile: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Account - J&T Express</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>My Account</h1>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="account-container">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <div class="avatar-circle"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                        </div>
                        <div class="profile-basic-info">
                            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                            <p>Member since <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="address">Address</label>
                            <textarea id="address" name="address"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($profile['postal_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
                
                <div class="account-summary">
                    <h3>Account Summary</h3>
                    <div class="summary-grid">
                        <div class="summary-card">
                            <h4>Total Shipments</h4>
                            <p>12</p>
                        </div>
                        <div class="summary-card">
                            <h4>Active Shipments</h4>
                            <p>3</p>
                        </div>
                        <div class="summary-card">
                            <h4>Delivered</h4>
                            <p>9</p>
                        </div>
                        <div class="summary-card">
                            <h4>Account Age</h4>
                            <p><?php echo floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24)); ?> days</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>