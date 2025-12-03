<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Access Denied."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ==========================================
// HANDLE PROMOTION / RE-ENROLLMENT LOGIC
// ==========================================
if (isset($_POST['promote_id'])) {
    $pid = $_POST['promote_id'];        // Student Account ID
    $next_level = $_POST['next_level']; // Target Grade
    $is_repeater = $_POST['is_repeater'] ?? 0; // New Flag
    
    try {
        $pdo->beginTransaction();

        if($next_level == 'Graduated') {
            // --- GRADUATION LOGIC ---
            $upd = $pdo->prepare("UPDATE account SET status = 'Graduated', last_active_date = CURDATE() WHERE id = ?");
            $upd->execute([$pid]);
            $msg = "Student Graduated.";
        } else {
            // --- PROMOTION / REPEAT LOGIC ---

            // 1. GET CURRENT STATUS
            $stmt = $pdo->prepare("SELECT s.section, s.track, s.semester 
                                   FROM enrollments e 
                                   JOIN sections s ON e.section_id = s.id 
                                   WHERE e.student_id = ? 
                                   ORDER BY e.date_enrolled DESC LIMIT 1");
            $stmt->execute([$pid]);
            $prev_sec = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. SAFETY CHECK: JHS to SHS Transition
            if ($next_level == 'Grade 11' && $prev_sec['track'] == 'junior high school') {
                $upd = $pdo->prepare("UPDATE students SET grade_level = ? WHERE student_id = ?");
                $upd->execute([$next_level, $pid]);
                $pdo->commit(); 
                echo "SUCCESS|Promoted to Grade 11. ⚠️ ACTION REQUIRED: Please go to 'Update Student Info' and select their SHS Track (STEM/ABM/etc) to enroll subjects.";
                exit;
            }

            // 3. DETERMINE TARGET SEMESTER
            $target_sem = 'Whole Year'; 
            $shs_tracks = ['senior high school', 'STEM', 'ABM', 'HUMSS'];
            
            if ($is_repeater) {
                 $target_sem = $prev_sec['semester'] ?? 'Whole Year';
            }
            elseif ($prev_sec && in_array($prev_sec['track'], $shs_tracks)) {
                if ($prev_sec['semester'] == '1st') $target_sem = '2nd';
                else $target_sem = '1st';
            }

            // 4. FIND TARGET SECTION
            $section_name = $prev_sec['section'];
            $track = $prev_sec['track'];
            
            $config = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
            $sy = $config['current_year'] ?? '2025-2026';

            $sem_sql = "AND (s.semester = ? OR s.semester = 'Whole Year')";
            if(in_array($track, $shs_tracks)) $sem_sql = "AND s.semester = ?";

            // --- FIXED QUERY HERE (Added JOIN) ---
            $sql_find = "SELECT s.id, sub.price 
                         FROM sections s
                         JOIN subjects sub ON s.code = sub.code
                         WHERE s.section = ? 
                         AND s.year_level = ? 
                         AND s.track = ? 
                         AND s.school_year = ? 
                         $sem_sql";
            
            $find_stmt = $pdo->prepare($sql_find);
            $find_stmt->execute([$section_name, $next_level, $track, $sy, $target_sem]);
            $subjects = $find_stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($subjects) == 0) {
                throw new Exception("No class section found for $next_level ($target_sem) - $section_name. Please create the section first.");
            }

            // 5. UPDATE PROFILE GRADE LEVEL
            $upd = $pdo->prepare("UPDATE students SET grade_level = ? WHERE student_id = ?");
            $upd->execute([$next_level, $pid]);

            // 6. ENROLL & BILL
            $total_fee = 0;
            $ins = $pdo->prepare("INSERT INTO enrollments (student_id, section_id, date_enrolled) VALUES (?, ?, CURDATE())");
            
            foreach($subjects as $sub) {
                $ins->execute([$pid, $sub['id']]);
                $total_fee += $sub['price'];
            }

            if($total_fee > 0) {
                $term_label = $is_repeater ? "Repeater Fee" : "Tuition";
                $assess = $pdo->prepare("INSERT INTO assessments (student_id, total_amount, school_year, term_mode) VALUES (?, ?, ?, ?)");
                $assess->execute([$pid, $total_fee, $sy, "$term_label: $next_level ($target_sem)"]);
            }
            
            $action_word = $is_repeater ? "Retained in" : "Promoted to";
            $enrolled_msg = "$action_word $next_level ($target_sem). Enrolled in " . count($subjects) . " subjects.";
            $msg = $enrolled_msg;
        }
        
        $pdo->commit();
        echo "SUCCESS|$msg"; 
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// ==========================================
// FETCH STUDENT LIST FOR TABLE
// ==========================================
$results = [];
$current_level = $_GET['level'] ?? '';

if ($current_level) {
    $sql = "SELECT s.student_id, a.id as account_pk, a.fname, a.lname, s.grade_level, s.track, sec.semester 
            FROM students s 
            JOIN account a ON s.student_id = a.id 
            LEFT JOIN enrollments e ON s.student_id = e.student_id
            LEFT JOIN sections sec ON e.section_id = sec.id
            WHERE s.grade_level = ? AND a.status = 'Active'
            GROUP BY s.student_id
            ORDER BY a.lname ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_level]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $s) {
        $sid = $s['account_pk'];

        // Stats
        $g_stmt = $pdo->prepare("SELECT AVG(grade) FROM grades WHERE student_id = ?");
        $g_stmt->execute([$sid]);
        $gwa = $g_stmt->fetchColumn();
        $gwa = $gwa ? round($gwa, 2) : 0;

        $f_stmt = $pdo->prepare("SELECT SUM(total_amount) FROM assessments WHERE student_id = ?");
        $f_stmt->execute([$sid]);
        $fees = $f_stmt->fetchColumn() ?: 0;
        
        $p_stmt = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE student_id = ?");
        $p_stmt->execute([$sid]);
        $paid = $p_stmt->fetchColumn() ?: 0;
        
        $balance = $fees - $paid;

        // Flags
        $academic_pass = ($gwa >= 75);
        $financial_clear = ($balance <= 0);
        
        $is_repeater = false;
        $next = "";
        $btn_label = "Locked";
        $can_action = false;

        $current_sem = $s['semester'] ?? 'Whole Year';
        $track_lower = strtolower($s['track'] ?? '');
        $is_shs = ($track_lower == 'senior high school' || in_array($s['track'], ['STEM', 'ABM', 'HUMSS']));

        // --- NEXT LEVEL LOGIC ---
        if ($is_shs) {
            if ($current_level == 'Grade 11') {
                if ($current_sem == '1st') $next = 'Grade 11'; // Move to 2nd Sem
                else $next = 'Grade 12';
            }
            else if ($current_level == 'Grade 12') {
                if ($current_sem == '1st') $next = 'Grade 12';
                else $next = 'Graduated';
            }
        } else {
            if ($current_level == 'Kinder') $next = 'Grade 1';
            else if ($current_level == 'Grade 10') $next = 'Grade 11';
            else {
                $num = (int)filter_var($current_level, FILTER_SANITIZE_NUMBER_INT);
                if($num > 0) $next = "Grade " . ($num + 1);
            }
        }

        // --- BUTTON LOGIC ---
        if ($academic_pass && $financial_clear) {
            $can_action = true;
            if ($next == 'Graduated') $btn_label = "Graduate";
            elseif ($next == $current_level) $btn_label = "Move to 2nd Sem";
            else $btn_label = "Promote to $next";
        } 
        elseif (!$academic_pass) {
            $can_action = true;
            $is_repeater = true;
            $next = $current_level;
            $btn_label = "Retain / Repeat";
        }
        else {
            $btn_label = "Pay Balance First";
        }

        $results[] = [
            'id' => $sid,
            'name' => $s['lname'] . ', ' . $s['fname'],
            'gwa' => $gwa,
            'balance' => $balance,
            'acad_stat' => $academic_pass ? 'PASSED' : 'FAILED',
            'fin_stat' => $financial_clear ? 'CLEARED' : 'UNPAID',
            'current_sem' => $current_sem,
            'next' => $next,
            'can_action' => $can_action,
            'btn_label' => $btn_label,
            'is_repeater' => $is_repeater
        ];
    }
}
?>

