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
                <a href="core-dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'core-dashboard.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📦</span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="core-tracking.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'core-tracking.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🔍</span>
                    <span class="nav-text">Track Shipment</span>
                </a>
            </li>
            <li>
                <a href="core-shipments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'core-shipments.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📋</span>
                    <span class="nav-text">My Shipments</span>
                </a>
            </li>
            <li>
                <a href="core-account.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'core-account.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">👤</span>
                    <span class="nav-text">My Account</span>
                </a>
            </li>
            <li>
                <a href="core-support.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'core-support.php' ? 'active' : ''; ?>">
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