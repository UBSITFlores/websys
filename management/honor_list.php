<?php
require_once '../functions/db.php';
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Session Expired."; exit;
}

// CONFIG
$sy_query = $pdo->query("SELECT DISTINCT school_year FROM sections ORDER BY school_year DESC");
$school_years = $sy_query->fetchAll(PDO::FETCH_COLUMN);
$default_sy = $current_sy ?? $school_years[0];

// PARAMS
$selected_sy = $_GET['sy'] ?? $default_sy;
$selected_level = $_GET['level'] ?? '';
$selected_period = $_GET['period'] ?? ''; 

$ranking = [];

if ($selected_level && $selected_period) {
    // 1. Get Students
    $sql = "SELECT DISTINCT st.student_id, a.id as account_pk, a.fname, a.lname, sec.section, sec.track
            FROM students st
            JOIN account a ON st.student_id = a.id
            JOIN enrollments e ON st.student_id = e.student_id
            JOIN sections sec ON e.section_id = sec.id
            WHERE sec.year_level = :yl AND sec.school_year = :sy";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':yl' => $selected_level, ':sy' => $selected_sy]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Calculate GWA
    foreach ($students as $s) {
        $sid = $s['account_pk'];
        $track = strtolower($s['track']);
        $g_sql = "";
        $params = [];

        if ($track == 'senior high school' || in_array($s['track'], ['STEM', 'ABM', 'HUMSS'])) {
            if ($selected_period == '5') { 
                $g_sql = "SELECT AVG(grade) FROM grades WHERE student_id = ? AND quarter IN (1, 2, 3)";
                $params = [$sid];
            } else {
                if($selected_period == '1st Sem') $g_sql = "SELECT AVG(grade) FROM grades WHERE student_id = ? AND quarter IN (1, 2)";
                else $g_sql = "SELECT AVG(grade) FROM grades WHERE student_id = ? AND quarter IN (3, 4)";
                $params = [$sid];
            }
        } else {
            if ($selected_period == '5') {
                $g_sql = "SELECT AVG(grade) FROM grades WHERE student_id = ? AND quarter IN (1, 2, 3, 4)";
                $params = [$sid];
            } else {
                $g_sql = "SELECT AVG(grade) FROM grades WHERE student_id = ? AND quarter = ?";
                $params = [$sid, $selected_period];
            }
        }

        if ($g_sql != "") {
            $g_stmt = $pdo->prepare($g_sql);
            $g_stmt->execute($params);
            $gwa = $g_stmt->fetchColumn();

            if ($gwa > 0) {
                $ranking[] = [
                    'name' => $s['lname'] . ', ' . $s['fname'],
                    'section' => $s['section'],
                    'track' => $s['track'],
                    'average' => round($gwa, 2)
                ];
            }
        }
    }

    // 3. SORT
    $count = count($ranking);
    for ($i = 0; $i < $count; $i++) {
        for ($j = $i + 1; $j < $count; $j++) {
            if ($ranking[$j]['average'] > $ranking[$i]['average']) {
                $temp = $ranking[$i];
                $ranking[$i] = $ranking[$j];
                $ranking[$j] = $temp;
            }
        }
    }
}
?>

