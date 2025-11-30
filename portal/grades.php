<?php
if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

// DB CONNECTION
$host = "localhost"; $user = "root"; $pass = ""; $db = "portal";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch(PDOException $e) { die("DB Error"); }

// GET STUDENT ID
$stmt = $pdo->prepare("SELECT id FROM account WHERE account_id = ?");
$stmt->execute([$_SESSION['ACCOUNTID']]);
$sid = $stmt->fetchColumn();

// FETCH GRADES (LEFT JOIN ensures subjects show up even without grades)
$sql = "SELECT 
            sub.code, 
            sub.description, 
            g.quarter, 
            g.grade
        FROM enrollments e
        JOIN sections sec ON e.section_id = sec.id
        JOIN subjects sub ON sec.code = sub.code
        LEFT JOIN grades g ON g.student_id = e.student_id AND g.section_id = sec.id
        WHERE e.student_id = ?
        ORDER BY sub.code ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$sid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ORGANIZE DATA (Group by Subject)
$grades_map = [];
foreach($rows as $r) {
    $code = $r['code'];
    if(!isset($grades_map[$code])) {
        $grades_map[$code] = [
            'name' => $r['description'],
            'q1' => '-', 'q2' => '-', 'q3' => '-', 'q4' => '-', 'final' => '-', 'remarks' => ''
        ];
    }
    if(isset($r['quarter']) && $r['grade'] != '') {
        $grades_map[$code]['q' . $r['quarter']] = $r['grade'];
    }
}

// CALCULATE AVERAGES
foreach($grades_map as $code => $d) {
    if(is_numeric($d['q1']) && is_numeric($d['q2']) && is_numeric($d['q3']) && is_numeric($d['q4'])) {
        $avg = ($d['q1'] + $d['q2'] + $d['q3'] + $d['q4']) / 4;
        $grades_map[$code]['final'] = number_format($avg, 2);
        $grades_map[$code]['remarks'] = ($avg >= 75) ? 'PASSED' : 'FAILED';
    }
}
?>

<div class="grades-card">
    <h2 class="grades-title">My Grades</h2>
    
    <?php if(empty($grades_map)): ?>
        <div style="text-align:center; padding:30px; color:#666;">No grades records found.</div>
    <?php else: ?>
        <table class="grades-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th style="text-align:center;">Q1</th>
                    <th style="text-align:center;">Q2</th>
                    <th style="text-align:center;">Q3</th>
                    <th style="text-align:center;">Q4</th>
                    <th style="text-align:center; background:#001f52;">Final</th>
                    <th style="text-align:center; background:#001f52;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($grades_map as $code => $data): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($code); ?></strong><br>
                        <small><?php echo htmlspecialchars($data['name']); ?></small>
                    </td>
                    <td><?php echo $data['q1']; ?></td>
                    <td><?php echo $data['q2']; ?></td>
                    <td><?php echo $data['q3']; ?></td>
                    <td><?php echo $data['q4']; ?></td>
                    <td style="font-weight:bold;"><?php echo $data['final']; ?></td>
                    <td>
                        <?php if($data['remarks'] == 'PASSED'): ?>
                            <span class="grade-badge grade-excellent">PASSED</span>
                        <?php elseif($data['remarks'] == 'FAILED'): ?>
                            <span class="grade-badge" style="background:#f8d7da; color:#721c24;">FAILED</span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>