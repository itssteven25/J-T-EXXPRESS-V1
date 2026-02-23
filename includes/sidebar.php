<aside class="sidebar">
    <div class="sidebar-header">
        <div class="user-info">
            <div class="user-avatar">
                <div class="avatar-circle"></div>
            </div>
            <div class="user-name">
                <p>Hi, <?php echo $_SESSION['username']; ?></p>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="../dashboard/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📦</span>
                    <span class="nav-text">Track & Trace</span>
                </a>
            </li>
            <li>
                <a href="../rates/shipping-rates.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shipping-rates.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">💰</span>
                    <span class="nav-text">Shipping Rates</span>
                </a>
            </li>
            <li>
                <a href="../pickup/package-pickup.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'package-pickup.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🚚</span>
                    <span class="nav-text">Package Pickup</span>
                </a>
            </li>
            <li>
                <a href="../drop-points/drop-points.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'drop-points.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📍</span>
                    <span class="nav-text">Drop Points</span>
                </a>
            </li>
            <li>
                <a href="../services/service-info.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'service-info.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">ℹ️</span>
                    <span class="nav-text">Service Info</span>
                </a>
            </li>
            <li>
                <a href="../dashboard/shipments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shipments.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📋</span>
                    <span class="nav-text">My Shipments</span>
                </a>
            </li>
            <li>
                <a href="../history/history.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🕒</span>
                    <span class="nav-text">History</span>
                </a>
            </li>
            <li>
                <a href="../account/my-account.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my-account.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">👤</span>
                    <span class="nav-text">My Account</span>
                </a>
            </li>
            <li>
                <a href="../notifications/notifications.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🔔</span>
                    <span class="nav-text">Notifications</span>
                </a>
            </li>
            <li>
                <a href="../support/support.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'support.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">❓</span>
                    <span class="nav-text">Support</span>
                </a>
            </li>
            <li class="nav-divider"></li>
            <li>
                <a href="../auth/logout.php" class="nav-link logout">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>