<?php
// 1. Manual Connection
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "portal";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database Error."; exit;
}

// 2. Count Active Students
// We use simple queries without complex joining logic here
$stmt = $pdo->query("SELECT COUNT(*) FROM account WHERE role='student' AND status='Active'");
$total_stu = $stmt->fetchColumn();

// 3. Count Per Track (Manual Queries)
// Kinder
$stmt_k = $pdo->query("SELECT COUNT(*) FROM students WHERE track='kinder'");
$k = $stmt_k->fetchColumn();

// Junior High
$stmt_j = $pdo->query("SELECT COUNT(*) FROM students WHERE track='junior high school'");
$j = $stmt_j->fetchColumn();

// Senior High (Explicit OR logic)
$stmt_s = $pdo->query("SELECT COUNT(*) FROM students WHERE track='senior high school' OR track='STEM' OR track='ABM' OR track='HUMSS'");
$s = $stmt_s->fetchColumn();

?>

<style>
    .dash-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px; 
        margin-top: 20px; 
    }
    .stat-card { 
        background: white; 
        padding: 20px; 
        border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        border-left: 5px solid #002D72; 
    }
    .stat-val { font-size: 2rem; font-weight: bold; color: #333; }
    .stat-label { color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
    
    .quick-actions { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
        gap: 15px; 
        margin-top: 30px; 
    }
    .qa-btn { 
        padding: 15px; 
        border: 1px solid #ddd; 
        background: white; 
        border-radius: 8px; 
        text-align: center; 
        cursor: pointer; 
        transition: 0.2s; 
    }
    .qa-btn:hover { background: #f0f8ff; border-color: #002D72; color: #002D72; }
</style>

<div style="padding: 10px;">
    <h1 style="color:#002D72; margin-bottom:5px;">Welcome Back!</h1>
    <p style="color:#666;">Here is the current status of the university portal.</p>

    <div class="dash-grid">
        <div class="stat-card">
            <div class="stat-val"><?php echo number_format($total_stu); ?></div>
            <div class="stat-label">Active Students</div>
        </div>

        <div class="stat-card" style="border-left-color: #ffc107;">
            <div class="stat-val"><?php echo $s; ?></div>
            <div class="stat-label">Senior High</div>
        </div>

        <div class="stat-card" style="border-left-color: #0dcaf0;">
            <div class="stat-val"><?php echo $j; ?></div>
            <div class="stat-label">Junior High</div>
        </div>

        <div class="stat-card" style="border-left-color: #d63384;">
            <div class="stat-val"><?php echo $k; ?></div>
            <div class="stat-label">Kindergarten</div>
        </div>
    </div>

    <h3 style="margin-top: 40px; color: #333;">Quick Actions</h3>
    <div class="quick-actions">
        <div class="qa-btn" onclick="loadZone('enroll-student-ajax.php', document.querySelectorAll('.sidebar-right button')[1])">
            <strong>📝 New Enrollee</strong>
        </div>
        <div class="qa-btn" onclick="loadZone('billing.php', document.querySelectorAll('.sidebar-right button')[2])">
            <strong>💳 Process Payment</strong>
        </div>
        <div class="qa-btn" onclick="loadZone('re_enroll.php', document.querySelectorAll('.sidebar-right button')[6])">
            <strong>🎓 Promotions</strong>
        </div>
    </div>
</div>