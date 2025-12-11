<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Access Denied."; exit;
}

// CENTRALIZED DB CONNECTION (Assuming you have this now, if not, use the manual block)
// require_once '../functions/db.php'; 
// OR Manual Block:
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- HANDLE POST ACTION (Promote/Retain) ---
if (isset($_POST['promote_id'])) {
    $pid = $_POST['promote_id'];
    $next_level = $_POST['next_level'];
    $is_repeater = $_POST['is_repeater'] ?? 0;
    
    try {
        $pdo->beginTransaction();

        if($next_level == 'Graduated') {
            $upd = $pdo->prepare("UPDATE account SET status = 'Graduated', last_active_date = CURDATE() WHERE id = ?");
            $upd->execute([$pid]);
            $msg = "Student Graduated.";
        } else {
            // 1. Get Current Status
            $stmt = $pdo->prepare("SELECT s.section, s.track, s.semester 
                                   FROM enrollments e 
                                   JOIN sections s ON e.section_id = s.id 
                                   WHERE e.student_id = ? 
                                   ORDER BY e.date_enrolled DESC LIMIT 1");
            $stmt->execute([$pid]);
            $prev_sec = $stmt->fetch(PDO::FETCH_ASSOC);
            $section_name = $prev_sec['section'] ?? '';
            $track = $prev_sec['track'] ?? '';

            // 2. Determine Semester
            $target_sem = 'Whole Year'; 
            $shs_tracks = ['senior high school', 'STEM', 'ABM', 'HUMSS'];
            
            if ($is_repeater) {
                 $target_sem = $prev_sec['semester'] ?? 'Whole Year';
            } elseif ($prev_sec && in_array($track, $shs_tracks)) {
                $target_sem = ($prev_sec['semester'] == '1st') ? '2nd' : '1st';
            }

            // 3. Find Target Section (With Correct JOIN for Price)
            $sy_row = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
            $sy = $sy_row['current_year'] ?? date('Y').'-'.(date('Y')+1);

            $sem_sql = in_array($track, $shs_tracks) ? "AND s.semester = ?" : "AND (s.semester = ? OR s.semester = 'Whole Year')";
            
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

            if (count($subjects) == 0) throw new Exception("No class section found for $next_level ($target_sem) - $section_name. Please create the section first.");

            // 4. Execute Update
            $upd = $pdo->prepare("UPDATE students SET grade_level = ? WHERE student_id = ?");
            $upd->execute([$next_level, $pid]);

            $total_fee = 0;
            $ins = $pdo->prepare("INSERT INTO enrollments (student_id, section_id, date_enrolled) VALUES (?, ?, CURDATE())");
            foreach($subjects as $sub) {
                $ins->execute([$pid, $sub['id']]);
                $total_fee += $sub['price'];
            }

            if($total_fee > 0) {
                $lbl = $is_repeater ? "Repeater Fee" : "Tuition";
                $assess = $pdo->prepare("INSERT INTO assessments (student_id, total_amount, school_year, term_mode) VALUES (?, ?, ?, ?)");
                $assess->execute([$pid, $total_fee, $sy, "$lbl: $next_level ($target_sem)"]);
            }
            
            $action_word = $is_repeater ? "Retained in" : "Promoted to";
            $msg = "$action_word $next_level ($target_sem).";
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
// VIEW LOGIC (Table Display)
// ==========================================
$results = [];
$current_level = $_GET['level'] ?? '';

if ($current_level) {
    // We select sec.id (section_id) to accurately calculate GWA for THAT SPECIFIC SECTION/YEAR
    $sql = "SELECT s.student_id, a.id as account_pk, a.fname, a.lname, s.grade_level, s.track, sec.semester, sec.id as current_section_id
            FROM students s 
            JOIN account a ON s.student_id = a.id 
            LEFT JOIN enrollments e ON s.student_id = e.student_id 
            LEFT JOIN sections sec ON e.section_id = sec.id 
            WHERE s.grade_level = ? AND a.status = 'Active' 
            GROUP BY s.student_id ORDER BY a.lname ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_level]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $s) {
        $sid = $s['account_pk'];
        $current_sec_id = $s['current_section_id'];

        // --- GWA FIX: Filter by Section ID ---
        // Only average grades from the current section/year, NOT history
        $gwa_sql = "SELECT AVG(grade) FROM grades WHERE student_id = ? AND section_id = ?";
        $g_stmt = $pdo->prepare($gwa_sql);
        $g_stmt->execute([$sid, $current_sec_id]);
        $gwa = $g_stmt->fetchColumn() ?: 0;

        // Balance Check
        $fees = $pdo->query("SELECT SUM(total_amount) FROM assessments WHERE student_id = $sid")->fetchColumn() ?: 0;
        $paid = $pdo->query("SELECT SUM(amount) FROM payments WHERE student_id = $sid")->fetchColumn() ?: 0;
        $balance = $fees - $paid;

        // Status Flags
        $acad_pass = ($gwa >= 75);
        $fin_clear = ($balance <= 0);

        // --- NEXT LEVEL LOGIC ---
        $next_lvl = "";
        $potential_next = ""; 
        
        if ($current_level == 'Kinder') $potential_next = 'Grade 1';
        elseif ($current_level == 'Grade 12') $potential_next = 'Graduated';
        else {
            $n = (int)filter_var($current_level, FILTER_SANITIZE_NUMBER_INT);
            $potential_next = "Grade " . ($n + 1);
        }

        $next_sem = 'Whole Year';
        if(in_array($s['track'], ['STEM','ABM','HUMSS','senior high school'])) {
            if($s['grade_level'] == 'Grade 11') {
                if($s['semester'] == '1st') { $potential_next = 'Grade 11'; $next_sem='2nd'; }
                else { $potential_next = 'Grade 12'; $next_sem='1st'; }
            } elseif($s['grade_level'] == 'Grade 12') {
                if($s['semester'] == '1st') { $potential_next = 'Grade 12'; $next_sem='2nd'; }
                else { $potential_next = 'Graduated'; }
            }
        }

        // --- BUTTON STATE ---
        $btn_label = "Locked";
        $can_action = false;
        $is_repeater = false;

        if (!$fin_clear) {
            $can_action = false;
            $btn_label = "Pay Balance";
            $next_lvl = $current_level; 
        } 
        elseif ($acad_pass) {
            $can_action = true;
            $next_lvl = $potential_next;
            if($next_lvl == 'Graduated') $btn_label = "Graduate";
            elseif($next_lvl == $current_level) $btn_label = "Next Sem";
            else $btn_label = "Promote";
        } 
        else {
            $can_action = true;
            $is_repeater = true;
            $next_lvl = $current_level; 
            $btn_label = "Retain";
        }

        $results[] = [
            'id' => $sid,
            'name' => $s['lname'].', '.$s['fname'],
            'gwa' => number_format($gwa,2),
            'balance' => $balance,
            'acad_stat' => $acad_pass ? 'PASSED' : 'FAILED',
            'fin_stat' => $fin_clear ? 'CLEARED' : 'UNPAID',
            'next' => $next_lvl,
            'can_action' => $can_action,
            'btn_label' => $btn_label,
            'is_repeater' => $is_repeater,
            'next_sem' => $next_sem
        ];
    }
}
?>

<div class="form-card" style="max-width: 1200px;">
    <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Re-Enrollment Manager</h2>
    
    <div class="no-print" style="background:#f0f8ff; padding:20px; margin-bottom:20px;">
        <form method="GET" style="display:flex; gap:10px;">
            <select name="level" required style="padding:10px; flex:1;">
                <option value="">-- Select Grade --</option>
                <option value="Kinder" <?php if($current_level=='Kinder')echo'selected';?>>Kinder</option>
                <?php for($i=7;$i<=12;$i++) echo "<option value='Grade $i' ".($current_level=="Grade $i"?'selected':'').">Grade $i</option>"; ?>
            </select>
            <button type="button" onclick="loadZone('re_enroll.php?' + new URLSearchParams(new FormData(this.form)).toString())" class="btn-save" style="width:auto;">Load</button>
        </form>
    </div>

    <?php if($current_level): ?>
    <table class="promo-table" style="width:100%; border-collapse:collapse;">
        <tr style="background:#002D72; color:white;">
            <th style="padding:10px;">Name</th>
            <th>GWA (Current)</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach($results as $r): ?>
        <tr style="border-bottom:1px solid #eee;">
            <td style="padding:10px;"><strong><?php echo $r['name']; ?></strong></td>
            
            <td>
                <?php echo $r['gwa']; ?> 
                <span class="<?php echo ($r['acad_stat']=='PASSED') ? 'stat-pass':'stat-fail'; ?>" style="font-size:0.8em; margin-left:5px;">
                    <?php echo $r['acad_stat']; ?>
                </span>
            </td>
            
            <td>
                ₱<?php echo number_format($r['balance']); ?> 
                <span class="<?php echo ($r['fin_stat']=='CLEARED') ? 'stat-ok':'stat-bad'; ?>" style="font-size:0.8em; margin-left:5px;">
                    <?php echo $r['fin_stat']; ?>
                </span>
            </td>
            
            <td>
                <?php if(!$r['can_action']): ?>
                    <span style="color:#666;">Locked (Unpaid)</span>
                <?php elseif($r['is_repeater']): ?>
                    <span style="color:#dc3545; font-weight:bold;">Retain in <?php echo $r['next']; ?></span>
                <?php else: ?>
                    <span style="color:#198754; font-weight:bold;">Ready for <?php echo $r['next']; ?></span>
                <?php endif; ?>
            </td>
            
            <td>
                <?php if($r['can_action']): ?>
                    <button onclick="promoteStudent(<?php echo $r['id']; ?>, '<?php echo $r['next']; ?>', '<?php echo $r['next_sem']; ?>', <?php echo $r['is_repeater']?1:0; ?>)" 
                            class="btn-save" style="padding:5px 15px; font-size:0.85rem; <?php if($r['is_repeater']) echo 'background:#dc3545;'; ?>">
                        <?php echo $r['btn_label']; ?> to <?php echo $r['next']; ?>
                    </button>
                <?php else: ?>
                    <button disabled style="padding:5px 15px; background:#eee; color:#888; border:1px solid #ccc;">Locked</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>