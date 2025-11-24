<?php
require_once '../functions/instructor_function.php';

session_start();

// Demo instructor ID - replace with session in production
$instructor_id = 1;

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    die('Class ID is required.');
}

$instructor = new Instructor();
$classInfo = $instructor->getClassInfo($class_id);
if (!$classInfo) {
    die('Class not found.');
}

$isSeniorHigh = ($classInfo['grade_level'] >= 11 && $classInfo['grade_level'] <= 12);

// Fetch students enrolled (student_number and full_name from students table)
$students = $instructor->getStudentsByClass($class_id);

// Grading periods
$quarters = ['Quarter 1', 'Quarter 2', 'Quarter 3', 'Quarter 4'];
$seniorPeriods = ['Prelim', 'Midterm', 'Finals'];

// Default grading period if not selected (both for HS and SHS now)
if ($isSeniorHigh) {
    $grading_period = $_POST['grading_period'] ?? $_GET['grading_period'] ?? 'Prelim';
} else {
    $grading_period = $_POST['grading_period'] ?? $_GET['grading_period'] ?? 'Quarter 1';
}

// Helper for display in rows
function getGradeValue($allGrades, $period, $enroll_id) {
    return isset($allGrades[$period][$enroll_id]) ? $allGrades[$period][$enroll_id] : '';
}

// Handle form submission for manual grades (single period at a time)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gradesToSave = $_POST['grades'][$grading_period] ?? [];
    if ($instructor->saveGrades($gradesToSave, $grading_period)) {
        $message = "Grades saved successfully for $grading_period.";
    } else {
        $message = "Failed to save grades for $grading_period.";
    }
}

