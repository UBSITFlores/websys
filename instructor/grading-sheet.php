<?php
include '../functions/instructor_function.php';
session_start();

// Accept either 'account_id' or 'ACCOUNTID' from session
$instructorId = $_SESSION['account_id'] ?? $_SESSION['ACCOUNTID'] ?? null;

if (!$instructorId) {
    echo "__SESSION_EXPIRED__";
    exit();
}
$func = new Instructor();

$semester   = $_GET['semester'] ?? '';
$schoolYear = $_GET['school_year'] ?? '';

$semesters   = $func->getSemesters();
$schoolYears = $func->getSchoolYears();

if (!$semester && !empty($semesters)) {
    $semester = $semesters[0];
}
if (!$schoolYear && !empty($schoolYears)) {
    $schoolYear = $schoolYears[0];
}

$sections = $func->getSections($instructorId, $semester, $schoolYear);
?>

<div class="grading-panel">
    <h2>Grading Sheets</h2>

    <form method="get" class="grading-form">
        <label for="semester">Semester:</label>
        <select name="semester" id="semester">
            <?php foreach ($semesters as $sem): ?>
                <option value="<?=htmlspecialchars($sem)?>" <?=($sem == $semester) ? 'selected' : ''?>>
                    <?=htmlspecialchars($sem)?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="school_year">School Year:</label>
        <select name="school_year" id="school_year">
            <?php foreach ($schoolYears as $sy): ?>
                <option value="<?=htmlspecialchars($sy)?>" <?=($sy == $schoolYear) ? 'selected' : ''?>>
                    <?=htmlspecialchars($sy)?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="button">Search</button>
        <a href="grading-sheet.php" class="button">Clear</a>
    </form>

    <div class="grading-meta">
        Semester: <?=htmlspecialchars($semester)?> |
        School Year: <?=htmlspecialchars($schoolYear)?> |
        Data Retrieved: <?=count($sections)?>
    </div>

    <table class="grading-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Section</th>
                <th>Code</th>
                <th>Description / School</th>
                <th>Last Transaction</th>
                <th>Finalized</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($sections): ?>
            <?php foreach ($sections as $i => $section): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td class="section-code"><?= htmlspecialchars($section['section']) ?></td>
                <td><?= htmlspecialchars($section['code']) ?></td>
                <td>
                    <?= htmlspecialchars($section['description']) ?><br>
                    <span class="school-label">School of Information Technology</span>
                </td>
                <td><?= $section['last_transaction'] ? date("M-d-Y", strtotime($section['last_transaction'])) : '' ?></td>
                <td><?= $section['finalized'] ? '<span class="finalized-yes">YES</span>' : '<span class="finalized-no">NO</span>' ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="no-data">No data found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
