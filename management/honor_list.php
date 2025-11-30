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
$selected_period = $_GET['period'] ?? '1'; // 1=Q1, 5=Final

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
            // Final Grade (Average of all quarters)
            $g_sql = "SELECT AVG(grade) as gwa FROM grades WHERE student_id = ?";
            $g_stmt = $pdo->prepare($g_sql);
            $g_stmt->execute([$sid]);
        } else {
            // Specific Quarter
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

    // 3. MANUAL SORT (Highest to Lowest) - Caveman Style
    // We loop through the list and swap items if the next one is bigger
    $count = count($ranking);
    for ($i = 0; $i < $count; $i++) {
        for ($j = $i + 1; $j < $count; $j++) {
            // If the one we are checking ($j) is BIGGER than current ($i), swap them
            if ($ranking[$j]['average'] > $ranking[$i]['average']) {
                $temp = $ranking[$i];
                $ranking[$i] = $ranking[$j];
                $ranking[$j] = $temp;
            }
        }
    }
}
?>

<div class="form-card" style="max-width: 1000px;">
    <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Honor List & Ranking</h2>
    
    <form method="GET" class="no-print" style="background:#f0f8ff; padding:20px; border-radius:8px; display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:15px; align-items:end;">
        <div class="form-group" style="margin:0;">
            <label>School Year</label>
            <select name="sy" style="width:100%; padding:8px;">
                <?php foreach($school_years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php if($selected_sy==$y) echo 'selected'; ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin:0;">
            <label>Grade Level</label>
            <select name="level" required style="width:100%; padding:8px;">
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

        <div class="form-group" style="margin:0;">
            <label>Period</label>
            <select name="period" style="width:100%; padding:8px;">
                <option value="1" <?php if($selected_period==1) echo 'selected'; ?>>1st Quarter / Prelim</option>
                <option value="2" <?php if($selected_period==2) echo 'selected'; ?>>2nd Quarter / Midterm</option>
                <option value="3" <?php if($selected_period==3) echo 'selected'; ?>>3rd Quarter / Pre-Fi</option>
                <option value="4" <?php if($selected_period==4) echo 'selected'; ?>>4th Quarter / Finals</option>
                <option value="5" <?php if($selected_period==5) echo 'selected'; ?>>General Average (Final)</option>
            </select>
        </div>

        <button type="button" onclick="loadZone('honor_list.php?' + new URLSearchParams(new FormData(this.form)).toString())" class="btn-save" style="height:40px;">Generate</button>
    </form>

    <?php if($selected_level): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:30px; margin-bottom:10px;">
            <h3 style="margin:0;">Ranking: <?php echo $selected_level; ?> (SY <?php echo $selected_sy; ?>)</h3>
            <button onclick="window.print()" class="btn-save no-print" style="background:#6c757d;">Print List</button>
        </div>

        <style>
            .rank-table { width: 100%; border-collapse: collapse; }
            .rank-table th { background: #002D72; color: white; padding: 10px; text-align: left; }
            .rank-table td { padding: 10px; border-bottom: 1px solid #eee; }
            .rank-1 { background-color: #fff3cd; font-weight: bold; } /* Gold */
            .rank-2 { background-color: #e0e0e0; } /* Silver */
            .rank-3 { background-color: #cd7f32; color: white; } /* Bronze */
            
            @media print {
                .no-print, .sidebar-right, .header { display: none !important; }
                .form-card { box-shadow: none; padding: 0; }
                .rank-table { border: 1px solid black; }
                .rank-table th, .rank-table td { border: 1px solid black; color: black; }
                .rank-table th { background: #eee !important; -webkit-print-color-adjust: exact; }
            }
        </style>

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
                        $class = ($rank <= 3) ? "rank-$rank" : ""; // Highlight Top 3
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