<style>
    .form-card { max-width: 1000px; margin: 0 auto; }
    .form-title { color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px; }
    .form-group { margin: 0; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.95rem; }
    
    .filter-form { 
        background:#f0f8ff; padding:20px; border-radius:8px; 
        display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:15px; align-items:end; 
        margin-bottom: 30px;
    }
    .form-select { width:100%; padding:10px; border: 1px solid #aaa; border-radius: 4px; font-size: 1rem; }

    .btn-generate { 
        background:#002D72; color:white; border:none; border-radius:4px; 
        cursor:pointer; font-weight:bold; height: 40px; padding: 0 20px; width: 100%; font-size: 1rem;
    }
    .btn-generate:hover { background: #004099; }
    
    .report-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
    .report-heading { margin:0; color:#002D72; }
    
    .btn-print { 
        background:#6c757d; color:white; border:none; border-radius:4px; 
        cursor:pointer; font-weight:bold; padding: 8px 15px; font-size: 0.9rem;
    }
    .btn-print:hover { background: #5a6268; }

    .rank-table { width: 100%; border-collapse: collapse; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
    .rank-table th { background: #002D72; color: white; padding: 12px 10px; text-align: left; font-size: 1rem; }
    .rank-table td { padding: 10px; border-bottom: 1px solid #eee; font-size: 0.95rem; }
    .rank-table tr:last-child td { border-bottom: none; }
    .rank-table td small { color: #666; font-style: italic; }

    .rank-1 { background-color: #fff3cd; font-weight: bold; border-left: 5px solid gold; } 
    .rank-2 { background-color: #f8f9fa; border-left: 5px solid silver; }
    .rank-3 { background-color: #fdf2f0; border-left: 5px solid #cd7f32; }
    
    @media print {
        .no-print, .sidebar-right, .header { display: none !important; }
        .form-card { box-shadow: none; padding: 0; }
        .rank-table { border: 1px solid black; }
        .rank-table th, .rank-table td { border: 1px solid black; color: black; }
    }
</style>

<div class="form-card">
    <h2 class="form-title">Honor List & Ranking</h2>
    
    <form method="GET" class="no-print filter-form">
        
        <div class="form-group">
            <label>School Year</label>
            <select name="sy" class="form-select">
                <?php foreach($school_years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php if($selected_sy==$y) echo 'selected'; ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Grade Level</label>
            <select name="level" id="hl_level" onchange="updatePeriodOptions()" class="form-select">
                <option value="">-- Select Level --</option>
                <option value="Kinder" <?php if($selected_level=='Kinder') echo 'selected'; ?>>Kindergarten</option>
                <option value="Grade 7" <?php if($selected_level=='Grade 7') echo 'selected'; ?>>Grade 7</option>
                <option value="Grade 8" <?php if($selected_level=='Grade 8') echo 'selected'; ?>>Grade 8</option>
                <option value="Grade 9" <?php if($selected_level=='Grade 9') echo 'selected'; ?>>Grade 9</option>
                <option value="Grade 10" <?php if($selected_level=='Grade 10') echo 'selected'; ?>>Grade 10</option>
                <option value="Grade 11" <?php if($selected_level=='Grade 11') echo 'selected'; ?>>Grade 11</option>
                <option value="Grade 12" <?php if($selected_level=='Grade 12') echo 'selected'; ?>>Grade 12</option>
            </select>
        </div>

        <div class="form-group">
            <label>Period</label>
            <select name="period" id="hl_period" class="form-select">
                <?php 
                if ($selected_level) {
                    $isSHS = ($selected_level == 'Grade 11' || $selected_level == 'Grade 12');
                    
                    if ($isSHS) {
                        $opts = ['1st Sem'=>'1st Semester', '2nd Sem'=>'2nd Semester'];
                    } else {
                        $opts = ['1'=>'1st Quarter', '2'=>'2nd Quarter', '3'=>'3rd Quarter', '4'=>'4th Quarter', '5'=>'General Average (Final)'];
                    }

                    foreach($opts as $val => $label) {
                        $sel = ($selected_period == $val) ? 'selected' : '';
                        echo "<option value='$val' $sel>$label</option>";
                    }
                } else {
                    echo "<option value=''>-- Select Level First --</option>";
                }
                ?>
            </select>
        </div>

        <button type="button" onclick="loadZone('honor_list.php?' + new URLSearchParams(new FormData(this.form)).toString())" class="btn-generate">Generate</button>
    </form>

    <?php if($selected_level && $selected_period): ?>
        <div class="report-actions">
            <h3 class="report-heading">Ranking: <?php echo $selected_level; ?> (<?php echo $selected_period; ?>)</h3>
            <button onclick="window.print()" class="btn-print no-print">Print List</button>
        </div>

        <table class="rank-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align:center;">Rank</th>
                    <th>Student Name</th>
                    <th>Section / Track</th>
                    <th style="text-align:center;">Average</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($ranking)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:20px;">No grades recorded for this selection.</td></tr>
                <?php else: ?>
                    <?php foreach($ranking as $i => $r): 
                        $rank = $i + 1;
                        $class = ($rank <= 3) ? "rank-$rank" : ""; 
                    ?>
                    <tr class="<?php echo $class; ?>">
                        <td style="text-align:center; font-weight:bold;"><?php echo $rank; ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo htmlspecialchars($r['section']); ?> <small>(<?php echo $r['track']; ?>)</small></td>
                        <td style="text-align:center; font-weight:bold;"><?php echo $r['average']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>