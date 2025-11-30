<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Session Expired."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// FETCH DATA
$student = null;
$grades = [];
$attendance = [];

if (isset($_GET['student_id'])) {
    $sid_input = $_GET['student_id'];
    
    // 1. Get Student Info
    $stmt = $pdo->prepare("SELECT s.*, a.account_id 
                           FROM students s 
                           JOIN account a ON s.student_id = a.id 
                           WHERE a.account_id = ?");
    $stmt->execute([$sid_input]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $pk = $student['student_id'];

        // 2. Get Grades & Subjects
        $sql = "SELECT sub.code, sub.description, g.quarter, g.grade, sec.section, sec.school_year, sec.semester 
                FROM enrollments e
                JOIN sections sec ON e.section_id = sec.id
                JOIN subjects sub ON sec.code = sub.code
                LEFT JOIN grades g ON g.student_id = e.student_id AND g.section_id = sec.id
                WHERE e.student_id = ?
                ORDER BY sec.school_year DESC, sub.code ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pk]);
        $raw_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. ORGANIZE DATA (Simple Loops)
        for ($i = 0; $i < count($raw_grades); $i++) {
            $r = $raw_grades[$i];
            $sy = $r['school_year'];
            $code = $r['code'];

            if (!isset($grades[$sy])) { $grades[$sy] = []; }
            if (!isset($grades[$sy][$code])) {
                $grades[$sy][$code] = [
                    'desc' => $r['description'],
                    'sec' => $r['section'],
                    'sem' => $r['semester'],
                    'q1'=>'-','q2'=>'-','q3'=>'-','q4'=>'-','final'=>'-','remarks'=>''
                ];
            }

            if ($r['grade'] !== null) {
                $grades[$sy][$code]['q' . $r['quarter']] = $r['grade'];
            }
        }

        // 4. CALCULATE AVERAGES
        foreach($grades as $sy => $subs) {
            foreach($subs as $code => $data) {
                if(is_numeric($data['q1']) && is_numeric($data['q2']) && is_numeric($data['q3']) && is_numeric($data['q4'])) {
                    $avg = ($data['q1'] + $data['q2'] + $data['q3'] + $data['q4']) / 4;
                    $grades[$sy][$code]['final'] = number_format($avg, 2);
                    $grades[$sy][$code]['remarks'] = ($avg >= 75) ? 'PASSED' : 'FAILED';
                }
            }
        }

        // 5. GET ATTENDANCE
        $att_sql = "SELECT grading_period, AVG(attendance_score) as att, AVG(conduct_grade) as con 
                    FROM behavior_records WHERE student_id = ? GROUP BY grading_period";
        $stmt = $pdo->prepare($att_sql);
        $stmt->execute([$pk]);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $attendance[$row['grading_period']] = $row;
        }
    }
}
?>

<div class="form-card" style="max-width: 1000px;">
    <div class="no-print">
        <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Student Records (Report Card)</h2>
        
        <form method="GET" style="display:flex; gap:10px; margin-bottom:20px; background:#f0f8ff; padding:15px; border-radius:8px;">
            <input type="text" name="student_id" value="<?php echo $_GET['student_id'] ?? ''; ?>" placeholder="Enter Student ID (e.g. 20260001)" style="padding:10px; flex:1; border:1px solid #aaa; border-radius:4px;">
            <button type="button" onclick="loadZone('student_records.php?' + new URLSearchParams(new FormData(this.form)).toString())" class="btn-save" style="width:auto; padding:10px 30px;">Search Record</button>
        </form>
    </div>

    <?php if($student): ?>
        
        <style>
            .report-header { text-align: center; margin-bottom: 30px; display: none; }
            .student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
            .info-group label { font-weight: bold; color: #666; font-size: 0.9rem; display: block; }
            .info-group span { font-weight: bold; color: #002D72; font-size: 1.1rem; }
            
            .grades-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; margin-bottom: 30px; }
            .grades-table th { background: #002D72; color: white; padding: 10px; text-align: center; border: 1px solid #001f52; }
            .grades-table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
            .grades-table th:first-child, .grades-table td:first-child { text-align: left; }
            
            @media print {
                .no-print, .sidebar-right, .header { display: none !important; }
                body, .content-zone, .form-card { background: white; margin: 0; padding: 0; width: 100%; box-shadow: none; }
                .report-header { display: block; }
                .grades-table th { background: #eee !important; color: black !important; border: 1px solid black; }
                .grades-table td { border: 1px solid black; }
            }
        </style>

        <div class="print-area">
            <div class="report-header">
                <h1>University of Saint Louis</h1>
                <p>OFFICIAL REPORT CARD / STUDENT RECORD</p>
            </div>

            <div style="text-align:right; margin-bottom:10px;" class="no-print">
                <button onclick="window.print()" class="btn-save" style="background:#6c757d;">🖨️ Print Report Card</button>
            </div>

            <div class="student-info">
                <div class="info-group">
                    <label>Student Name:</label>
                    <span><?php echo htmlspecialchars($student['familyname'] . ', ' . $student['fname'] . ' ' . $student['mname']); ?></span>
                </div>
                <div class="info-group">
                    <label>Student ID / LRN:</label>
                    <span><?php echo htmlspecialchars($_GET['student_id']); ?> / <?php echo htmlspecialchars($student['lrn']); ?></span>
                </div>
                <div class="info-group">
                    <label>Track / Grade:</label>
                    <span><?php echo htmlspecialchars(ucfirst($student['track']) . ' - ' . $student['grade_level']); ?></span>
                </div>
            </div>

            <?php foreach($grades as $sy => $subs): ?>
                <h3 style="color:#002D72; border-bottom:1px solid #ccc;">School Year: <?php echo $sy; ?></h3>
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th style="width:30%;">Subject</th>
                            <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
                            <th>Final</th><th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($subs as $code => $d): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($code); ?></strong><br>
                                <small><?php echo htmlspecialchars($d['desc']); ?></small>
                            </td>
                            <td><?php echo $d['q1']; ?></td>
                            <td><?php echo $d['q2']; ?></td>
                            <td><?php echo $d['q3']; ?></td>
                            <td><?php echo $d['q4']; ?></td>
                            <td style="font-weight:bold;"><?php echo $d['final']; ?></td>
                            <td style="font-size:0.85rem; font-weight:bold; color:<?php echo ($d['remarks']=='PASSED'?'green':'red'); ?>">
                                <?php echo $d['remarks']; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
            
            <h3 style="color:#002D72; margin-top:30px;">Observed Values</h3>
            <table class="grades-table" style="width:50%;">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Attendance %</th>
                        <th>Conduct Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for($i=1; $i<=4; $i++): 
                        $att = $attendance[$i]['att'] ?? '-';
                        $con = $attendance[$i]['con'] ?? '-';
                        if(is_numeric($att)) $att = number_format($att,0).'%';
                    ?>
                    <tr>
                        <td>Quarter <?php echo $i; ?></td>
                        <td><?php echo $att; ?></td>
                        <td><?php echo $con; ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <div class="report-header" style="text-align:left; margin-top:50px; border:none;">
                <p><strong>Certified True Copy:</strong></p>
                <br><br>
                <p>__________________________<br>School Registrar</p>
            </div>
        </div>

    <?php elseif(isset($_GET['student_id'])): ?>
        <div style="padding:30px; text-align:center; color:#dc3545; background:#fff0f0; border-radius:8px;">
            <strong>Student not found.</strong> Please check the ID number.
        </div>
    <?php endif; ?>
</div>