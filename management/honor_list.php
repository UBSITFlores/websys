<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Session Expired."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// FETCH CONFIG FOR DEFAULTS
$sy_query = $pdo->query("SELECT DISTINCT school_year FROM sections ORDER BY school_year DESC");
$school_years = $sy_query->fetchAll(PDO::FETCH_COLUMN);
$default_sy = $school_years[0] ?? '2025-2026';

// --- GENERATE REPORT ---
$ranking = [];
$selected_sy = $_GET['sy'] ?? $default_sy;
$selected_level = $_GET['level'] ?? '';
$selected_period = $_GET['period'] ?? '1';

if ($selected_level) {
    // 1. Get all students enrolled in this Grade Level & School Year
    $sql = "SELECT DISTINCT st.student_id, a.fname, a.lname, sec.section, sec.track
            FROM students st
            JOIN account a ON st.student_id = a.id
            JOIN enrollments e ON st.student_id = e.student_id
            JOIN sections sec ON e.section_id = sec.id
            WHERE sec.year_level = :yl AND sec.school_year = :sy";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':yl' => $selected_level, ':sy' => $selected_sy]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Calculate Average for each student
    foreach ($students as $s) {
        $sid = $s['student_id'];
        
        if ($selected_period == '5') {
            $g_sql = "SELECT AVG(grade) as gwa FROM grades WHERE student_id = ?";
            $g_stmt = $pdo->prepare($g_sql);
            $g_stmt->execute([$sid]);
        } else {
            $g_sql = "SELECT AVG(grade) as gwa FROM grades WHERE student_id = ? AND quarter = ?";
            $g_stmt = $pdo->prepare($g_sql);
            $g_stmt->execute([$sid, $selected_period]);
        }
        
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

    // 3. MANUAL SORT (Highest to Lowest)
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

<div class="form-card">
    <h2>Honor List & Ranking</h2>
    
    <form method="GET" class="honor-form no-print">
        <div class="form-group">
            <label>School Year</label>
            <select name="sy">
                <?php foreach($school_years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php if($selected_sy==$y) echo 'selected'; ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Grade Level</label>
            <select name="level" required>
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
            <select name="period">
                <option value="1" <?php if($selected_period==1) echo 'selected'; ?>>1st Quarter / Prelim</option>
                <option value="2" <?php if($selected_period==2) echo 'selected'; ?>>2nd Quarter / Midterm</option>
                <option value="3" <?php if($selected_period==3) echo 'selected'; ?>>3rd Quarter / Pre-Fi</option>
                <option value="4" <?php if($selected_period==4) echo 'selected'; ?>>4th Quarter / Finals</option>
                <option value="5" <?php if($selected_period==5) echo 'selected'; ?>>General Average (Final)</option>
            </select>
        </div>

        <button type="button" onclick="loadZone('honor_list.php?' + new URLSearchParams(new FormData(this.form)).toString())" class="btn-save">Generate</button>
    </form>

    <?php if($selected_level): ?>
        <div class="ranking-header">
            <h3>Ranking: <?php echo $selected_level; ?> (SY <?php echo $selected_sy; ?>)</h3>
            <button onclick="window.print()" class="btn-save secondary no-print">Print List</button>
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