<?php
require_once '../functions/instructor_function.php';
session_start();

$account_id = $_SESSION['account_id'] ?? $_SESSION['ACCOUNTID'] ?? null;
if (!$account_id) {
    echo "__SESSION_EXPIRED__";
    exit();
}

$func = new Instructor();

$section = $_GET['section'] ?? null;
$code    = $_GET['code'] ?? null;
if (!$section || !$code) {
    echo "Missing section or code.";
    exit();
}

// Handle AJAX save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grades'])) {
    $ok = $func->saveGrades($section, $code, $_POST['grades']);
    echo json_encode(['ok' => $ok]);
    exit();
}

$students = $func->getStudents($section, $code);
$grades   = $func->getGrades($section, $code);
?>
<div class="grading-panel">
    <h2>Grades for <?=htmlspecialchars($section)?> (<?=htmlspecialchars($code)?>)</h2>

    <!-- Bulk input area -->
    <div id="bulkInputArea" class="grading-form" style="margin-bottom:15px;">
        <label><strong>Bulk Grades:</strong></label>
        <input type="text" id="bulkGrades" placeholder="e.g. 85 90 78 88" disabled>
        <button type="button" id="bulkSubmit" disabled onclick="applyBulk()">Submit</button>
        <span id="bulkTargetLabel" style="margin-left:10px; font-weight:600; color:#002D72;"></span>
    </div>

    <form id="gradesForm">
        <table class="grading-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <?php for ($q=1; $q<=4; $q++): ?>
                        <th>
                            <?= $q ?><?php echo ($q==1?'st':($q==2?'nd':($q==3?'rd':'th'))); ?> Quarter
                            <div>
                                <button type="button" onclick="enableBulk(<?= $q ?>)">Bulk</button>
                                <button type="button" onclick="enableManual(<?= $q ?>)">Manual</button>
                            </div>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($students): ?>
                <?php foreach ($students as $i => $student): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($student['account_id']) ?></td>
                    <?php for ($q=1; $q<=4; $q++): ?>
                        <td>
                          <input type="text"
                                 name="grades[<?= (int)$student['id'] ?>][<?= $q ?>]"
                                 placeholder="0-100"
                                 value="<?= isset($grades[$student['id']][$q]) ? htmlspecialchars($grades[$student['id']][$q]) : '' ?>"
                                 disabled>
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="no-data">No students found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="grading-form" style="margin-top:15px;">
            <button type="button" onclick="saveGrades()">Save Grades</button>
            <a href="grading-sheet.php" class="button" onclick="loadContent('grading-sheet.php'); return false;">Back to Grading Sheets</a>
        </div>
    </form>
</div>

<script>
let bulkQuarter = null;

function enableBulk(q) {
    bulkQuarter = q;
    document.getElementById('bulkGrades').disabled = false;
    document.getElementById('bulkSubmit').disabled = false;
    document.getElementById('bulkTargetLabel').textContent = "Target: Quarter " + q;
    document.getElementById('bulkGrades').focus();
}

function applyBulk() {
    if (!bulkQuarter) {
        alert("No quarter selected for bulk input.");
        return;
    }
    const grades = document.getElementById('bulkGrades').value.trim().split(/\s+/);
    if (grades.length === 0 || grades[0] === "") {
        alert("Please enter grades in the bulk box first.");
        return;
    }
    const inputs = document.querySelectorAll(`input[name^="grades"][name$="[${bulkQuarter}]"]`);
    inputs.forEach((input, i) => {
        if (grades[i]) {
            input.value = grades[i];
            input.disabled = false; // unlock so you can edit further
        }
    });
    alert("Bulk grades applied to Quarter " + bulkQuarter);
    // reset
    document.getElementById('bulkGrades').value = "";
    document.getElementById('bulkGrades').disabled = true;
    document.getElementById('bulkSubmit').disabled = true;
    document.getElementById('bulkTargetLabel').textContent = "";
    bulkQuarter = null;
}

function enableManual(q) {
    const inputs = document.querySelectorAll(`input[name^="grades"][name$="[${q}]"]`);
    if (inputs.length === 0) {
        alert("No inputs found for quarter " + q);
        return;
    }
    inputs.forEach(input => {
        input.disabled = false;   // unlock
        input.classList.add('manual-enabled');
    });
    const th = document.querySelector(`th:nth-child(${q+2})`);
    if (th) th.classList.add('active-quarter');
    alert("Manual mode enabled for Quarter " + q + ". You can now type grades.");
}

function saveGrades() {
    const form = document.getElementById('gradesForm');
    const formData = new FormData(form);

    fetch('section-grades.php?section=<?=urlencode($section)?>&code=<?=urlencode($code)?>', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error("Network response was not ok");
        return res.json();
    })
    .then(json => {
        if (json.ok) {
            alert('Grades saved successfully.');
        } else {
            alert('Failed to save grades.');
        }
    })
    .catch(err => {
        console.error("AJAX error:", err);
        alert('Error saving grades. Check console for details.');
    });
}
</script>
