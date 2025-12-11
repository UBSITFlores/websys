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

<style>
/* --- INLINE STYLES FOR GRADING SHEET --- */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.page-title {
    color: #002D72;
    margin: 0;
    font-size: 1.8rem;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.btn-toggle {
    padding: 8px 15px;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-current {
    background: #198754;
    color: white;
}

.btn-current:hover {
    background: #157347;
}

.btn-archive {
    background: #6c757d;
    color: white;
}

.btn-archive:hover {
    background: #5a6268;
}

.class-card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.class-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.class-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 10px;
}

.cc-code {
    font-size: 1.2rem;
    font-weight: bold;
    color: #002D72;
}

.cc-section {
    font-size: 0.85rem;
    color: #666;
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 4px;
}

.cc-description {
    color: #555;
    font-size: 0.95rem;
    margin: 10px 0;
    line-height: 1.4;
}

.badge-container {
    display: flex;
    gap: 5px;
    margin: 10px 0;
}

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: bold;
    text-transform: uppercase;
}

.bg-track {
    background: #e7f1ff;
    color: #002D72;
}

.bg-year {
    background: #eee;
    color: #555;
}

.card-divider {
    border: 0;
    border-top: 1px solid #eee;
    margin: 12px 0;
}

.btn-open {
    display: block;
    width: 100%;
    text-align: center;
    background: #002D72;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-open:hover {
    background: #004099;
}

.btn-attendance {
    background: #ffc107;
    color: #333;
    margin-top: 5px;
}

.btn-attendance:hover {
    background: #e0a800;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #888;
}

.empty-state h3 {
    margin: 0;
    font-size: 1.3rem;
}

/* PRINT STYLES - LANDSCAPE ORIENTATION */
@media print {
    @page {
        size: landscape;
        margin: 0.5cm;
    }
    
    body {
        margin: 0;
        padding: 0;
    }
    
    .page-header,
    .btn-toggle,
    .btn-open,
    .btn-attendance {
        display: none !important;
    }
    
    .class-card-container {
        display: block;
        width: 100%;
    }
    
    .class-card {
        break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 10px;
    }
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .class-card-container {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .header-actions {
        width: 100%;
    }
    
    .btn-toggle {
        flex: 1;
    }
}
</style>

<div class="page-header">
    <h2 class="page-title">
        <?php echo $show_archive ? "📦 Archived Classes" : "📚 My Class Loads"; ?>
    </h2>
    <div class="header-actions">
        <?php if($show_archive): ?>
            <button onclick="loadZone('grading-sheet-ajax.php', this)" class="btn-toggle btn-current">
                ← View Current
            </button>
        <?php else: ?>
            <button onclick="loadZone('grading-sheet-ajax.php?view=archive', this)" class="btn-toggle btn-archive">
                📦 View Archive
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($sections)): ?>
    <div class="empty-state">
        <h3>No classes found in <?php echo $show_archive ? 'Archive' : 'Current Year'; ?>.</h3>
        <p style="color:#999; margin-top:10px;">
            <?php if($show_archive): ?>
                Your archived classes will appear here.
            <?php else: ?>
                Contact the admin if you should have classes assigned.
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div class="class-card-container">
        <?php foreach ($sections as $row): ?>
            <div class="class-card">
                <div class="card-header">
                    <div class="cc-code"><?php echo htmlspecialchars($row['code']); ?></div>
                    <div class="cc-section"><?php echo htmlspecialchars($row['section']); ?></div>
                </div>
                
                <p class="cc-description"><?php echo htmlspecialchars($row['description']); ?></p>
                
                <div class="badge-container">
                    <span class="badge bg-track"><?php echo htmlspecialchars($row['track']); ?></span>
                    <span class="badge bg-year"><?php echo htmlspecialchars($row['year_level']); ?></span>
                </div>
                
                <hr class="card-divider">
                
                <button class="btn-open" onclick="loadZone('section-grades.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                    📊 Open Gradebook
                </button>
                
                <?php if(!$show_archive): ?>
                <button class="btn-open btn-attendance" 
                        onclick="loadZone('attendance.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                    📋 Attendance
                </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>