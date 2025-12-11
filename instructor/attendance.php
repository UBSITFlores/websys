<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    die("Access Denied");
}

require_once '../functions/db.php';

// ---------------------------------------------------------
// 1. RESOLVE INSTRUCTOR ID (THE FIX)
// ---------------------------------------------------------
$sess_id = $_SESSION['ACCOUNTID'] ?? $_SESSION['ACCOUNT_ID'] ?? '';
$instructor_id = 0;

// FIX: Removed 'username' column check to prevent crash
// We only check 'id' (Primary Key) or 'account_id' (String ID)
$stmt = $pdo->prepare("SELECT id FROM account WHERE id = ? OR account_id = ? LIMIT 1");
$stmt->execute([$sess_id, $sess_id]);
$instructor_id = $stmt->fetchColumn();

if (!$instructor_id) {
    // Fallback: If session is empty or invalid, try to proceed if we have a section parameter
    // This allows the page to load "No classes" message instead of crashing
    $instructor_id = 0; 
}

// ---------------------------------------------------------
// HANDLE SAVE REQUEST (AJAX POST)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $section_id = $_POST['section_id'];
        $month = $_POST['month'];
        $att_data = $_POST['att'] ?? []; 

        $pdo->beginTransaction();

        foreach ($att_data as $sid => $days) {
            $check = $pdo->prepare("SELECT id FROM attendance_daily WHERE student_id=? AND section_id=? AND month_year=?");
            $check->execute([$sid, $section_id, $month]);
            $exists = $check->fetchColumn();

            $set_parts = [];
            $params = [];
            
            foreach ($days as $day_num => $status) {
                if ($day_num >= 1 && $day_num <= 31) {
                    $set_parts[] = "day_{$day_num} = ?";
                    $params[] = $status; 
                }
            }

            if (empty($set_parts)) continue;

            if ($exists) {
                $sql = "UPDATE attendance_daily SET " . implode(', ', $set_parts) . " WHERE id = ?";
                $params[] = $exists;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                $cols = "student_id, section_id, month_year, " . implode(', ', array_map(function($d){ return "day_$d"; }, array_keys($days)));
                $vals = "?, ?, ?, " . implode(', ', array_fill(0, count($days), '?'));
                $insert_params = array_merge([$sid, $section_id, $month], array_values($days));
                $stmt = $pdo->prepare("INSERT INTO attendance_daily ($cols) VALUES ($vals)");
                $stmt->execute($insert_params);
            }
        }

        $pdo->commit();
        echo "SAVED"; 
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
    exit; 
}

// ---------------------------------------------------------
// DISPLAY VIEW (GET)
// ---------------------------------------------------------

// 2. GET URL PARAMETERS
$section_name = $_GET['section'] ?? '';
$subject_code = $_GET['code'] ?? '';
$month = $_GET['month'] ?? date('Y-m');

// 3. FETCH DATA
$students = [];
$att_data = [];
$subject_info = null;
$my_classes = [];

