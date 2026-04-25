<?php
// export.php
session_start();
require_once 'config/database.php';

// Only admins can export
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

// Fetch all tasks with joins
$stmt = $pdo->prepare("
    SELECT 
        t.id, 
        u.full_name as logged_by, 
        c.name as category, 
        t.department, 
        t.staff_helped, 
        t.description, 
        t.priority, 
        t.notes, 
        t.created_at 
    FROM tasks t
    JOIN users u ON t.user_id = u.id
    JOIN categories c ON t.category_id = c.id
    ORDER BY t.created_at DESC
");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=LogIT_Export_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, ['ID', 'Logged By', 'Category', 'Department', 'Staff Helped', 'Description', 'Priority Status', 'Notes', 'Date & Time']);

foreach ($logs as $log) {
    fputcsv($output, [
        $log['id'],
        $log['logged_by'],
        $log['category'],
        $log['department'],
        $log['staff_helped'],
        $log['description'],
        strtoupper($log['priority']),
        $log['notes'],
        $log['created_at']
    ]);
}

fclose($output);
exit();
?>
