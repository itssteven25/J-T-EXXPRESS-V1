<?php
include '../includes/header.php';
include '../includes/sidebar.php';
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
    $country = $_POST['country'];
    
    // Update profile
    $update_sql = "UPDATE user_profiles SET first_name=?, last_name=?, phone=?, address=?, city=?, postal_code=?, country=? WHERE user_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssssi", $first_name, $last_name, $phone, $address, $city, $postal_code, $country, $_SESSION['user_id']);
    
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
    <title>My Account - J&T Express</title>
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
                <div class="account-tabs">
                    <button class="tab-btn active" data-tab="profile">Profile Information</button>
                    <button class="tab-btn" data-tab="security">Security Settings</button>
                    <button class="tab-btn" data-tab="preferences">Preferences</button>
                </div>
                
                <div class="tab-content active" id="profile-tab">
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
                                
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? 'Philippines'); ?>">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
                
                <div class="tab-content" id="security-tab">
                    <div class="security-card">
                        <h3>Security Settings</h3>
                        <div class="security-options">
                            <div class="security-item">
                                <div class="security-info">
                                    <h4>Change Password</h4>
                                    <p>Update your account password regularly</p>
                                </div>
                                <button class="btn btn-secondary">Change</button>
                            </div>
                            
                            <div class="security-item">
                                <div class="security-info">
                                    <h4>Two-Factor Authentication</h4>
                                    <p>Add an extra layer of security to your account</p>
                                </div>
                                <button class="btn btn-secondary">Setup</button>
                            </div>
                            
                            <div class="security-item">
                                <div class="security-info">
                                    <h4>Session Management</h4>
                                    <p>Manage active sessions across devices</p>
                                </div>
                                <button class="btn btn-secondary">Manage</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="preferences-tab">
                    <div class="preferences-card">
                        <h3>Account Preferences</h3>
                        <div class="preference-options">
                            <div class="preference-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" checked>
                                    <span class="checkmark"></span>
                                    Email notifications for shipment updates
                                </label>
                            </div>
                            
                            <div class="preference-item">
                                <label class="checkbox-label">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    SMS notifications for delivery
                                </label>
                            </div>
                            
                            <div class="preference-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" checked>
                                    <span class="checkmark"></span>
                                    Receive promotional offers
                                </label>
                            </div>
                            
                            <div class="preference-item">
                                <label class="checkbox-label">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    Share location for better service
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and tabs
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Show corresponding tab content
                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>