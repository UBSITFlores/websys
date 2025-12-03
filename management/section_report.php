<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    header('Location: ../account/login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. FETCH ALL SECTIONS FOR DROPDOWN
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

<div class="form-card">
    <div class="no-print">
        <h2>Class Record Viewer</h2>

        <form method="GET">
            <select name="section_id">
                <option value="">-- Select a Section --</option>
                <?php 
                for ($i = 0; $i < count($all_sections); $i++) {
                    $s = $all_sections[$i];
                    $sel = ($s['id'] == $selected_section_id) ? 'selected' : '';
                    echo "<option value='" . $s['id'] . "' $sel>" . $s['year_level'] . " - " . $s['section'] . " (" . $s['code'] . ")</option>";
                }
                ?>
            </select>
            <button type="button" onclick="loadZone('section_report.php?' + new URLSearchParams(new FormData(this.form)).toString())" class="btn-save">Load Class</button>
        </form>
    </div>

    <?php if ($selected_section_id): ?>
        <div class="report-header">
            <h1>
                <?php echo htmlspecialchars($sec_info['year_level'] . " - " . $sec_info['section']); ?>
            </h1>
            <p>
                Subject: <strong><?php echo htmlspecialchars($sec_info['code'] . ' - ' . $sec_info['description']); ?></strong><br>
                Track: <?php echo htmlspecialchars(ucfirst($sec_info['track'])); ?> | SY: <?php echo htmlspecialchars($sec_info['school_year']); ?>
            </p>
        </div>
        
        <div class="print-button-container no-print">
            <button onclick="window.print()" class="btn-save secondary">🖨️ Print Class Record</button>
        </div>

        <table class="rec-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <?php 
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
                    for ($i = 0; $i < count($students); $i++) {
                        $stu = $students[$i];
                        $sid = $stu['student_id'];
                        
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($stu['lname'] . ", " . $stu['fname']) . "</strong></td>";

                        $total = 0;
                        $count = 0;

                        for ($k = 0; $k < count($col_keys); $k++) {
                            $q_key = $col_keys[$k];
                            $val = isset($grades_map[$sid][$q_key]) ? $grades_map[$sid][$q_key] : '';
                            
                            echo "<td>$val</td>";

                            if ($val != "" && is_numeric($val)) {
                                $total = $total + $val;
                                $count = $count + 1;
                            }
                        }

                        $final = "-";
                        $status = "-";
                        if ($count > 0) {
                            $avg = $total / count($col_keys);
                            $final = number_format($avg, 2);
                            if ($avg >= 75) {
                                $status = "<span class='passed'>PASSED</span>";
                            } else {
                                $status = "<span class='failed'>FAILED</span>";
                            }
                        }

                        echo "<td>" . $final . "</td>";
                        echo "<td>" . $status . "</td>";
                        echo "</tr>";
                    }
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>