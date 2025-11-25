<?php
require_once '../functions/student_function.php';

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

$studentFunc = new Student();
$student_pk = $studentFunc->getStudentId($_SESSION['ACCOUNTID']);

if (!$student_pk) {
    echo "Error: Student not found.";
    exit();
}

$my_subjects = $studentFunc->getEnrollment($student_pk);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Enrollment</title>
    <link rel="stylesheet" href="enrollment.css">
</head>
<body>
    <div class="enrollment-container">
        <div class="enrollment-card">
            <h2 class="enrollment-title">Enrolled Subjects</h2>
            
            <table class="enrollment-table">
                <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Section</th>
                    <th>Term</th>
                    <th>Status</th>
                </tr>

                <?php if(empty($my_subjects)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px; color:#888;">
                            You are not enrolled in any subjects yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($my_subjects as $sub): ?>
                    <tr>
                        <td style="font-weight:bold; color:#002D72;">
                            <?php echo htmlspecialchars($sub['code']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($sub['description']); ?></td>
                        <td><?php echo htmlspecialchars($sub['section']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($sub['semester']); ?>
                            <span style="color:#888; font-size:0.8em;">(<?php echo htmlspecialchars($sub['school_year']); ?>)</span>
                        </td>
                        <td>
                            <span class="status-enrolled">Enrolled</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>