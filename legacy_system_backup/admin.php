<?php
// admin.php
require_once 'includes/header.php';
require_once 'config/database.php';

// Only admins allowed
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// Handle Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff';

    if (empty($username) || empty($full_name) || empty($password)) {
        $error_msg = "All fields are required to create a user.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error_msg = "Username already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $hash, $full_name, $role])) {
                $success_msg = "User created successfully.";
            } else {
                $error_msg = "Failed to create user.";
            }
        }
    }
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $cat_name = trim($_POST['name'] ?? '');
    if (empty($cat_name)) {
        $error_msg = "Category name is required.";
    } else {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->execute([$cat_name]);
        if ($stmt->rowCount() > 0) {
            $success_msg = "Category added successfully.";
        } else {
            $error_msg = "Category already exists.";
        }
    }
}

$users = $pdo->query("SELECT id, username, full_name, role, created_at FROM users ORDER BY full_name ASC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<?php if ($success_msg): ?>
    <div class="alert alert-success" style="background-color: var(--status-green-bg); color: var(--status-green); border: 1px solid rgba(16,185,129,0.2); border-radius: var(--radius-sm);">
        <i class='bx bx-check-circle'></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-error" style="border-radius: var(--radius-sm);">
        <i class='bx bx-error-circle'></i> <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.5rem;">Admin Panel</h1>
        <p style="color: var(--text-muted);">Manage users, categories, and system data.</p>
    </div>
    <a href="export.php" class="btn" style="background: var(--status-green); color: white; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);">
        <i class='bx bx-export'></i> Export CSV
    </a>
</div>

<div class="dashboard-grid">
    <!-- Left Column: Forms -->
    <div>
        <div class="glass-card">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-user-plus' style="color: var(--primary);"></i> Add New User</h3>
            <form action="admin.php" method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="role">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create User</button>
            </form>
        </div>

        <div class="glass-card">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-category' style="color: var(--primary);"></i> Add Category</h3>
            <form action="admin.php" method="POST">
                <input type="hidden" name="action" value="add_category">
                <div class="form-group">
                    <label class="form-label" for="name">Category Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-outline" style="width: 100%;">Add Category</button>
            </form>
        </div>
    </div>

    <!-- Right Column: Lists -->
    <div>
        <div class="glass-card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem;"><i class='bx bx-group'></i> System Users</h3>
            <div class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($user['full_name']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] === 'admin' ? 'red' : 'green' ?>">
                                        <?= strtoupper($user['role']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-card">
            <h3 style="margin-bottom: 1.5rem;"><i class='bx bx-list-ul'></i> Active Categories</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                <?php foreach($categories as $cat): ?>
                    <span style="background: rgba(255,255,255,0.8); padding: 0.5rem 1rem; border-radius: var(--radius-pill); border: 1px solid rgba(0,0,0,0.05); font-size: 0.875rem; font-weight: 500; color: var(--text-main); box-shadow: var(--shadow-sm);">
                        <?= htmlspecialchars($cat['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
