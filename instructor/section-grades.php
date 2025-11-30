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

// --- DETERMINE COLUMNS (CAVEMAN STYLE) ---
$track = strtolower($section_data['track']);
$columns = [];
$col_keys = []; // This maps the name to the database ID (1, 2, 3...)

if ($track == 'senior high school') {
    // SHS = 3 Terms
    $columns = ['Prelim', 'Midterm', 'Finals'];
    $col_keys = [1, 2, 3]; 
} else {
    // Kinder & JHS = 4 Quarters
    $columns = ['Q1', 'Q2', 'Q3', 'Q4'];
    $col_keys = [1, 2, 3, 4];
}

// 2. HANDLE SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SAVE GRADES
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
    
    // SAVE BEHAVIOR
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
                // Save if not empty
                if($att != "" || $con != "") {
                    // Handle empty strings as null
                    if($att == "") $att = null;
                    if($con == "") $con = null;
                    
                    $stmt->execute([':sid'=>$sid, ':sec'=>$section_id, ':iid'=>$real_inst_id, ':q'=>$q, ':att'=>$att, ':con'=>$con]);
                }
            }
        }
    }
    echo "SAVED"; exit;
}

// 3. FETCH DATA
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
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existing_grades[$row['student_id']][$row['quarter']] = $row['grade'];
}

$sql_behav = "SELECT student_id, grading_period, attendance_score, conduct_grade
              FROM behavior_records WHERE section_id = ?";
$stmt = $pdo->prepare($sql_behav);
$stmt->execute([$section_id]);
$existing_behav = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existing_behav[$row['student_id']][$row['grading_period']] = [
        'att' => $row['attendance_score'],
        'con' => $row['conduct_grade']
    ];
}
$col_count = count($columns);
?>
?>

<<div class="grade-box">
    <div class="grade-header">
        <div>
            <h2><?php echo htmlspecialchars($section_name); ?></h2>
            <p class="grade-subtitle">
                <strong><?php echo htmlspecialchars($subject_code); ?></strong> |
                <?php echo htmlspecialchars($section_data['track']); ?> |
                <?php echo htmlspecialchars($section_data['semester']); ?>
            </p>
        </div>
        <div class="grade-header-actions">
            <button onclick="window.print()" class="btn-print">Print / PDF</button>
            <button onclick="loadZone('grading-sheet-ajax.php', this)" class="btn-back">Back</button>
        </div>
    </div>

    <div class="print-header">
        <h1>
            <?php
            echo htmlspecialchars($section_data['year_level'] . " - " . $section_data['track']);
            ?>
            |
            <?php echo htmlspecialchars($section_name); ?> Grading Sheet
        </h1>
        <h3><?php echo htmlspecialchars($subject_code . ' - ' . $section_data['description']); ?></h3>
        <p>Instructor: <?php echo htmlspecialchars($_SESSION['FNAME'] . ' ' . $_SESSION['LNAME']); ?></p>
    </div>

    <input type="hidden" id="hidden_sec_name" value="<?php echo htmlspecialchars($section_name); ?>">
    <input type="hidden" id="hidden_subj_code" value="<?php echo htmlspecialchars($subject_code); ?>">

    <div class="mode-tabs">
        <button class="mode-btn active" onclick="switchMode('academic', this)">Academic Grades</button>
        <button class="mode-btn" onclick="switchMode('behavior', this)">Attendance &amp; Conduct</button>
    </div>

    <div id="bulk-container" class="grade-bulk">
        <strong>Bulk Input (<span id="bulk-q-label"></span>):</strong>
        <input type="text" id="bulk-input" class="bulk-input" placeholder="e.g. 85 90 88">
        <button type="button" onclick="applyBulk()" class="bulk-apply">Apply</button>
        <button type="button" onclick="closeBulk()" class="bulk-cancel">Cancel</button>
    </div>

    <form id="gradingForm" onsubmit="event.preventDefault(); saveGrades();">
        <table class="grade-table">
            <thead>
            <tr>
                <th class="grade-col-student">Student Name</th>

                <?php for ($i = 0; $i < $col_count; $i++): ?>
                    <?php $label = $columns[$i]; $db_key = $col_keys[$i]; ?>
                    <th>
                        <?php echo $label; ?><br>
                        <div class="col-acad ctrl-div">
                            <button type="button" onclick="enableManual('acad', <?php echo $db_key; ?>)">Edit</button>
                            <button type="button" onclick="enableBulk(<?php echo $db_key; ?>)">Bulk</button>
                        </div>
                        <div class="col-behav ctrl-div">
                            <button type="button" onclick="enableManual('behav', <?php echo $db_key; ?>)">Edit</button>
                        </div>
                    </th>
                <?php endfor; ?>

                <th class="final-col-header">Final</th>
                <th class="remarks-col-header">Remarks</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="<?php echo $col_count + 3; ?>" class="no-students">
                        No students enrolled.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $stu): ?>
                    <?php $sid = $stu['id']; ?>
                    <tr class="student-row">
                        <td class="grade-col-student">
                            <span class="grade-student-name">
                                <?php echo htmlspecialchars($stu['lname'] . ', ' . $stu['fname']); ?>
                            </span><br>
                            <span class="grade-student-id">
                                <?php echo htmlspecialchars($stu['account_id']); ?>
                            </span>
                        </td>

                        <?php for ($i = 0; $i < $col_count; $i++): ?>
                            <?php
                            $db_key = $col_keys[$i];
                            $g   = $existing_grades[$sid][$db_key] ?? '';
                            $att = $existing_behav[$sid][$db_key]['att'] ?? '';
                            $con = $existing_behav[$sid][$db_key]['con'] ?? '';
                            ?>
                            <td class="grade-cell-nowrap">
                                <input
                                    type="text"
                                    class="grade-input col-acad score-val q<?php echo $db_key; ?>"
                                    name="grades[<?php echo $sid; ?>][<?php echo $db_key; ?>]"
                                    value="<?php echo $g; ?>"
                                    readonly
                                    oninput="calcRow(this)"
                                >

                                <div class="col-behav">
                                    <input
                                        type="text"
                                        class="grade-input b-att q<?php echo $db_key; ?>"
                                        name="behavior[<?php echo $sid; ?>][<?php echo $db_key; ?>][att]"
                                        value="<?php echo $att; ?>"
                                        placeholder="Att"
                                        readonly
                                    >
                                    <input
                                        type="text"
                                        class="grade-input b-con q<?php echo $db_key; ?>"
                                        name="behavior[<?php echo $sid; ?>][<?php echo $db_key; ?>][con]"
                                        value="<?php echo $con; ?>"
                                        placeholder="Con"
                                        readonly
                                    >
                                </div>
                            </td>
                        <?php endfor; ?>

                        <td class="final-grade">-</td>
                        <td class="remarks">-</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="save-bar">
            <span id="save_status" class="save-status"></span>
            <button type="submit" class="btn-save">Save All Changes</button>
        </div>
    </form>
</div>