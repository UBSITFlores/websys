<?php
require_once '../functions/student_function.php';

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php'); exit();
}

$studentFunc = new Student();
$student_pk = $studentFunc->getStudentId($_SESSION['ACCOUNTID']);

if (!$student_pk) {
    echo "Error: Student not found."; exit();
}

// 1. GET STUDENT PROFILE (To check if they are SHS)
$profile = $studentFunc->getProfile($student_pk);
$track_lower = strtolower($profile['track']);
$is_shs = ($track_lower == 'senior high school' || $track_lower == 'stem' || $track_lower == 'abm' || $track_lower == 'humss');

// 2. GET SUBJECTS
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
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Section</th>
                        <th>Term</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($my_subjects)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px; color:#888;">
                            You are not enrolled in any subjects yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($my_subjects as $sub): 
                        $raw_sem = $sub['semester'];
                        $sy = $sub['school_year'];
                        $pretty_sem = $raw_sem;

                        // --- FIX: VISUAL OVERRIDE FOR SENIOR HIGH ---
                        // If the database says "Whole Year" (or is empty), but the student is SHS,
                        // we FORCE it to display "1st Semester".
                        if ($is_shs && ($raw_sem == 'Whole Year' || empty($raw_sem))) {
                            $pretty_sem = "1st Semester";
                        }
                        
                        // Standard formatting for valid semesters
                        if($raw_sem == '1st' || $raw_sem == '2nd' || $raw_sem == 'Summer') {
                            $pretty_sem = $raw_sem . " Semester";
                        }
                    ?>
                    <tr>
                        <td style="font-weight:bold; color:#002D72;">
                            <?php echo htmlspecialchars($sub['code']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($sub['description']); ?></td>
                        <td><?php echo htmlspecialchars($sub['section']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($pretty_sem); ?>
                            <span style="color:#888; font-size:0.8em;">(<?php echo htmlspecialchars($sy); ?>)</span>
                        </td>
                        <td>
                            <span class="status-enrolled">ENROLLED</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>