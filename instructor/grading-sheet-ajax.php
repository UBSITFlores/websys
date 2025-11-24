<?php
require_once '../functions/instructor_function.php';
session_start();
$account_id = $_SESSION['ACCOUNTID'] ?? $_SESSION['account_id'] ?? null;
if (!$account_id) {
    echo "<div style='color:red;'>Session expired. Please log in again.</div>";
    exit;
}
$instructor = new Instructor();
$sections = $instructor->getSections($account_id);
?>
<div>
    <h2>Grading Sheet</h2>
    <table class="grading-table">
        <thead>
            <tr>
                <th>Section</th>
                <th>Code</th>
                <th>Description</th>
                <th>Semester</th>
                <th>School Year</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($sections): ?>
                <?php foreach ($sections as $section): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($section['section']); ?></td>
                        <td><?php echo htmlspecialchars($section['code']); ?></td>
                        <td><?php echo htmlspecialchars($section['description']); ?></td>
                        <td><?php echo htmlspecialchars($section['semester']); ?></td>
                        <td><?php echo htmlspecialchars($section['school_year']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="not-found">No class sections found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
