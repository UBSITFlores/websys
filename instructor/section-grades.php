<?php
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    http_response_code(403); echo "Session Expired."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$section_name = $_GET['section'] ?? '';
$subject_code = $_GET['code'] ?? '';

// 1. GET SECTION DETAILS
$stmt = $pdo->prepare("SELECT id, description, track, year_level, semester FROM sections WHERE section = ? AND code = ? LIMIT 1");
$stmt->execute([$section_name, $subject_code]);
$section_data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$section_data) { echo "<div style='padding:20px; color:red;'>Error: Section not found.</div>"; exit; }
$section_id = $section_data['id'];

// --- DETERMINE GRADING COLUMNS ---
$track = strtolower($section_data['track']);
$grading_periods = [];

if ($track == 'kinder' || $track == 'junior high school') {
    $grading_periods = [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4'];
} else {
    $grading_periods = [1 => 'Prelim', 2 => 'Midterm', 3 => 'Finals'];
}

// 2. HANDLE SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['grades'])) {
        $sql = "INSERT INTO grades (student_id, section_id, quarter, grade) VALUES (:sid, :sec, :q, :g) ON DUPLICATE KEY UPDATE grade = :g";
        $stmt = $pdo->prepare($sql);
        foreach($_POST['grades'] as $sid => $periods) {
            foreach($periods as $q => $val) {
                if(trim($val) !== "") {
                    $stmt->execute([':sid'=>$sid, ':sec'=>$section_id, ':q'=>$q, ':g'=>trim($val)]);
                }
            }
        }
    }
    
    if(isset($_POST['behavior'])) {
        $acc_stmt = $pdo->prepare("SELECT id FROM account WHERE account_id = ?");
        $acc_stmt->execute([$_SESSION['ACCOUNTID']]);
        $real_inst_id = $acc_stmt->fetchColumn();

        $sql = "INSERT INTO behavior_records (student_id, section_id, instructor_id, grading_period, attendance_score, conduct_grade) 
                VALUES (:sid, :sec, :iid, :q, :att, :con)
                ON DUPLICATE KEY UPDATE attendance_score = :att, conduct_grade = :con";
        $stmt = $pdo->prepare($sql);
        
        foreach($_POST['behavior'] as $sid => $periods) {
            foreach($periods as $q => $data) {
                $att = $data['att'] ?? null;
                $con = $data['con'] ?? null;
                if($att !== "" || $con !== "") {
                    $stmt->execute([':sid'=>$sid, ':sec'=>$section_id, ':iid'=>$real_inst_id, ':q'=>$q, ':att'=>($att===""?null:$att), ':con'=>($con===""?null:$con)]);
                }
            }
        }
    }
    echo "SAVED"; exit;
}

// 3. FETCH DATA
$sql_students = "SELECT a.id, a.account_id, a.fname, a.lname FROM enrollments e JOIN account a ON e.student_id = a.id WHERE e.section_id = ? ORDER BY a.lname ASC";
$stmt = $pdo->prepare($sql_students);
$stmt->execute([$section_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_grades = "SELECT student_id, quarter, grade FROM grades WHERE section_id = ?";
$stmt = $pdo->prepare($sql_grades);
$stmt->execute([$section_id]);
$existing_grades = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $existing_grades[$row['student_id']][$row['quarter']] = $row['grade']; }

$sql_behav = "SELECT student_id, grading_period, attendance_score, conduct_grade FROM behavior_records WHERE section_id = ?";
$stmt = $pdo->prepare($sql_behav);
$stmt->execute([$section_id]);
$existing_behav = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $existing_behav[$row['student_id']][$row['grading_period']] = ['att' => $row['attendance_score'], 'con' => $row['conduct_grade']]; }
?>

