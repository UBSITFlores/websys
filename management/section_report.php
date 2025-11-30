<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    header('Location: ../account/login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. FETCH ALL SECTIONS FOR DROPDOWN
// Caveman: Simple query
$sec_stmt = $pdo->query("SELECT * FROM sections ORDER BY track, year_level, section ASC");
$all_sections = $sec_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. HANDLE SEARCH
$selected_section_id = "";
$students = [];
$grades_map = [];
$columns = [];
$col_keys = [];

if (isset($_GET['section_id']) && $_GET['section_id'] != "") {
    $selected_section_id = $_GET['section_id'];
    
    // Get Section Details
    $stmt = $pdo->prepare("SELECT * FROM sections WHERE id = ?");
    $stmt->execute([$selected_section_id]);
    $sec_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Determine Columns (Manual If-Else)
    $track = strtolower($sec_info['track']);
    if ($track == 'senior high school') {
        $columns = ['Prelim', 'Midterm', 'Finals'];
        $col_keys = [1, 2, 3];
    } else {
        $columns = ['Q1', 'Q2', 'Q3', 'Q4'];
        $col_keys = [1, 2, 3, 4];
    }

    // Get Students in this Section
    $stu_sql = "SELECT s.student_id, a.lname, a.fname 
                FROM enrollments e 
                JOIN account a ON e.student_id = a.id
                JOIN students s ON s.student_id = a.id
                WHERE e.section_id = ? 
                ORDER BY a.lname ASC";
    $stu_stmt = $pdo->prepare($stu_sql);
    $stu_stmt->execute([$selected_section_id]);
    $students = $stu_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Grades
    $grd_sql = "SELECT student_id, quarter, grade FROM grades WHERE section_id = ?";
    $grd_stmt = $pdo->prepare($grd_sql);
    $grd_stmt->execute([$selected_section_id]);
    
    // Map Grades manually
    while ($row = $grd_stmt->fetch(PDO::FETCH_ASSOC)) {
        $sid = $row['student_id'];
        $q = $row['quarter'];
        $grades_map[$sid][$q] = $row['grade'];
    }
}
?>

<div class="form-card" style="max-width: 1000px;">
    <div class="no-print">
        <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Class Record Viewer</h2>

        <form method="GET" style="display:flex; gap:10px; background:#f0f8ff; padding:15px; border-radius:8px;">
            <select name="section_id" style="flex:1; padding:10px; border:1px solid #aaa; border-radius:4px;">
                <option value="">-- Select a Section --</option>
                <?php 
                // Simple Loop for Options
                for ($i = 0; $i < count($all_sections); $i++) {
                    $s = $all_sections[$i];
                    $sel = ($s['id'] == $selected_section_id) ? 'selected' : '';
                    echo "<option value='" . $s['id'] . "' $sel>" . $s['year_level'] . " - " . $s['section'] . " (" . $s['code'] . ")</option>";
                }
                ?>
            </select>
            <button type="button" onclick="loadZone('section_report.php?' + new URLSearchParams(new FormData(this.form)).toString())" class="btn-save" style="width:auto; padding:10px 30px;">Load Class</button>
        </form>
    </div>

    <?php if ($selected_section_id): ?>
        <div class="report-header" style="margin-top:30px; margin-bottom:20px; border-bottom:2px solid #002D72; padding-bottom:10px;">
            <h1 style="margin:0; color:#002D72; font-size:1.8rem;">
                <?php echo htmlspecialchars($sec_info['year_level'] . " - " . $sec_info['section']); ?>
            </h1>
            <p style="margin:5px 0; color:#555;">
                Subject: <strong><?php echo htmlspecialchars($sec_info['code'] . ' - ' . $sec_info['description']); ?></strong><br>
                Track: <?php echo htmlspecialchars(ucfirst($sec_info['track'])); ?> | SY: <?php echo htmlspecialchars($sec_info['school_year']); ?>
            </p>
        </div>
        
        <div style="text-align:right; margin-bottom:15px;" class="no-print">
            <button onclick="window.print()" class="btn-save" style="background:#6c757d;">🖨️ Print Class Record</button>
        </div>

        <style>
            .rec-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
            .rec-table th { background: #002D72; color: white; padding: 10px; text-align: center; border: 1px solid #001f52; }
            .rec-table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
            .rec-table th:first-child, .rec-table td:first-child { text-align: left; }
            
            @media print {
                .no-print, .sidebar-right, .header { display: none !important; }
                body, .content-zone, .form-card { background: white; margin: 0; padding: 0; width: 100%; box-shadow: none; }
                .rec-table th { background: #eee !important; color: black !important; border: 1px solid black; -webkit-print-color-adjust: exact; }
                .rec-table td { border: 1px solid black; }
            }
        </style>

        <table class="rec-table">
            <thead>
                <tr>
                    <th style="width:30%;">Student Name</th>
                    <?php 
                    // Loop Columns
                    for ($c = 0; $c < count($columns); $c++) {
                        echo "<th>" . $columns[$c] . "</th>";
                    }
                    ?>
                    <th style="background:#001f52;">Final</th>
                    <th style="background:#001f52;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) == 0): ?>
                    <tr><td colspan="7" style="text-align:center; padding:20px;">No students found in this section.</td></tr>
                <?php else: ?>
                    <?php 
                    // Loop Students
                    for ($i = 0; $i < count($students); $i++) {
                        $stu = $students[$i];
                        $sid = $stu['student_id']; // Account ID PK
                        
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($stu['lname'] . ", " . $stu['fname']) . "</strong></td>";

                        $total = 0;
                        $count = 0;

                        // Loop Grades
                        for ($k = 0; $k < count($col_keys); $k++) {
                            $q_key = $col_keys[$k];
                            // Manual check if key exists
                            $val = isset($grades_map[$sid][$q_key]) ? $grades_map[$sid][$q_key] : '';
                            
                            echo "<td>$val</td>";

                            if ($val != "" && is_numeric($val)) {
                                $total = $total + $val;
                                $count = $count + 1;
                            }
                        }

                        // Calculate Final
                        $final = "-";
                        $status = "-";
                        if ($count > 0) {
                            $avg = $total / count($col_keys); // Strict average (divide by total quarters)
                            // Or $avg = $total / $count; (divide by filled quarters) - Let's do strict.
                            $final = number_format($avg, 2);
                            if ($avg >= 75) {
                                $status = "<span style='color:green; font-weight:bold;'>PASSED</span>";
                            } else {
                                $status = "<span style='color:red; font-weight:bold;'>FAILED</span>";
                            }
                        }

                        echo "<td style='font-weight:bold;'>$final</td>";
                        echo "<td>$status</td>";
                        echo "</tr>";
                    }
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>