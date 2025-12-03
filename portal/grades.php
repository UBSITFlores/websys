<?php
require_once '../functions/student_function.php';

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php'); exit();
}

$studentFunc = new Student();
$student_pk = $studentFunc->getStudentId($_SESSION['ACCOUNTID']);

// 1. GET HISTORY FOR DROPDOWN
$history = $studentFunc->getEnrollmentHistory($student_pk);

// Default to the latest entry if nothing selected
$selected_sy = $_GET['sy'] ?? ($history[0]['school_year'] ?? '');
$selected_sem = $_GET['sem'] ?? ($history[0]['semester'] ?? '');

// 2. FETCH GRADES
$rows = $studentFunc->getStudentGrades($student_pk, $selected_sy, $selected_sem);

// 3. PROCESS DATA & DETECT TRACK TYPE
$processed_grades = [];
$is_shs_view = false; // Default to Standard (Q1-Q4)

foreach($rows as $r) {
    $code = $r['code'];
    
    // Check if ANY subject in this list is Senior High. If so, switch view mode.
    $t = strtolower($r['track']);
    if($t == 'senior high school' || $t == 'stem' || $t == 'abm' || $t == 'humss') {
        $is_shs_view = true;
    }

    if(!isset($processed_grades[$code])){
        $processed_grades[$code] = [
            'desc' => $r['description'],
            'section' => $r['section'],
            'grades' => [] 
        ];
    }
    // Map database quarter (1,2,3,4) to array
    if(isset($r['quarter']) && $r['grade'] !== null) {
        $processed_grades[$code]['grades'][$r['quarter']] = $r['grade'];
    }
}
?>

<style>
    .grades-container { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 1000px; margin: 0 auto; }
    .grades-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
    .g-title { color: #002D72; margin: 0; font-size: 1.5rem; }
    
    .term-select { padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; color: #333; min-width: 300px; }
    
    .g-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .g-table th { background: #002D72; color: white; padding: 12px; text-align: center; font-weight: 600; font-size: 0.9rem; }
    .g-table td { border-bottom: 1px solid #eee; padding: 12px; text-align: center; color: #444; }
    .g-table th:first-child, .g-table td:first-child { text-align: left; }
    
    .status-pass { color: #198754; font-weight: bold; background: #d1e7dd; padding: 2px 8px; border-radius: 4px; }
    .status-fail { color: #dc3545; font-weight: bold; background: #f8d7da; padding: 2px 8px; border-radius: 4px; }
</style>

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h2 class="g-title">Academic Records</h2>
            <small style="color:#666;">View your grades by School Year & Term</small>
        </div>
        
        <form method="GET" action="index.php">
            <input type="hidden" name="page" value="grades">
            <select name="term_selector" class="term-select" onchange="const [sy, sem] = this.value.split('|'); window.location.href='index.php?page=grades&sy='+sy+'&sem='+sem;">
                <?php foreach($history as $h): 
                    $val = $h['school_year'] . '|' . $h['semester'];
                    
                    // --- SMART LABEL GENERATION (UPDATED) ---
                    $raw_sem = $h['semester'];
                    $lvl = $h['year_level'];
                    
                    // Logic: Is this Senior High? (Grade 11 or 12)
                    $is_shs_level = (strpos($lvl, '11') !== false || strpos($lvl, '12') !== false);

                    $pretty_sem = "Whole Year"; // Default fallback
                    
                    // If SHS and it says "Whole Year" (or empty), force it to look like "1st Semester"
                    if ($is_shs_level && ($raw_sem == 'Whole Year' || $raw_sem == '')) {
                        $pretty_sem = "1st Semester";
                    } 
                    // Normal Semester Handling
                    elseif ($raw_sem == '1st' || $raw_sem == '2nd' || $raw_sem == 'Summer') {
                        $pretty_sem = $raw_sem . " Semester";
                    } 
                    // Catch-all for other text
                    elseif (!empty($raw_sem)) {
                        $pretty_sem = $raw_sem;
                    }

                    $label = "SY " . $h['school_year'] . " - " . $pretty_sem . " (" . $lvl . ")";
                    
                    $is_sel = ($h['school_year'] == $selected_sy && $h['semester'] == $selected_sem) ? 'selected' : '';
                ?>
                    <option value="<?php echo $val; ?>" <?php echo $is_sel; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
                <?php if(empty($history)): ?><option>No records found</option><?php endif; ?>
            </select>
        </form>
    </div>

    <table class="g-table">
        <thead>
            <tr>
                <th style="width: 35%;">Subject</th>
                <?php if($is_shs_view): ?>
                    <th>Prelim</th><th>Midterm</th><th>Finals</th>
                <?php else: ?>
                    <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
                <?php endif; ?>
                <th>Final Grade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($processed_grades)): ?>
                <tr><td colspan="7" style="text-align:center; padding:30px;">No grades uploaded for this term yet.</td></tr>
            <?php else: ?>
                <?php foreach($processed_grades as $code => $data): 
                    $g = $data['grades'];
                    $total = 0; $count = 0;
                    
                    if($is_shs_view) {
                        // SHS Logic: 1=Prelim, 2=Midterm, 3=Finals
                        $c1 = $g[1] ?? '-'; $c2 = $g[2] ?? '-'; $c3 = $g[3] ?? '-';
                        if(is_numeric($c1)) { $total += $c1; $count++; }
                        if(is_numeric($c2)) { $total += $c2; $count++; }
                        if(is_numeric($c3)) { $total += $c3; $count++; }
                    } else {
                        // Regular Logic: Q1-Q4
                        $c1 = $g[1] ?? '-'; $c2 = $g[2] ?? '-'; $c3 = $g[3] ?? '-'; $c4 = $g[4] ?? '-';
                        if(is_numeric($c1)) { $total += $c1; $count++; }
                        if(is_numeric($c2)) { $total += $c2; $count++; }
                        if(is_numeric($c3)) { $total += $c3; $count++; }
                        if(is_numeric($c4)) { $total += $c4; $count++; }
                    }

                    // Calculate Final
                    $final_avg = ($count > 0) ? number_format($total/$count, 2) : '-';
                    $status = '-';
                    if($final_avg !== '-') {
                        $status = ($final_avg >= 75) ? '<span class="status-pass">PASSED</span>' : '<span class="status-fail">FAILED</span>';
                    }
                ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($code); ?></strong><br>
                        <small style="color:#666;"><?php echo htmlspecialchars($data['desc']); ?></small>
                    </td>
                    
                    <?php if($is_shs_view): ?>
                        <td><?php echo $c1; ?></td>
                        <td><?php echo $c2; ?></td>
                        <td><?php echo $c3; ?></td>
                    <?php else: ?>
                        <td><?php echo $c1; ?></td>
                        <td><?php echo $c2; ?></td>
                        <td><?php echo $c3; ?></td>
                        <td><?php echo $c4; ?></td>
                    <?php endif; ?>

                    <td style="font-weight:bold; color:#002D72;"><?php echo $final_avg; ?></td>
                    <td><?php echo $status; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>