<style>
    .grade-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .grade-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .grade-header h2 { margin: 0; color: #002D72; font-size: 1.5rem; }
    .grade-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .grade-table th { background: #f8f9fa; padding: 10px; text-align: center; border-bottom: 2px solid #ddd; font-size: 0.85rem; }
    .grade-table td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .grade-input { width: 45px; text-align: center; padding: 5px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; }
    .grade-input:not([readonly]) { background: #fff; border-color: #002D72; font-weight: bold; }
    
    .mode-tabs { display:flex; gap:5px; margin-bottom:10px; }
    .mode-btn { padding:8px 15px; border:1px solid #ccc; background:#fff; cursor:pointer; border-radius:4px 4px 0 0; border-bottom:none; font-weight:bold; color:#666; }
    .mode-btn.active { background:#002D72; color:white; border-color:#002D72; }
    .col-behav { display: none; }
    #bulk-container { background: #e7f1ff; border: 1px solid #b6d4fe; color: #084298; padding: 10px; margin-bottom: 15px; border-radius: 5px; display: none; align-items: center; gap: 10px; }

    /* --- PRINT-ONLY HEADER STYLE --- */
    .print-header { display: none; }

    @media print {
        /* Hide Web Interface */
        .grade-header, .ctrl-div, .save-bar, #bulk-container, .sidebar-right, .header, .mode-tabs { 
            display: none !important; 
        }
        
        /* Show Custom Print Header */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            color: black;
        }
        .print-header h1 { font-size: 18pt; margin: 0 0 5px 0; }
        .print-header h3 { font-size: 14pt; margin: 0; font-weight: normal; }

        /* Reset Layout */
        body, .content-zone, .grade-box { background: white; margin: 0; padding: 0; box-shadow: none; width: 100%; }
        .grade-table { border: 1px solid black; }
        .grade-table th, .grade-table td { border: 1px solid black !important; color: black !important; }
        .grade-input { border: none; background: transparent; text-align: center; color: black !important; }
        
        /* Optional: Force landscape if needed */
        @page { size: landscape; margin: 1cm; }
    }
</style>

<div class="grade-box">
    
    <div class="grade-header">
        <div>
            <h2><?php echo htmlspecialchars($section_name); ?></h2>
            <p style="color:#666; margin:5px 0;">
                <strong><?php echo htmlspecialchars($subject_code); ?></strong> | 
                <?php echo htmlspecialchars($section_data['track']); ?>
            </p>
        </div>
        <div>
            <button onclick="window.print()" style="padding:8px 15px; cursor:pointer; background:#6c757d; color:white; border:none; border-radius:4px; margin-right:5px;">Print / PDF</button>
            <button onclick="loadZone('grading-sheet-ajax.php', this)" style="padding:8px 15px; cursor:pointer; border:1px solid #ccc; background:#fff; border-radius:4px;">Back</button>
        </div>
    </div>

    <div class="print-header">
        <h1>
            <?php echo htmlspecialchars($section_data['year_level']); ?> - 
            <?php echo htmlspecialchars($section_data['track']); ?> | 
            <?php echo htmlspecialchars($section_name); ?> Grading Sheet
        </h1>
        <h3><?php echo htmlspecialchars($subject_code . ' - ' . $section_data['description']); ?></h3>
        <p>Instructor: <?php echo htmlspecialchars($_SESSION['FNAME'] . ' ' . $_SESSION['LNAME']); ?></p>
    </div>

    <input type="hidden" id="hidden_sec_name" value="<?php echo htmlspecialchars($section_name); ?>">
    <input type="hidden" id="hidden_subj_code" value="<?php echo htmlspecialchars($subject_code); ?>">

    <div class="mode-tabs">
        <button class="mode-btn active" onclick="switchMode('academic', this)">Academic Grades</button>
        <button class="mode-btn" onclick="switchMode('behavior', this)">Attendance & Conduct</button>
    </div>

    <div id="bulk-container">
        <strong>Bulk Input (<span id="bulk-q-label"></span>):</strong>
        <input type="text" id="bulk-input" placeholder="e.g. 85 90 88 92" style="flex:1; padding:5px;">
        <button onclick="applyBulk()" style="padding:5px 10px; background:#0d6efd; color:white; border:none; cursor:pointer;">Apply</button>
        <button onclick="closeBulk()" style="padding:5px 10px; background:#6c757d; color:white; border:none; cursor:pointer;">Cancel</button>
    </div>

    <form id="gradingForm" onsubmit="event.preventDefault(); saveGrades();">
        <table class="grade-table">
            <thead>
                <tr>
                    <th style="text-align:left; width:200px;">Student Name</th>
                    <?php foreach($grading_periods as $key => $label): ?>
                    <th>
                        <?php echo $label; ?><br>
                        <div class="col-acad ctrl-div" style="margin-top:5px;">
                            <button type="button" style="font-size:0.7em; cursor:pointer;" onclick="enableManual('acad', <?php echo $key; ?>)">Edit</button>
                            <button type="button" style="font-size:0.7em; cursor:pointer;" onclick="enableBulk(<?php echo $key; ?>)">Bulk</button>
                        </div>
                        <div class="col-behav ctrl-div" style="margin-top:5px;">
                            <button type="button" style="font-size:0.7em; cursor:pointer;" onclick="enableManual('behav', <?php echo $key; ?>)">Edit</button>
                        </div>
                    </th>
                    <?php endforeach; ?>
                    <th style="background:#e9ecef;">Final</th>
                    <th style="background:#e9ecef;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($students)): ?>
                    <tr><td colspan="<?php echo count($grading_periods)+3; ?>" style="text-align:center; padding:30px; color:#999;">No students enrolled.</td></tr>
                <?php else: ?>
                    <?php foreach($students as $stu): 
                        $sid = $stu['id'];
                    ?>
                    <tr class="student-row">
                        <td style="text-align:left;">
                            <strong style="color:#002D72;"><?php echo htmlspecialchars($stu['lname'] . ', ' . $stu['fname']); ?></strong><br>
                            <small><?php echo htmlspecialchars($stu['account_id']); ?></small>
                        </td>
                        <?php foreach($grading_periods as $key => $label): 
                            $g = $existing_grades[$sid][$key] ?? '';
                            $att = $existing_behav[$sid][$key]['att'] ?? '';
                            $con = $existing_behav[$sid][$key]['con'] ?? '';
                        ?>
                        <td style="white-space:nowrap;">
                            <input type="text" class="grade-input col-acad score-val q<?php echo $key; ?>" name="grades[<?php echo $sid; ?>][<?php echo $key; ?>]" value="<?php echo $g; ?>" readonly oninput="calcRow(this)">
                            <div class="col-behav">
                                <input type="text" class="grade-input b-att q<?php echo $key; ?>" name="behavior[<?php echo $sid; ?>][<?php echo $key; ?>][att]" value="<?php echo $att; ?>" placeholder="Att" readonly style="width:35px;">
                                <input type="text" class="grade-input b-con q<?php echo $key; ?>" name="behavior[<?php echo $sid; ?>][<?php echo $key; ?>][con]" value="<?php echo $con; ?>" placeholder="Con" readonly style="width:35px;">
                            </div>
                        </td>
                        <?php endforeach; ?>
                        <td style="font-weight:bold; background:#f8f9fa;" class="final-grade">-</td>
                        <td style="font-size:0.9rem; background:#f8f9fa;" class="remarks">-</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="save-bar" style="margin-top:20px; text-align:right;">
            <span id="save_status" style="margin-right:15px; font-weight:bold;"></span>
            <button type="submit" class="btn-save" style="padding:12px 30px;">Save All Changes</button>
        </div>
    </form>
</div>