<?php
// index.php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/database.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="login-layout">
    
    <!-- Left Side: Dark Constellation Animation -->
    <div class="login-left">
        <!-- particles.js container -->
        <div id="particles-js"></div>
        
        <div class="login-left-content">
            <h1>LogIT</h1>
            <p>The smart way to track your IT workflow.</p>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="login-right">
        <div class="login-card">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="border-radius: var(--radius-sm);">
                    <i class='bx bx-error-circle'></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div style="position: relative;">
                        <i class='bx bx-user' style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.25rem;"></i>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required autofocus style="padding-left: 3rem;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 2.5rem;">
                    <label for="password" class="form-label">Password</label>
                    <div style="position: relative;">
                        <i class='bx bx-lock-alt' style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.25rem;"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required style="padding-left: 3rem;">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Log In
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
