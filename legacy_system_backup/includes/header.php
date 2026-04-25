<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Redirect to login if not authenticated and not on login page
if (!isset($_SESSION['user_id']) && $current_page !== 'index.php') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogIT - IT Activity Logging System</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Boxicons for modern icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Particles.js for Login Animation -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="app-container">
    
    <!-- Mobile Header -->
    <header class="mobile-header">
        <div style="font-weight: 800; color: var(--primary); font-size: 1.25rem;">
            LogIT
        </div>
        <button class="mobile-menu-btn" aria-label="Toggle Menu">
            <i class='bx bx-menu'></i>
        </button>
    </header>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class='bx bx-hive' style="color: var(--primary);"></i> LogIT
        </div>
        
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class='bx bx-grid-alt'></i> Dashboard
            </a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="nav-item <?= $current_page == 'admin.php' ? 'active' : '' ?>">
                    <i class='bx bx-slider-alt'></i> Admin Panel
                </a>
            <?php endif; ?>
        </div>
        
        <div class="user-profile">
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">
                        <?= htmlspecialchars($_SESSION['full_name']) ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">
                        <?= htmlspecialchars($_SESSION['role']) ?>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="nav-item" style="color: var(--status-red); padding: 0.75rem 0;">
                <i class='bx bx-log-out'></i> Log out
            </a>
        </div>
    </nav>
    
    <main class="main-content">
<?php else: ?>
    <!-- Login page will not have sidebar wrapper -->
<?php endif; ?>
