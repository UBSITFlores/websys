<?php
require_once '../functions/instructor_function.php';
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    echo "Session Expired."; exit;
}

$account_id = $_SESSION['ACCOUNTID'] ?? null;
$instructor = new Instructor();

// 1. GET CURRENT CONFIG
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$config = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
$current_sy = $config['current_year'] ?? '2025-2026';

// 2. HANDLE ARCHIVE TOGGLE
$show_archive = isset($_GET['view']) && $_GET['view'] == 'archive';

// 3. FETCH SECTIONS
if ($show_archive) {
    $sql = "SELECT * FROM sections 
            WHERE instructor_id = (SELECT id FROM account WHERE account_id = ?) 
              AND school_year != ? 
            ORDER BY school_year DESC";
} else {
    $sql = "SELECT * FROM sections 
            WHERE instructor_id = (SELECT id FROM account WHERE account_id = ?) 
              AND school_year = ? 
            ORDER BY code ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$account_id, $current_sy]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Loads</title>
    <link rel="stylesheet" href="class_loads.css">
</head>
<body>
    <div class="header-bar">
        <h2 class="header-title">
            <?php echo $show_archive ? "Archived Classes" : "My Class Loads"; ?>
        </h2>
        <div>
            <?php if($show_archive): ?>
                <button class="btn btn-current" 
                        onclick="loadZone('grading-sheet-ajax.php', this)">
                    View Current
                </button>
            <?php else: ?>
                <button class="btn btn-archive" 
                        onclick="loadZone('grading-sheet-ajax.php?view=archive', this)">
                    View Archive
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($sections)): ?>
        <div class="no-classes">
            <h3>No classes found in <?php echo $show_archive ? 'Archive' : 'Current Year'; ?>.</h3>
        </div>
    <?php else: ?>
        <div class="class-card-container">
            <?php foreach ($sections as $row): ?>
                <div class="class-card">
                    <div class="card-header">
                        <div class="cc-code"><?php echo htmlspecialchars($row['code']); ?></div>
                        <small><?php echo htmlspecialchars($row['section']); ?></small>
                    </div>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <div>
                        <span class="badge bg-track"><?php echo htmlspecialchars($row['track']); ?></span>
                        <span class="badge bg-year"><?php echo htmlspecialchars($row['year_level']); ?></span>
                    </div>
                    <hr class="card-divider">
                    
                    <button class="btn-open" 
                            onclick="loadZone('section-grades.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                        Open Gradebook
                    </button>
                    <?php if(!$show_archive): ?>
                        <button class="btn-open btn-attendance" 
                                onclick="loadZone('attendance.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                            Attendance
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
