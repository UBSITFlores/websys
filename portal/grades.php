<?php
require_once '../functions/student_function.php';

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

$studentFunc = new Student();
$student_pk = $studentFunc->getStudentId($_SESSION['ACCOUNTID']);

if (!$student_pk) {
    echo "<div style='padding:20px; color:red;'>Error: Student account record not found.</div>";
    exit();
}

$rows = $studentFunc->getGrades($student_pk);
$my_grades = [];

foreach($rows as $r){
    $code = $r['code'];
    if(!isset($my_grades[$code])){
        $my_grades[$code] = [
            'name'   => $r['description'],
            'q1'     => '-', 'q2' => '-', 'q3' => '-', 'q4' => '-',
            'avg'    => '-', 'status' => '-'
        ];
    }
    if($r['quarter'] == 1) $my_grades[$code]['q1'] = $r['grade'];
    if($r['quarter'] == 2) $my_grades[$code]['q2'] = $r['grade'];
    if($r['quarter'] == 3) $my_grades[$code]['q3'] = $r['grade'];
    if($r['quarter'] == 4) $my_grades[$code]['q4'] = $r['grade'];
}

foreach($my_grades as $code => $data) {
    if(is_numeric($data['q1']) && is_numeric($data['q2']) && is_numeric($data['q3']) && is_numeric($data['q4'])) {
        $average = ($data['q1'] + $data['q2'] + $data['q3'] + $data['q4']) / 4;
        $my_grades[$code]['avg'] = round($average, 2);
        if($average >= 75) {
            $my_grades[$code]['status'] = 'PASSED';
        } else {
            $my_grades[$code]['status'] = 'FAILED';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades Records</title>
    <link rel="stylesheet" href="grades.css">
</head>
<body>

    <div class="grades-card">
        <h2 class="grades-title">My Grades</h2>
        
        <table class="grades-table">
            <tr>
                <th>Subject / Code</th>
                <th>1st</th>
                <th>2nd</th>
                <th>3rd</th>
                <th>4th</th>
                <th style="background:#001f52;">Final</th>
                <th style="background:#001f52;">Status</th>
            </tr>

            <?php if(empty($my_grades)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:20px; color:#888;">
                        No grades records found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($my_grades as $code => $data): ?>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($data['name']); ?><br>
                        <small style="color:#888;"><?php echo htmlspecialchars($code); ?></small>
                    </td>
                    <td><?php echo $data['q1']; ?></td>
                    <td><?php echo $data['q2']; ?></td>
                    <td><?php echo $data['q3']; ?></td>
                    <td><?php echo $data['q4']; ?></td>
                    <td style="font-weight:bold;"><?php echo $data['avg']; ?></td>
                    <td>
                        <?php if($data['status'] == 'PASSED'): ?>
                            <span class="badge-pass">PASSED</span>
                        <?php elseif($data['status'] == 'FAILED'): ?>
                            <span class="badge-fail">FAILED</span>
                        <?php else: ?>
                            <span style="color:#ccc;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>