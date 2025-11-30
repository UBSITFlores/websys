<?php
require_once '../functions/instructor_function.php';
session_start();

// 1. SECURITY CHECK
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    echo "<div style='color:red; padding:20px;'>Session Expired. Please login again.</div>";
    exit;
}

$account_id = $_SESSION['ACCOUNTID'] ?? null;
$instructor = new Instructor();

// 2. GET CLASSES (Now fetching Track and Year Level too)
// Note: Ensure your Instructor class in functions/instructor_function.php is fetching * // or specifically selecting these new columns. 
// If not, we can use a direct query here for the "Caveman" approach:
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$stmt = $pdo->prepare("SELECT * FROM sections WHERE instructor_id = (SELECT id FROM account WHERE account_id = :aid) ORDER BY code ASC");
$stmt->execute([':aid' => $account_id]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
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
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .cc-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 10px;
    }
    
    .cc-code {
        font-size: 1.2rem;
        font-weight: bold;
        color: #198754; /* Green for Instructor */
    }
    
    .cc-section {
        background: #e8f5e9;
        color: #146c43;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: bold;
    }

    .cc-title {
        font-size: 1rem;
        color: #333;
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .cc-meta {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 15px;
        padding-top: 10px;
        border-top: 1px solid #f0f0f0;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        margin-right: 5px;
    }
    .bg-track { background-color: #cfe2ff; color: #084298; }
    .bg-year { background-color: #fff3cd; color: #664d03; }

    .btn-open {
        display: block;
        width: 100%;
        text-align: center;
        background: #198754;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-open:hover { background: #146c43; }
</style>

<h2 style="color:#198754; border-bottom:2px solid #eee; padding-bottom:10px;">My Class Loads</h2>

<?php if (empty($sections)): ?>
    <div style="text-align:center; padding:50px; background:#fff; border-radius:8px; color:#888;">
        <h3>No classes assigned yet.</h3>
        <p>Contact the Management office to have subjects assigned to you.</p>
    </div>
<?php else: ?>
    <div class="class-card-container">
        <?php foreach ($sections as $row): ?>
            <div class="class-card">
                <div class="cc-header">
                    <div class="cc-code"><?php echo htmlspecialchars($row['code']); ?></div>
                    <div class="cc-section"><?php echo htmlspecialchars($row['section']); ?></div>
                </div>
                
                <div class="cc-title">
                    <?php echo htmlspecialchars($row['description']); ?>
                </div>

                <div>
                    <span class="badge bg-track"><?php echo htmlspecialchars($row['track']); ?></span>
                    <span class="badge bg-year"><?php echo htmlspecialchars($row['year_level']); ?></span>
                </div>

                <div class="cc-meta">
                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($row['schedule_time']); ?><br>
                    <i class="fas fa-door-open"></i> <?php echo htmlspecialchars($row['room'] ?? 'TBA'); ?>
                </div>

                <button class="btn-open" 
                    onclick="loadZone('section-grades.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                    Open Gradebook
                </button>
                <div style="display:flex; gap:5px;">
                    <button class="btn-open" 
                        onclick="loadZone('section-grades.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                        Grades
                    </button>
                    <button class="btn-open" style="background:#ffc107; color:#333;"
                        onclick="loadZone('attendance.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                        Attendance
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>