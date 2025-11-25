<?php
// 1. GET STATS
$host = "localhost"; $user = "root"; $pass = ""; $db = "portal";
$student_count = 0;
$instructor_count = 0;
$db_status = "<span style='color:#dc3545'>❌ Disconnected</span>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $db_status = "<span style='color:#198754'>✅ Database Connected (Healthy)</span>";
    
    // Count Students
    $stmt = $pdo->query("SELECT COUNT(*) FROM account WHERE role = 'student'");
    $student_count = $stmt->fetchColumn();

    // Count Instructors
    $stmt = $pdo->query("SELECT COUNT(*) FROM account WHERE role = 'instructor'");
    $instructor_count = $stmt->fetchColumn();

} catch(PDOException $e) {
    $db_status = "<span style='color:#dc3545'>❌ Connection Error: " . $e->getMessage() . "</span>";
}
?>

<style>
    /* Force Centered Layout */
    .dashboard-center {
        text-align: center;
        max-width: 1000px;
        margin: 0 auto;
    }

    .stats-row {
        display: flex;
        justify-content: center; /* Centers the cards */
        gap: 20px;
        margin-top: 30px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .stat-card {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        width: 220px;
        text-align: center;
        border-top: 5px solid #002D72; /* Royal Blue Top Border */
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-5px); }

    .stat-number { font-size: 2.5rem; color: #002D72; font-weight: bold; margin: 0; }
    .stat-label { color: #666; margin-top: 5px; font-size: 1rem; }
    .stat-icon { font-size: 2rem; margin-bottom: 10px; opacity: 0.8; }
    
    .tips-box {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: left;
        max-width: 600px;
        margin: 0 auto; /* Center the box itself */
    }
</style>

<div class="dashboard-center">
    <h2 style="color:#002D72; border-bottom:2px solid #eee; padding-bottom:15px;">Admin Dashboard Overview</h2>

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

        <div class="stat-card" style="border-top-color: #198754;">
            <div class="stat-icon">🖥️</div>
            <div style="font-weight:bold; margin-top:10px;"><?php echo $db_status; ?></div>
            <div class="stat-label" style="font-size:0.8rem;">Server: Localhost</div>
        </div>

    </div>

    <div class="tips-box">
        <strong style="color:#002D72; font-size:1.1rem;">Quick Tips:</strong>
        <ul style="color:#555; margin-top:10px; padding-left:20px;">
            <li>Go to <strong>"Faculty List"</strong> to update instructor degrees and active status.</li>
            <li>Go to <strong>"Manage Accounts"</strong> to reset passwords or delete users.</li>
            <li>Go to <strong>"Create New User"</strong> to add new staff or teachers.</li>
        </ul>
    </div>
</div>