// A. Always fetch the list of classes for this instructor (for the dropdown)
if ($instructor_id) {
    $stmt = $pdo->prepare("SELECT * FROM sections WHERE instructor_id = ? ORDER BY school_year DESC, section ASC");
    $stmt->execute([$instructor_id]);
    $my_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// B. If a specific class is selected (via URL from Dashboard), fetch its data
if ($section_name && $subject_code && $instructor_id) {
    // Verify Ownership & Get Section ID
    $stmt = $pdo->prepare("SELECT * FROM sections WHERE section = ? AND code = ? AND instructor_id = ?");
    $stmt->execute([$section_name, $subject_code, $instructor_id]);
    $subject_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subject_info) {
        $sec_id = $subject_info['id'];

        // Get Students
        $stmt = $pdo->prepare("SELECT a.id, a.lname, a.fname 
                               FROM enrollments e 
                               JOIN account a ON e.student_id = a.id 
                               WHERE e.section_id = ? 
                               ORDER BY a.lname ASC");
        $stmt->execute([$sec_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get Saved Attendance
        $stmt = $pdo->prepare("SELECT * FROM attendance_daily WHERE section_id = ? AND month_year = ?");
        $stmt->execute([$sec_id, $month]);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $att_data[$row['student_id']] = $row;
        }
    }
}

// Helper: Get days in selected month
$days_in_month = date('t', strtotime($month . '-01'));
?>

<style>
    /* --- Layout --- */
    .att-container { max-width: 100%; overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    
    /* --- Controls --- */
    .att-controls { display: flex; gap: 15px; margin-bottom: 20px; align-items: end; flex-wrap: wrap; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; }
    .att-controls label { font-weight: bold; font-size: 0.9rem; display: block; margin-bottom: 5px; color: #002D72; }
    .att-controls select, .att-controls input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; min-width: 200px; }
    
    /* --- Table --- */
    .att-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; min-width: 1200px; margin-top: 15px; }
    .att-table th { background: #002D72; color: white; padding: 8px; text-align: center; border: 1px solid #001f52; white-space: nowrap; }
    .att-table td { border: 1px solid #ddd; padding: 0; text-align: center; height: 30px; }
    
    /* Sticky Name Column */
    .col-name { position: sticky; left: 0; background: #fff; z-index: 2; width: 200px; text-align: left !important; padding-left: 10px !important; border-right: 2px solid #ccc !important; }
    .att-table th.col-name { background: #002D72; color: white; z-index: 3; }

    /* --- Inputs --- */
    .att-input { 
        width: 100%; height: 100%; border: none; text-align: center; 
        text-transform: uppercase; font-weight: bold; cursor: pointer;
    }
    .att-input:focus { background: #e8f0fe; outline: none; }
    
    /* --- Buttons --- */
    .btn-action { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; color: white; }
    .btn-save { background: #198754; }
    .btn-print { background: #002D72; }
    .btn-save:hover { background: #157347; }
    .btn-print:hover { background: #001f52; }
    
    #save_msg { margin-left: 10px; font-weight: bold; }
</style>

<div class="att-container">
    <h2 style="color:#002D72; margin-top:0; border-bottom: 2px solid #febb3f; padding-bottom: 10px;">Class Attendance</h2>
    
    <form id="controlForm" class="att-controls">
        
        <div>
            <label>Select Class:</label>
            <select onchange="loadZone('attendance.php?section=' + encodeURIComponent(this.options[this.selectedIndex].getAttribute('data-sec')) + '&code=' + encodeURIComponent(this.options[this.selectedIndex].getAttribute('data-code')) + '&month=' + document.getElementById('month_picker').value)">
                <option value="">-- Choose a Subject --</option>
                <?php foreach($my_classes as $cls): 
                    $isSelected = ($cls['section'] == $section_name && $cls['code'] == $subject_code) ? 'selected' : '';
                    $label = $cls['code'] . " - " . $cls['section'];
                ?>
                    <option value="<?php echo $cls['id']; ?>" 
                            data-sec="<?php echo htmlspecialchars($cls['section']); ?>" 
                            data-code="<?php echo htmlspecialchars($cls['code']); ?>"
                            <?php echo $isSelected; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="hidden" id="att_sec_name" value="<?php echo htmlspecialchars($section_name); ?>">
        <input type="hidden" id="att_sub_code" value="<?php echo htmlspecialchars($subject_code); ?>">

        <div>
            <label>Month:</label>
            <input type="month" id="month_picker" name="month" value="<?php echo $month; ?>" onchange="changeMonth()">
        </div>

        <div style="flex:1;"></div>

        <?php if($subject_info): ?>
            <span id="save_msg"></span>
            <button type="button" onclick="saveAttendance()" class="btn-action btn-save">💾 Save Changes</button>
            <button type="button" onclick="printAttendancePDF()" class="btn-action btn-print">🖨️ Print Record</button>
        <?php endif; ?>
    </form>

    <?php if(!$subject_info): ?>
        <div style="text-align:center; padding:40px; color:#666;">
            <h3>Please select a class from the dropdown above.</h3>
            <?php if(empty($my_classes)): ?>
                <p style="color:red;">No classes found for this instructor account. Please contact admin.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        
        <form id="attForm">
            <input type="hidden" name="section_id" value="<?php echo $sec_id; ?>">
            <input type="hidden" name="month" value="<?php echo $month; ?>">

            <table class="att-table">
                <thead>
                    <tr>
                        <th class="col-name">Student Name</th>
                        <?php for($d=1; $d<=$days_in_month; $d++): ?>
                            <th style="width: 25px;"><?php echo $d; ?></th>
                        <?php endfor; ?>
                        <th style="background:#444;">P</th>
                        <th style="background:#444;">A</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($students)): ?>
                        <tr><td colspan="<?php echo $days_in_month + 3; ?>" style="padding:20px;">No students enrolled in this section.</td></tr>
                    <?php else: ?>
                        <?php foreach($students as $s): 
                            $sid = $s['id'];
                            $row_data = $att_data[$sid] ?? [];
                            
                            $p_total = 0; $a_total = 0;
                            for($d=1; $d<=$days_in_month; $d++) {
                                $val = $row_data['day_'.$d] ?? '';
                                if($val == 'P') $p_total++;
                                if($val == 'A') $a_total++;
                            }
                        ?>
                        <tr>
                            <td class="col-name">
                                <strong><?php echo htmlspecialchars($s['lname'] . ', ' . $s['fname']); ?></strong>
                            </td>
                            
                            <?php for($d=1; $d<=$days_in_month; $d++): 
                                $val = $row_data['day_'.$d] ?? '';
                                $style = '';
                                if($val === 'P') $style = 'color:green; background-color:#e6ffe6;';
                                elseif($val === 'A') $style = 'color:red; background-color:#ffe6e6;';
                                elseif($val === 'L') $style = 'color:#856404; background-color:#fff3cd;';
                            ?>
                                <td>
                                    <input type="text" 
                                           name="att[<?php echo $sid; ?>][<?php echo $d; ?>]" 
                                           value="<?php echo $val; ?>" 
                                           class="att-input" 
                                           style="<?php echo $style; ?>"
                                           maxlength="1"
                                           onfocus="this.select()"
                                           oninput="updateCounts(this)" 
                                           autocomplete="off">
                                </td>
                            <?php endfor; ?>
                            
                            <td class="count-p" style="font-weight:bold; color:green; background:#f9f9f9;"><?php echo $p_total; ?></td>
                            <td class="count-a" style="font-weight:bold; color:red; background:#f9f9f9;"><?php echo $a_total; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    <?php endif; ?>
</div>