<div class="form-card" style="max-width: 1200px;">
    <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Re-enrollment & Promotion Manager</h2>

    <div class="no-print" style="background:#f0f8ff; padding:20px; border-radius:8px; margin-bottom:20px;">
        <label style="font-weight:bold;">Select Grade Level to Assess:</label>
        <form method="GET" style="display:flex; gap:10px; margin-top:10px;">
            <select name="level" required style="padding:10px; flex:1; border:1px solid #ccc; border-radius:4px;">
                <option value="">-- Select Level --</option>
                <option value="Kinder" <?php if($current_level=='Kinder') echo 'selected'; ?>>Kindergarten</option>
                <option value="Grade 7" <?php if($current_level=='Grade 7') echo 'selected'; ?>>Grade 7</option>
                <option value="Grade 8" <?php if($current_level=='Grade 8') echo 'selected'; ?>>Grade 8</option>
                <option value="Grade 9" <?php if($current_level=='Grade 9') echo 'selected'; ?>>Grade 9</option>
                <option value="Grade 10" <?php if($current_level=='Grade 10') echo 'selected'; ?>>Grade 10</option>
                <option value="Grade 11" <?php if($current_level=='Grade 11') echo 'selected'; ?>>Grade 11</option>
                <option value="Grade 12" <?php if($current_level=='Grade 12') echo 'selected'; ?>>Grade 12</option>
            </select>
            <button type="button" onclick="loadZone('re_enroll.php?' + new URLSearchParams(new FormData(this.form)).toString())" class="btn-save" style="width:auto;">Load List</button>
        </form>
    </div>

    <?php if($current_level): ?>
        <h3 style="color:#002D72;">Candidates for Promotion (<?php echo htmlspecialchars($current_level); ?>)</h3>
        
        <style>
            .promo-table { width:100%; border-collapse:collapse; font-size:0.9rem; }
            .promo-table th { background:#002D72; color:white; padding:10px; text-align:left; }
            .promo-table td { border-bottom:1px solid #eee; padding:10px; vertical-align:middle; }
            .stat-pass { color:green; font-weight:bold; background:#d1e7dd; padding:2px 6px; border-radius:4px; }
            .stat-fail { color:red; font-weight:bold; background:#f8d7da; padding:2px 6px; border-radius:4px; }
            .stat-ok { color:green; font-weight:bold; }
            .stat-bad { color:red; font-weight:bold; }
            .sem-tag { background:#e2e3e5; color:#383d41; padding:2px 6px; border-radius:4px; font-size:0.8em; }
        </style>

        <table class="promo-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Current</th>
                    <th>GWA</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($results)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:20px;">No active students found.</td></tr>
                <?php else: ?>
                    <?php foreach($results as $r): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($current_level); ?>
                            <span class="sem-tag"><?php echo htmlspecialchars($r['current_sem'] ?: 'Whole Year'); ?></span>
                        </td>
                        <td><?php echo $r['gwa']; ?> <span class="<?php echo ($r['acad_stat']=='PASSED') ? 'stat-pass':'stat-fail'; ?>"><?php echo $r['acad_stat']; ?></span></td>
                        <td>₱<?php echo number_format($r['balance'], 2); ?> <span class="<?php echo ($r['fin_stat']=='CLEARED') ? 'stat-ok':'stat-bad'; ?>"><?php echo $r['fin_stat']; ?></span></td>
                        <td>
                            <span style="font-weight:bold; color:<?php echo $r['can_action'] ? '#002D72' : '#666'; ?>">
                                <?php echo $r['btn_label']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if($r['can_action']): ?>
                                <button onclick="promoteStudent(<?php echo $r['id']; ?>, '<?php echo $r['next']; ?>', <?php echo $r['is_repeater']?1:0; ?>)" class="btn-save" style="padding:5px 15px; font-size:0.85rem; <?php if($r['is_repeater']) echo 'background:#dc3545;'; ?>">
                                    <?php echo $r['is_repeater'] ? 'Repeat' : 'Proceed'; ?>
                                </button>
                            <?php else: ?>
                                <button disabled style="padding:5px 15px; border:1px solid #ccc; background:#eee; color:#888; cursor:not-allowed; border-radius:4px;">Locked</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>