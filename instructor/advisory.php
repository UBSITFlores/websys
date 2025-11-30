<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    http_response_code(403);
    echo "Access Denied.";
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$inst_id = $_SESSION['id'] ?? 0;
// If SESSION['id'] is missing, fetch it using Account ID
if ($inst_id == 0) {
    $stmt = $pdo->prepare("SELECT id FROM account WHERE account_id = ?");
    $stmt->execute([$_SESSION['ACCOUNTID']]);
    $inst_id = $stmt->fetchColumn();
}

// 1. GET ADVISORY SECTION
$stmt = $pdo->prepare("SELECT * FROM section_list WHERE adviser_id = ?");
$stmt->execute([$inst_id]);
$my_section = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. GENERATE RANKING (If Section Found)
$ranking = [];
if ($my_section) {
    $sec_name   = $my_section['section_name'];
    $year_level = $my_section['year_level'];
    $period     = $_GET['period'] ?? '1';

    // Find students enrolled in ANY subject belonging to this Section Name + Year
    $sql = "SELECT DISTINCT s.student_id, a.fname, a.lname 
            FROM students s
            JOIN account a ON s.student_id = a.id
            JOIN enrollments e ON s.student_id = e.student_id
            JOIN sections sec ON e.section_id = sec.id
            WHERE sec.section = ? AND sec.year_level = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sec_name, $year_level]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate Grades
    foreach ($students as $s) {
        $sid = $s['student_id'];

        if ($period == '5') { // Final
            $g_sql  = "SELECT AVG(grade) FROM grades WHERE student_id = ?";
            $g_stmt = $pdo->prepare($g_sql);
            $g_stmt->execute([$sid]);
        } else { // Quarterly
            $g_sql  = "SELECT AVG(grade) FROM grades WHERE student_id = ? AND quarter = ?";
            $g_stmt = $pdo->prepare($g_sql);
            $g_stmt->execute([$sid, $period]);
        }

        $gwa = $g_stmt->fetchColumn();

        if ($gwa > 0) {
            $ranking[] = [
                'name'    => $s['lname'] . ', ' . $s['fname'],
                'average' => round($gwa, 2)
            ];
        }
    }

    // Sort Highest to Lowest (Bubble Sort)
    for ($i = 0; $i < count($ranking); $i++) {
        for ($j = $i + 1; $j < count($ranking); $j++) {
            if ($ranking[$j]['average'] > $ranking[$i]['average']) {
                $temp         = $ranking[$i];
                $ranking[$i]  = $ranking[$j];
                $ranking[$j]  = $temp;
            }
        }
    }
}
?>

<div class="form-card">
    <h2 class="advisory-title">My Advisory Class</h2>

    <?php if (!$my_section): ?>
        <div class="advisory-empty">
            <h3 class="advisory-empty-title">No Advisory Class Assigned</h3>
            <p>Please contact the Administrator to be assigned as a Class Adviser.</p>
        </div>
    <?php else: ?>
        <div class="advisory-header-box">
            <h3 class="advisory-header-title">
                <?php echo htmlspecialchars($my_section['year_level'] . ' - ' . $my_section['section_name']); ?>
            </h3>
            <p class="advisory-header-track">
                Track: <strong><?php echo htmlspecialchars($my_section['track']); ?></strong>
            </p>
        </div>

        <div class="no-print advisory-controls">
            <label class="advisory-label">Grading Period:</label>
            <select id="adv_period" onchange="loadAdvisory()" class="advisory-select">
                <option value="1" <?php if (($period ?? 1) == 1) echo 'selected'; ?>>1st Quarter / Prelim</option>
                <option value="2" <?php if (($period ?? 1) == 2) echo 'selected'; ?>>2nd Quarter / Midterm</option>
                <option value="3" <?php if (($period ?? 1) == 3) echo 'selected'; ?>>3rd Quarter / Pre-Fi</option>
                <option value="4" <?php if (($period ?? 1) == 4) echo 'selected'; ?>>4th Quarter / Finals</option>
                <option value="5" <?php if (($period ?? 1) == 5) echo 'selected'; ?>>General Average (Final)</option>
            </select>
            <button onclick="window.print()" class="btn-save btn-advisory-print">Print Honor List</button>
        </div>

        <table class="rank-table">
            <thead>
            <tr>
                <th class="rank-rank-header">Rank</th>
                <th>Student Name</th>
                <th class="rank-average-cell">General Average</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($ranking)): ?>
                <tr>
                    <td colspan="3" class="rank-empty">No grades found for this period.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($ranking as $i => $r): ?>
                    <?php
                    $rank = $i + 1;
                    $cls  = ($rank == 1) ? "top-1" : "";
                    ?>
                    <tr class="<?php echo $cls; ?>">
                        <td class="rank-rank-cell"><?php echo $rank; ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td class="rank-average-cell"><?php echo $r['average']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function loadAdvisory() {
    let p = document.getElementById('adv_period').value;
    loadZone('advisory.php?period=' + p);
}
</script>
