<?php
// dashboard.php
require_once 'includes/header.php';
require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');
$success_msg = '';
$error_msg = '';

// Handle Task Submission (Staff Only)
if (!$is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_task') {
    $category_id = $_POST['category_id'] ?? '';
    $department = trim($_POST['department'] ?? '');
    $staff_helped = trim($_POST['staff_helped'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'yellow';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($category_id) || empty($department) || empty($staff_helped) || empty($description)) {
        $error_msg = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO tasks (user_id, category_id, department, staff_helped, description, priority, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$user_id, $category_id, $department, $staff_helped, $description, $priority, $notes])) {
            $success_msg = "Task logged successfully!";
            // Redirect to avoid resubmission
            header("Location: dashboard.php?msg=success");
            exit();
        } else {
            $error_msg = "Failed to log task. Please try again.";
        }
    }
}

// Handle Status Update (Staff Only)
if (!$is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $task_id = $_POST['task_id'] ?? 0;
    $new_status = $_POST['new_status'] ?? '';
    
    if ($task_id && in_array($new_status, ['red', 'yellow', 'green'])) {
        $stmt = $pdo->prepare("UPDATE tasks SET priority = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_status, $task_id, $user_id]);
        $success_msg = "Task status updated!";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'success' && empty($success_msg)) {
    $success_msg = "Task logged successfully!";
}

// Filters setup
$time_filter = $_GET['time'] ?? 'today';
$staff_filter = $_GET['staff_id'] ?? 'all';

// Build WHERE clauses based on filters
$where_clauses = [];
$params = [];

// Time Filter Logic
if ($time_filter === 'today') {
    $where_clauses[] = "DATE(t.created_at) = CURDATE()";
} elseif ($time_filter === 'week') {
    $where_clauses[] = "YEARWEEK(t.created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($time_filter === 'month') {
    $where_clauses[] = "MONTH(t.created_at) = MONTH(CURDATE()) AND YEAR(t.created_at) = YEAR(CURDATE())";
} elseif ($time_filter === 'year') {
    $where_clauses[] = "YEAR(t.created_at) = YEAR(CURDATE())";
}

// Role / User Filter Logic
if ($is_admin) {
    if ($staff_filter !== 'all') {
        $where_clauses[] = "t.user_id = ?";
        $params[] = $staff_filter;
    }
} else {
    // Staff can ONLY see their own logs
    $where_clauses[] = "t.user_id = ?";
    $params[] = $user_id;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch Stats based on filters
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks t $where_sql");
$stmt->execute($params);
$total_count = $stmt->fetchColumn();

// Fetch Log Feed based on filters
$stmt = $pdo->prepare("
    SELECT t.*, c.name as category_name, u.full_name as logged_by 
    FROM tasks t 
    JOIN categories c ON t.category_id = c.id 
    JOIN users u ON t.user_id = u.id 
    $where_sql
    ORDER BY t.created_at DESC 
    LIMIT 100
");
$stmt->execute($params);
$recent_logs = $stmt->fetchAll();

// Fetch dropdown data
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$all_staff = [];
if ($is_admin) {
    $all_staff = $pdo->query("SELECT id, full_name FROM users WHERE role = 'staff' ORDER BY full_name ASC")->fetchAll();
}
?>

<div style="margin-bottom: 2rem; display: flex; flex-direction: column; gap: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.25rem;">Dashboard</h1>
            <p style="color: var(--text-muted);">Overview of IT support activities.</p>
        </div>
        
        <!-- Filter Form -->
        <form action="dashboard.php" method="GET" class="filter-bar" style="margin: 0; align-items: center;">
            <?php if ($is_admin): ?>
                <select name="staff_id" class="filter-select" onchange="this.form.submit()">
                    <option value="all">All Staff</option>
                    <?php foreach($all_staff as $staff): ?>
                        <option value="<?= $staff['id'] ?>" <?= $staff_filter == $staff['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($staff['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <select name="time" class="filter-select" onchange="this.form.submit()">
                <option value="today" <?= $time_filter === 'today' ? 'selected' : '' ?>>Today (24hrs)</option>
                <option value="week" <?= $time_filter === 'week' ? 'selected' : '' ?>>This Week</option>
                <option value="month" <?= $time_filter === 'month' ? 'selected' : '' ?>>This Month</option>
                <option value="year" <?= $time_filter === 'year' ? 'selected' : '' ?>>This Year</option>
            </select>
        </form>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success" style="background-color: var(--status-green-bg); color: var(--status-green); border: 1px solid rgba(16,185,129,0.2);">
        <i class='bx bx-check-circle'></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-error">
        <i class='bx bx-error-circle'></i> <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<!-- Stats Area -->
<div class="stats-grid">
    <div class="glass-card stat-card">
        <div class="icon">
            <i class='bx bx-group'></i>
        </div>
        <h3><?= $is_admin ? ($staff_filter === 'all' ? 'Total Team Logs' : 'Total Staff Logs') : 'My Total Logs' ?></h3>
        <div class="value"><?= $total_count ?></div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="dashboard-grid" style="<?= $is_admin ? 'grid-template-columns: 1fr;' : '' ?>">
    <!-- Left Column: Activity Feed -->
    <div>
        <div class="glass-card" style="height: 100%;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 700;">
                Activity Feed
            </h2>
            
            <div class="log-feed">
                <?php if (empty($recent_logs)): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No tasks found for this filter.</p>
                <?php else: ?>
                    <?php foreach($recent_logs as $log): ?>
                        <div class="log-item">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                                <strong style="font-size: 1.1rem; color: var(--text-main);"><?= htmlspecialchars($log['category_name']) ?></strong>
                                <span class="badge <?= htmlspecialchars($log['priority']) ?>">
                                    <?= strtoupper($log['priority']) ?>
                                </span>
                            </div>
                            <p style="font-size: 0.95rem; color: var(--text-main); line-height: 1.6; word-wrap: break-word;">
                                <?= nl2br(htmlspecialchars($log['description'])) ?>
                            </p>
                            <div class="log-meta" style="margin-top: 0.5rem;">
                                <?php if ($is_admin): ?>
                                    <span><i class='bx bx-user'></i> <?= htmlspecialchars($log['logged_by']) ?></span>
                                <?php endif; ?>
                                <span><i class='bx bx-buildings'></i> <?= htmlspecialchars($log['department']) ?> (<?= htmlspecialchars($log['staff_helped']) ?>)</span>
                                <span><i class='bx bx-calendar'></i> <?= date('M j, Y - h:i A', strtotime($log['created_at'])) ?></span>
                            </div>
                            
                            <?php if (!$is_admin && $log['user_id'] == $_SESSION['user_id'] && $log['priority'] !== 'green'): ?>
                                <form action="dashboard.php" method="POST" style="margin-top: 1rem; display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="task_id" value="<?= $log['id'] ?>">
                                    <select name="new_status" class="form-control" style="padding: 0.5rem; font-size: 0.85rem; width: auto; flex: 1; min-width: 140px;">
                                        <option value="green">Mark Resolved</option>
                                        <option value="yellow" <?= $log['priority'] === 'yellow' ? 'selected' : '' ?>>Mark Medium</option>
                                        <option value="red" <?= $log['priority'] === 'red' ? 'selected' : '' ?>>Mark Urgent</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Update</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Log Task Form (Staff Only) -->
    <?php if (!$is_admin): ?>
    <div>
        <div class="glass-card" style="position: sticky; top: 2rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                Log New Task
            </h2>
            <form action="dashboard.php" method="POST">
                <input type="hidden" name="action" value="log_task">
                
                <div class="form-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="department">Department</label>
                    <input type="text" name="department" id="department" class="form-control" required placeholder="e.g. HR, Finance">
                </div>

                <div class="form-group">
                    <label class="form-label" for="staff_helped">Staff Member Helped</label>
                    <input type="text" name="staff_helped" id="staff_helped" class="form-control" required placeholder="Name of staff">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3" required placeholder="What was done?"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="priority">Status / Priority</label>
                    <select name="priority" id="priority" class="form-control" required>
                        <option value="green">🟢 Resolved / Completed</option>
                        <option value="yellow" selected>🟡 Medium (Needs monitoring)</option>
                        <option value="red">🔴 High (Unresolved/Urgent)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Follow-up actions or remarks"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Submit Log
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
