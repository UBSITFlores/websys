<?php
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    http_response_code(403);
    echo "Session Expired.";
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$section_name = $_GET['section'] ?? '';
$subject_code = $_GET['code'] ?? '';

$stmt = $pdo->prepare("SELECT id, description, track, year_level FROM sections WHERE section = ? AND code = ? LIMIT 1");
$stmt->execute([$section_name, $subject_code]);
$section_data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$section_data) {
    echo "<div style='padding:20px; color:red;'>Error: Section not found.</div>";
    exit;
}
$section_id = $section_data['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grades_input = $_POST['grades'] ?? [];

    $sql = "INSERT INTO grades (student_id, section_id, quarter, grade) 
            VALUES (:sid, :sec, :q, :g)
            ON DUPLICATE KEY UPDATE grade = :g";
    
    $stmt = $pdo->prepare($sql);

    foreach($grades_input as $student_id => $quarters) {
        foreach($quarters as $q => $grade_value) {
            $val = trim($grade_value);
            if($val !== "") {
                $stmt->execute([
                    ':sid' => $student_id,
                    ':sec' => $section_id,
                    ':q'   => $q,
                    ':g'   => $val
                ]);
            }
        }
    }
    echo "SAVED";
    exit;
}

$sql_students = "SELECT a.id, a.account_id, a.fname, a.lname 
                 FROM enrollments e
                 JOIN account a ON e.student_id = a.id
                 WHERE e.section_id = ?
                 ORDER BY a.lname ASC";
$stmt = $pdo->prepare($sql_students);
$stmt->execute([$section_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_grades = "SELECT student_id, quarter, grade FROM grades WHERE section_id = ?";
$stmt = $pdo->prepare($sql_grades);
$stmt->execute([$section_id]);
$existing_grades = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existing_grades[$row['student_id']][$row['quarter']] = $row['grade'];
}
?>

<style>
    .grade-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .grade-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .grade-header h2 { margin: 0; color: #198754; font-size: 1.5rem; }
    
    .grade-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .grade-table th { background: #f8f9fa; padding: 10px; text-align: center; border-bottom: 2px solid #ddd; font-size: 0.9rem; vertical-align: middle; }
    .grade-table td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .grade-table tr:hover { background: #fafafa; }

    .grade-input { width: 50px; text-align: center; padding: 5px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; }
    .grade-input:not([readonly]) { background: #fff; border-color: #198754; font-weight: bold; }
    .grade-input:focus { outline: none; box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.25); }

    .ctrl-btn { font-size: 0.75rem; padding: 2px 6px; margin: 0 2px; border: 1px solid #ccc; background: #fff; cursor: pointer; border-radius: 3px; }
    .ctrl-btn:hover { background: #eee; }
    .ctrl-active { background: #198754; color: white; border-color: #198754; }

    .result-pass { color: #198754; font-weight: bold; }
    .result-fail { color: #dc3545; font-weight: bold; }
    
    #bulk-container { background: #e7f1ff; border: 1px solid #b6d4fe; color: #084298; padding: 10px; margin-bottom: 15px; border-radius: 5px; display: none; align-items: center; gap: 10px; }
    #bulk-input { flex: 1; padding: 5px; border: 1px solid #b6d4fe; border-radius: 3px; }
</style>

<div class="grade-box">
    <div class="grade-header">
        <div>
            <h2>Grading Sheet: <?php echo htmlspecialchars($section_name); ?></h2>
            <p style="color:#666; margin:5px 0;">Subject: <strong><?php echo htmlspecialchars($subject_code); ?></strong> | Track: <?php echo htmlspecialchars($section_data['track']); ?></p>
        </div>
        <button onclick="loadZone('grading-sheet-ajax.php', this)" style="padding:8px 15px; cursor:pointer; border:1px solid #ccc; background:#fff; border-radius:4px;">Back</button>
    </div>

    <input type="hidden" id="hidden_sec_name" value="<?php echo htmlspecialchars($section_name); ?>">
    <input type="hidden" id="hidden_subj_code" value="<?php echo htmlspecialchars($subject_code); ?>">

    <div id="bulk-container">
        <strong>Bulk Input (Q<span id="bulk-q-label"></span>):</strong>
        <input type="text" id="bulk-input" placeholder="Enter grades separated by space (e.g. 85 90 88 92)">
        <button onclick="applyBulk()" style="padding:5px 10px; background:#0d6efd; color:white; border:none; border-radius:3px; cursor:pointer;">Apply</button>
        <button onclick="closeBulk()" style="padding:5px 10px; background:#6c757d; color:white; border:none; border-radius:3px; cursor:pointer;">Cancel</button>
    </div>

    <form id="gradingForm" onsubmit="event.preventDefault(); saveGrades();">
        <table class="grade-table">
            <thead>
                <tr>
                    <th style="text-align:left;">Student Name</th>
                    <?php for($q=1; $q<=4; $q++): ?>
                    <th>
                        Q<?php echo $q; ?><br>
                        <div style="margin-top:5px;">
                            <button type="button" class="ctrl-btn" onclick="enableManual(<?php echo $q; ?>, this)">Manual</button>
                            <button type="button" class="ctrl-btn" onclick="enableBulk(<?php echo $q; ?>, this)">Bulk</button>
                        </div>
                    </th>
                    <?php endfor; ?>
                    <th style="background:#e9ecef;">Final</th>
                    <th style="background:#e9ecef;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($students)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:#999;">No students enrolled yet.</td></tr>
                <?php else: ?>
                    <?php foreach($students as $stu): 
                        $sid = $stu['id'];
                        $g1 = $existing_grades[$sid][1] ?? '';
                        $g2 = $existing_grades[$sid][2] ?? '';
                        $g3 = $existing_grades[$sid][3] ?? '';
                        $g4 = $existing_grades[$sid][4] ?? '';
                    ?>
                    <tr class="student-row">
                        <td style="text-align:left;">
                            <strong style="color:#002D72;"><?php echo htmlspecialchars($stu['lname'] . ', ' . $stu['fname']); ?></strong><br>
                            <small style="color:#888;"><?php echo htmlspecialchars($stu['account_id']); ?></small>
                        </td>
                        <td><input type="text" class="grade-input q1" name="grades[<?php echo $sid; ?>][1]" value="<?php echo $g1; ?>" readonly oninput="calcRow(this)"></td>
                        <td><input type="text" class="grade-input q2" name="grades[<?php echo $sid; ?>][2]" value="<?php echo $g2; ?>" readonly oninput="calcRow(this)"></td>
                        <td><input type="text" class="grade-input q3" name="grades[<?php echo $sid; ?>][3]" value="<?php echo $g3; ?>" readonly oninput="calcRow(this)"></td>
                        <td><input type="text" class="grade-input q4" name="grades[<?php echo $sid; ?>][4]" value="<?php echo $g4; ?>" readonly oninput="calcRow(this)"></td>
                        <td style="font-weight:bold; background:#f8f9fa;" class="final-grade">-</td>
                        <td style="font-size:0.9rem; background:#f8f9fa;" class="remarks">-</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top:20px; text-align:right;">
            <span id="save_status" style="margin-right:15px; font-weight:bold;"></span>
            <button type="submit" class="btn-save" style="background:#198754; color:white; padding:12px 30px; border:none; border-radius:5px; font-size:1.1rem; cursor:pointer;">Save Grades</button>
        </div>
    </form>
</div>