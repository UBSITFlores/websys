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

$my_schedule = $studentFunc->getSchedule($student_pk);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule</title>
    <link rel="stylesheet" href="schedule.css">
</head>
<body>
    <div class="schedule-container">
        <div class="schedule-card">
            <h2 class="schedule-title">Class Schedule</h2>
            <table class="schedule-table">
                <tr>
                    <th>Time</th>
                    <th>Course</th>
                    <th>Instructor</th>
                    <th>Room</th>
                </tr>

                <?php if(empty($my_schedule)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:20px; color:#888;">
                            No classes enrolled yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($my_schedule as $class): ?>
                    <tr>
                        <td style="font-weight:bold; color:#002D72;">
                            <?php echo htmlspecialchars($class['schedule_time']); ?>
                        </td>
                        <td>
                            <span class="code-badge"><?php echo htmlspecialchars($class['code']); ?></span>
                            <?php echo htmlspecialchars($class['course']); ?>
                        </td>
                        <td>
                            <?php 
                                if($class['instructor_fname']) {
                                    echo htmlspecialchars($class['instructor_fname'] . ' ' . $class['instructor_lname']); 
                                } else {
                                    echo "<span style='color:#999; font-style:italic;'>TBA</span>";
                                }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($class['room']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>