// Fetch all grades for all periods
$allGrades = [];
if ($isSeniorHigh) {
    foreach ($seniorPeriods as $period) {
        $grades = $instructor->getGradesByClassAndPeriod($class_id, $period);
        foreach ($grades as $g) {
            $allGrades[$period][$g['enrollment_id']] = $g['grade'];
        }
    }
} else {
    foreach ($quarters as $period) {
        $grades = $instructor->getGradesByClassAndPeriod($class_id, $period);
        foreach ($grades as $g) {
            $allGrades[$period][$g['enrollment_id']] = $g['grade'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Section Grades - AMS Instructor Portal</title>
<link rel="stylesheet" href="index.css" />
<style>
    .message { padding: 10px; background-color: #e0ffe0; margin-bottom: 15px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    input[type=number] { width: 60px; }
</style>
</head>
<body>
<div class="header">
    <div class="logo">AMS Instructor Portal</div>
    <form action="../logout.php" method="post" style="margin:0;">
        <button class="logout-button" type="submit">Logout</button>
    </form>
</div>
<div class="container">
    <div class="sidebar">
        <button onclick="location.href='grading-sheet.php'">Back to Grading Sheets</button>
    </div>
    <div class="main">
        <h2>Grades for <?=htmlspecialchars($classInfo['subject_name'])?> — <?=htmlspecialchars($classInfo['section_name'])?> (<?= $isSeniorHigh ? "Senior High" : "High School" ?>)</h2>
        <?php if ($message): ?>
            <div class="message"><?=htmlspecialchars($message)?></div>
        <?php endif; ?>

        <form method="post">
        <label for="grading_period">Grading Period:</label>
        <select name="grading_period" id="grading_period" onchange="this.form.submit()">
            <?php
            $periods = $isSeniorHigh ? $seniorPeriods : $quarters;
            foreach ($periods as $period):
                $sel = ($grading_period === $period) ? 'selected' : '';
                echo "<option value=\"$period\" $sel>$period</option>";
            endforeach; ?>
        </select>

        <?php if ($isSeniorHigh): ?>
            <h3>Manual Grade Entry (Senior High)</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Number</th>
                        <th>Full Name</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Finals</th>
                        <th>Average</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i=1; foreach ($students as $student): 
                    $enroll_id = $student['enrollment_id'];
                    $prelim = getGradeValue($allGrades, 'Prelim', $enroll_id);
                    $midterm = getGradeValue($allGrades, 'Midterm', $enroll_id);
                    $finals = getGradeValue($allGrades, 'Finals', $enroll_id);
                    $vals = array_filter([$prelim, $midterm, $finals], function($v){return $v !== '' && $v !== null;});
                    $avg = count($vals) ? array_sum($vals)/count($vals) : 0;
                ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><?=htmlspecialchars($student['student_number'])?></td>
                    <td>
                        <?php 
                            if (!empty($student['full_name'])) {
                                echo htmlspecialchars($student['full_name']);
                            } else {
                                echo htmlspecialchars(trim("{$student['fname']} {$student['mname']} {$student['lname']}"));
                            }
                        ?>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" max="100"
                               name="grades[Prelim][<?= $enroll_id ?>]"
                               id="Prelim_<?= $i ?>"
                               value="<?= htmlspecialchars($prelim) ?>"
                               <?= $grading_period === 'Prelim' ? '' : 'disabled' ?>
                               oninput="updateSHAverage(<?= $i ?>)">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" max="100"
                               name="grades[Midterm][<?= $enroll_id ?>]"
                               id="Midterm_<?= $i ?>"
                               value="<?= htmlspecialchars($midterm) ?>"
                               <?= $grading_period === 'Midterm' ? '' : 'disabled' ?>
                               oninput="updateSHAverage(<?= $i ?>)">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" max="100"
                               name="grades[Finals][<?= $enroll_id ?>]"
                               id="Finals_<?= $i ?>"
                               value="<?= htmlspecialchars($finals) ?>"
                               <?= $grading_period === 'Finals' ? '' : 'disabled' ?>
                               oninput="updateSHAverage(<?= $i ?>)">
                    </td>
                    <td id="sh_avg_<?= $i ?>"><?= number_format($avg, 2) ?></td>
                </tr>
                <?php $i++; endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="manual_grades">Save Manual Grades</button>
            <script>
                function updateSHAverage(rowId) {
                    let p1 = parseFloat(document.getElementById(`Prelim_${rowId}`).value) || 0;
                    let p2 = parseFloat(document.getElementById(`Midterm_${rowId}`).value) || 0;
                    let p3 = parseFloat(document.getElementById(`Finals_${rowId}`).value) || 0;
                    let avg = (p1 + p2 + p3) / 3;
                    document.getElementById(`sh_avg_${rowId}`).textContent = avg.toFixed(2);
                }
                window.onload = function() {
                    <?php foreach ($students as $idx => $student):
                        $rowNum = $idx + 1; ?>
                        updateSHAverage(<?= $rowNum ?>);
                    <?php endforeach; ?>
                };
            </script>
        <?php else: ?>
            <h3>Manual Grade Entry (High School)</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Number</th>
                        <th>Full Name</th>
                        <th>Quarter 1</th>
                        <th>Quarter 2</th>
                        <th>Quarter 3</th>
                        <th>Quarter 4</th>
                        <th>Average</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i=1; foreach ($students as $student):
                    $enroll_id = $student['enrollment_id'];
                    $grades = [];
                    foreach ($quarters as $q) $grades[] = getGradeValue($allGrades, $q, $enroll_id);
                    $vals = array_filter($grades, function($v){return $v !== '' && $v !== null;});
                    $avg = count($vals) ? array_sum($vals)/count($vals) : 0;
                ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><?=htmlspecialchars($student['student_number'])?></td>
                    <td>
                        <?php 
                            if (!empty($student['full_name'])) {
                                echo htmlspecialchars($student['full_name']);
                            } else {
                                echo htmlspecialchars(trim("{$student['fname']} {$student['mname']} {$student['lname']}"));
                            }
                        ?>
                    </td>
                    <?php foreach ($quarters as $idx => $q): ?>
                    <td>
                        <input type="number" step="0.01" min="0" max="100"
                               id="Q<?=($idx+1)?>_<?= $i ?>"
                               name="grades[<?= $q ?>][<?= $enroll_id ?>]"
                               value="<?= htmlspecialchars(getGradeValue($allGrades, $q, $enroll_id)) ?>"
                               <?= $grading_period === $q ? '' : 'disabled' ?>
                               oninput="updateAverage(<?= $i ?>)">
                    </td>
                    <?php endforeach; ?>
                    <td id="avg_<?= $i ?>"><?= number_format($avg, 2) ?></td>
                </tr>
                <?php $i++; endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="manual_grades">Save Manual Grades</button>
            <script>
                function updateAverage(rowId) {
                    let q1 = parseFloat(document.getElementById(`Q1_${rowId}`).value) || 0;
                    let q2 = parseFloat(document.getElementById(`Q2_${rowId}`).value) || 0;
                    let q3 = parseFloat(document.getElementById(`Q3_${rowId}`).value) || 0;
                    let q4 = parseFloat(document.getElementById(`Q4_${rowId}`).value) || 0;
                    let avg = (q1 + q2 + q3 + q4) / 4;
                    document.getElementById(`avg_${rowId}`).textContent = avg.toFixed(2);
                }
                window.onload = function() {
                    <?php foreach ($students as $idx => $student):
                        $rowNum = $idx + 1; ?>
                        updateAverage(<?= $rowNum ?>);
                    <?php endforeach; ?>
                };
            </script>
        <?php endif; ?>
        </form>
    </div>
</div>
</body>
</html>
