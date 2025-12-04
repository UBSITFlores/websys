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
                ORDER BY sec.school_year DESC, sec.semester DESC, sub.code ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pk]);
        $raw_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. ORGANIZE DATA (Group by SY)
        foreach($raw_grades as $r) {
            $sy = $r['school_year'];
            $code = $r['code'];
            
            if (!isset($grades[$sy])) { $grades[$sy] = []; }
            if (!isset($grades[$sy][$code])) {
                $grades[$sy][$code] = [
                    'desc' => $r['description'],
                    'sec' => $r['section'],
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
                $total = 0; $count = 0;
                for($q=1; $q<=4; $q++) {
                    if(is_numeric($data['q'.$q])) { $total += $data['q'.$q]; $count++; }
                }
                if($count > 0) {
                    $avg = $total / $count;
                    $grades[$sy][$code]['final'] = number_format($avg, 2);
                    $grades[$sy][$code]['remarks'] = ($avg >= 75) ? 'PASSED' : 'FAILED';
                }
            }
        }

        // 5. GET ATTENDANCE
        $stmt = $pdo->prepare("SELECT grading_period, attendance_score, conduct_grade FROM behavior_records WHERE student_id = ?");
        $stmt->execute([$pk]);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $attendance[$row['grading_period']] = $row;
        }
    }
}
?>

<style>
/* --- INLINE STYLES FOR AJAX LOADED CONTENT --- */
.form-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    max-width: 1200px;
    margin: 0 auto;
}

.record-title {
    color: #002D72;
    font-size: 1.8rem;
    margin-bottom: 20px;
    border-bottom: 3px solid #febb3f;
    padding-bottom: 10px;
}

.search-box {
    background: #f0f8ff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #cce5ff;
}

.search-box form {
    display: flex;
    align-items: stretch;
    gap: 12px;
    width: 100%;
}

.search-input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #aaa;
    border-radius: 6px;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    height: 48px;
    box-sizing: border-box;
}

.search-input:focus {
    border-color: #002D72;
    box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.1);
}

.btn-search {
    background: #002D72;
    color: white;
    border: none;
    padding: 0 30px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    white-space: nowrap;
    transition: background 0.2s, transform 0.1s;
    height: 48px;
    min-width: 140px;
    box-sizing: border-box;
}

.btn-search:hover {
    background: #004099;
    transform: translateY(-1px);
}

.btn-search:active {
    transform: translateY(0);
}

.student-info {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-group label {
    font-weight: 600;
    color: #555;
    font-size: 0.9rem;
}

.info-group span {
    color: #002D72;
    font-size: 1rem;
}

.grades-header {
    background: #002D72;
    color: white;
    padding: 12px 20px;
    font-weight: bold;
    margin-top: 20px;
    border-radius: 6px 6px 0 0;
}

.grades-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.grades-table th {
    background: #002D72;
    color: white;
    padding: 12px;
    text-align: center;
    font-weight: 600;
    border: 1px solid #001f52;
}

.grades-table th:first-child {
    text-align: left;
}

.grades-table td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
}

.grades-table td:first-child {
    text-align: left;
}

.grades-table tbody tr:hover {
    background: #f8f9fa;
}

.status-pass {
    color: #28a745;
    font-weight: 600;
}

.status-fail {
    color: #dc3545;
    font-weight: 600;
}

.signature-section {
    margin-top: 60px;
    padding-top: 20px;
}

.report-header-print {
    text-align: center;
    margin-bottom: 30px;
    display: none;
}

.report-header-print h1 {
    margin: 0;
    font-size: 24pt;
    color: #002D72;
}

