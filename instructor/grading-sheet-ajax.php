<?php
require_once '../functions/instructor_function.php';
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    echo "Session Expired."; exit;
}

$account_id = $_SESSION['ACCOUNTID'] ?? null;
$instructor = new Instructor();

// 1. GET CURRENT CONFIG (To know what is "Current")
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$config = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
$current_sy = $config['current_year'] ?? '2025-2026';

// 2. HANDLE ARCHIVE TOGGLE
$show_archive = isset($_GET['view']) && $_GET['view'] == 'archive';

// 3. FETCH SECTIONS
// We filter by School Year based on the toggle
if ($show_archive) {
    $sql = "SELECT * FROM sections WHERE instructor_id = (SELECT id FROM account WHERE account_id = ?) AND school_year != ? ORDER BY school_year DESC";
} else {
    $sql = "SELECT * FROM sections WHERE instructor_id = (SELECT id FROM account WHERE account_id = ?) AND school_year = ? ORDER BY code ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$account_id, $current_sy]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #eee; padding-bottom:10px;">
    <h2 style="color:#002D72; margin:0;">
        <?php echo $show_archive ? "Archived Classes" : "My Class Loads"; ?>
    </h2>
    <div>
        <?php if($show_archive): ?>
            <button onclick="loadZone('grading-sheet-ajax.php', this)" style="padding:8px 15px; background:#198754; color:white; border:none; cursor:pointer; border-radius:4px;">View Current</button>
        <?php else: ?>
            <button onclick="loadZone('grading-sheet-ajax.php?view=archive', this)" style="padding:8px 15px; background:#6c757d; color:white; border:none; cursor:pointer; border-radius:4px;">View Archive</button>
        <?php endif; ?>
    </div>
</div>

<style>
    /* ... paste your existing CSS for cards ... */
    .class-card-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
    .class-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .cc-code { font-size: 1.2rem; font-weight: bold; color: #002D72; }
    .badge { padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; margin-right: 5px; }
    .bg-track { background: #e7f1ff; color: #002D72; }
    .btn-open { display: block; width: 100%; text-align: center; background: #002D72; color: white; padding: 10px; border: none; border-radius: 6px; cursor: pointer; margin-top:10px;}
</style>

<?php if (empty($sections)): ?>
    <div style="text-align:center; padding:50px; color:#888;">
        <h3>No classes found in <?php echo $show_archive ? 'Archive' : 'Current Year'; ?>.</h3>
    </div>
<?php else: ?>
    <div class="class-card-container">
        <?php foreach ($sections as $row): ?>
            <div class="class-card">
                <div style="display:flex; justify-content:space-between;">
                    <div class="cc-code"><?php echo htmlspecialchars($row['code']); ?></div>
                    <small><?php echo htmlspecialchars($row['section']); ?></small>
                </div>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <div>
                    <span class="badge bg-track"><?php echo htmlspecialchars($row['track']); ?></span>
                    <span class="badge" style="background:#eee;"><?php echo htmlspecialchars($row['year_level']); ?></span>
                </div>
                <hr style="border:0; border-top:1px solid #eee; margin:10px 0;">
                
                <button class="btn-open" onclick="loadZone('section-grades.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                    Open Gradebook
                </button>
                <?php if(!$show_archive): ?>
                <button class="btn-open" style="background:#ffc107; color:#333; margin-top:5px;" 
                        onclick="loadZone('attendance.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                    Attendance
                </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>