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

<h2 class="class-load-title">My Class Loads</h2>

<?php if (empty($sections)): ?>
    <div class="empty-class-state">
        <h3>No classes assigned yet.</h3>
        <p>Contact the Management office to have subjects assigned to you.</p>
    </div>
<?php else: ?>
    <div class="class-card-container">
        <?php foreach ($sections as $row): ?>
            <div class="class-card">
                <div class="cc-header">
                    <div class="cc-code">
                        <?php echo htmlspecialchars($row['code']); ?>
                    </div>
                    <div class="cc-section">
                        <?php echo htmlspecialchars($row['section']); ?>
                    </div>
                </div>

                <div class="cc-title">
                    <?php echo htmlspecialchars($row['description']); ?>
                </div>

                <div>
                    <span class="badge bg-track">
                        <?php echo htmlspecialchars($row['track']); ?>
                    </span>
                    <span class="badge bg-year">
                        <?php echo htmlspecialchars($row['year_level']); ?>
                    </span>
                </div>

                <div class="cc-meta">
                    <i class="fas fa-clock"></i>
                    <?php echo htmlspecialchars($row['schedule_time']); ?><br>
                    <i class="fas fa-door-open"></i>
                    <?php echo htmlspecialchars($row['room'] ?? "TBA"); ?>
                </div>

                <div class="class-card-actions">
                    <button
                        class="btn-open"
                        onclick="loadZone('section-grades.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                        Grades
                    </button>

                    <button
                        class="btn-open btn-open-warning"
                        onclick="loadZone('attendance.php?section=<?php echo urlencode($row['section']); ?>&code=<?php echo urlencode($row['code']); ?>', null)">
                        Attendance
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>