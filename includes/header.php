<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<header class="top-nav">
    <div class="nav-container">
        <div class="nav-left">
            <div class="logo">
                <h2>J&T EXPRESS</h2>
            </div>
            <nav class="main-nav">
                <a href="../dashboard/index.php">Home</a>
                <a href="../tracking/track.php">Track & Trace</a>
                <a href="../rates/shipping-rates.php">Shipping Rates</a>
                <a href="../pickup/package-pickup.php">Package Pickup</a>
                <a href="../drop-points/drop-points.php">Drop Points</a>
                <a href="../services/service-info.php">Service Info</a>
            </nav>
        </div>
        <div class="nav-right">
            <div class="search-bar">
                <input type="text" placeholder="Search tracking number..." id="search-input">
                <button class="search-btn">🔍</button>
            </div>
            <div class="nav-icons">
                <a href="../notifications/notifications.php"><button class="icon-btn">🔔</button></a>
                <div class="user-profile">
                    <span><?php echo $_SESSION['username']; ?></span>
                    <div class="profile-icon"></div>
                </div>
            </div>
        </div>
    </div>
</header>