.report-header-print p {
    margin: 5px 0;
    font-size: 14pt;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .report-header-print {
        display: block !important;
    }

    body {
        background: white;
    }

    .form-card {
        box-shadow: none;
        max-width: 100%;
    }

    .grades-table th {
        background: #e0e0e0 !important;
        color: black !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .grades-header {
        background: #e0e0e0 !important;
        color: black !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

@media (max-width: 768px) {
    .search-box form {
        flex-direction: column;
    }

    .search-input,
    .btn-search {
        width: 100%;
        height: 44px;
    }

    .student-info {
        grid-template-columns: 1fr;
    }

    .grades-table {
        font-size: 0.85rem;
    }

    .grades-table th,
    .grades-table td {
        padding: 8px 6px;
    }
}
</style>

<div class="form-card">
    
    <div class="report-header-print">
        <h1>University of Saint Louis</h1>
        <p>OFFICIAL REPORT CARD</p>
    </div>

    <h2 class="record-title no-print">Student Records</h2>
    
    <div class="search-box no-print">
        <form method="GET" onsubmit="event.preventDefault(); loadZone('student_records.php?' + new URLSearchParams(new FormData(this)).toString() + '&_=' + Date.now());">
            <input type="text" name="student_id" value="<?php echo htmlspecialchars($_GET['student_id'] ?? ''); ?>" class="search-input" placeholder="Enter Student ID (e.g. 20260001)" required>
            <button type="submit" class="btn-search">Search Record</button>
        </form>
    </div>

    <?php if($student): ?>
        
        <div style="text-align:right; margin-bottom:10px;" class="no-print">
            <button onclick="window.print()" class="btn-search" style="background:#6c757d;">🖨️ Print Report</button>
        </div>

        <div class="student-info">
            <div class="info-group">
                <label>Name</label>
                <span><?php echo htmlspecialchars($student['lname'] . ', ' . $student['fname'] . ' ' . $student['mname']); ?></span>
            </div>
            <div class="info-group">
                <label>Student ID / LRN</label>
                <span><?php echo htmlspecialchars($_GET['student_id']); ?> / <?php echo htmlspecialchars($student['lrn']); ?></span>
            </div>
            <div class="info-group">
                <label>Track / Level</label>
                <span><?php echo htmlspecialchars(ucfirst($student['track']) . ' - ' . $student['grade_level']); ?></span>
            </div>
            <div class="info-group">
                <label>Date Enrolled</label>
                <span><?php echo htmlspecialchars($student['date_enrolled']); ?></span>
            </div>
        </div>

        <?php foreach($grades as $sy => $subs): ?>
            <div class="grades-header">School Year: <?php echo $sy; ?></div>
            <table class="grades-table">
                <thead>
                    <tr>
                        <th width="40%">Subject</th>
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
                        <td>
                            <?php if($d['remarks'] == 'PASSED'): ?>
                                <span class="status-pass">PASSED</span>
                            <?php elseif($d['remarks'] == 'FAILED'): ?>
                                <span class="status-fail">FAILED</span>
                            <?php else: ?> - <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
        
        <div class="grades-header" style="background:#444;">Observed Values & Attendance</div>
        <table class="grades-table" style="width:60%;">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Attendance Score</th>
                    <th>Conduct Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php for($i=1; $i<=4; $i++): 
                    $att = $attendance[$i]['attendance_score'] ?? '-';
                    $con = $attendance[$i]['conduct_grade'] ?? '-';
                ?>
                <tr>
                    <td>Quarter <?php echo $i; ?></td>
                    <td><?php echo $att; ?></td>
                    <td><?php echo $con; ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div class="signature-section">
            <div style="float:left; width:40%; border-top:1px solid black; text-align:center; padding-top:5px;">
                <strong>Principal / School Head</strong>
            </div>
            <div style="float:right; width:40%; border-top:1px solid black; text-align:center; padding-top:5px;">
                <strong>School Registrar</strong>
            </div>
            <div style="clear:both;"></div>
        </div>

    <?php elseif(isset($_GET['student_id'])): ?>
        <div style="padding:30px; text-align:center; color:#dc3545; background:#fff0f0; border-radius:8px;">
            <strong>Student not found.</strong> Please check the ID number.
        </div>
    <?php endif; ?>
</div>