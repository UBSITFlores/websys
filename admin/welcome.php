<?php
// 1. GET STATS
$host = "localhost"; $user = "root"; $pass = ""; $db = "portal";
$student_count = 0;
$instructor_count = 0;
$db_status = "<span class='db-error'>❌ Disconnected</span>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $db_status = "<span class='db-success'>✅ Database Connected (Healthy)</span>";
    
    // Count Students
    $stmt = $pdo->query("SELECT COUNT(*) FROM account WHERE role = 'student'");
    $student_count = $stmt->fetchColumn();

    // Count Instructors
    $stmt = $pdo->query("SELECT COUNT(*) FROM account WHERE role = 'instructor'");
    $instructor_count = $stmt->fetchColumn();

} catch(PDOException $e) {
    $db_status = "<span class='db-error'>❌ Connection Error: " . $e->getMessage() . "</span>";
}
?>

<link rel="stylesheet" href="welcome.css">

<div class="dashboard-center">
    <h2>Admin Dashboard Overview</h2>

    <div class="stats-row">
        
        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <div class="stat-number"><?php echo $student_count; ?></div>
            <div class="stat-label">Total Students</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🍎</div>
            <div class="stat-number"><?php echo $instructor_count; ?></div>
            <div class="stat-label">Instructors</div>
        </div>

        <div class="stat-card db-card">
            <div class="stat-icon">🖥️</div>
            <div class="db-status"><?php echo $db_status; ?></div>
            <div class="stat-label small">Server: Localhost</div>
        </div>

    </div>

    <div class="tips-box">
        <strong>Quick Tips:</strong>
        <ul>
            <li>Go to <strong>"Faculty List"</strong> to update instructor degrees and active status.</li>
            <li>Go to <strong>"Manage Accounts"</strong> to reset passwords or delete users.</li>
            <li>Go to <strong>"Create New User"</strong> to add new staff or teachers.</li>
        </ul>
    </div>
</div>