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
                <h2>J&T EXPRESS - Core</h2>
            </div>
            <nav class="main-nav">
                <a href="core-dashboard.php">Dashboard</a>
                <a href="core-tracking.php">Track</a>
                <a href="core-shipments.php">Shipments</a>
            </nav>
        </div>
        <div class="nav-right">
            <div class="search-bar">
                <input type="text" placeholder="Search tracking..." id="search-input">
                <button class="search-btn" onclick="searchTracking()">🔍</button>
            </div>
            <div class="nav-icons">
                <a href="core-support.php"><button class="icon-btn">❓</button></a>
                <div class="user-profile">
                    <span><?php echo $_SESSION['username']; ?></span>
                    <div class="profile-icon"></div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function searchTracking() {
    const trackingNumber = document.getElementById('search-input').value;
    if (trackingNumber) {
        window.location.href = `core-tracking.php?tracking=${encodeURIComponent(trackingNumber)}`;
    }
}
</script>