<?php
$host = "localhost"; $user = "root"; $pass = ""; $db = "portal";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch(PDOException $e) { echo "ERROR"; exit; }

if(isset($_POST['student_id'])) {
    $aid = $_POST['student_id'];

    // 1. Get Student Info
    $stmt = $pdo->prepare("SELECT a.id, a.fname, a.lname, s.track 
                           FROM account a 
                           JOIN students s ON s.student_id = a.id 
                           WHERE a.account_id = :aid");
    $stmt->execute([':aid' => $aid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$student) { echo "NOT_FOUND"; exit; }
    $sid = $student['id'];

    // 2. Get Total Assessment (Tuition Fee)
    $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM assessments WHERE student_id = ?");
    $stmt->execute([$sid]);
    $total_fee = $stmt->fetchColumn() ?: 0;

    // 3. Get Total Payments
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE student_id = ?");
    $stmt->execute([$sid]);
    $total_paid = $stmt->fetchColumn() ?: 0;

    // 4. Get Payment History
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY transaction_date DESC");
    $stmt->execute([$sid]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate Balance
    $balance = $total_fee - $total_paid;
    
    // Determine Status Badge
    if($total_fee == 0) {
        $status_badge = "<span class='badge-gray' style='background:#ccc; color:#333; padding:5px 10px; border-radius:4px;'>No Assessment</span>";
    } elseif ($balance <= 0) {
        $status_badge = "<span class='badge-paid' style='background:#198754; color:white; padding:5px 10px; border-radius:4px;'>FULLY PAID</span>";
    } elseif ($total_paid > 0) {
        $status_badge = "<span class='badge-partial' style='background:#ffc107; color:#333; padding:5px 10px; border-radius:4px;'>PARTIALLY PAID</span>";
    } else {
        $status_badge = "<span class='badge-unpaid' style='background:#dc3545; color:white; padding:5px 10px; border-radius:4px;'>NOT PAID</span>";
    }

    // Build History Rows HTML
    $history_html = "";
    if(empty($history)) {
        $history_html = "<tr><td colspan='4' style='text-align:center; color:#888; padding:15px;'>No payment history found.</td></tr>";
    } else {
        foreach($history as $pay) {
            $history_html .= "<tr>
                <td style='padding:10px; border-bottom:1px solid #eee;'>" . $pay['transaction_date'] . "</td>
                <td style='padding:10px; border-bottom:1px solid #eee;'>" . htmlspecialchars($pay['method']) . "<br><small style='color:#888;'>" . htmlspecialchars($pay['reference_no'] ?? '') . "</small></td>
                <td style='padding:10px; border-bottom:1px solid #eee;'>" . htmlspecialchars($pay['purpose']) . "</td>
                <td style='padding:10px; border-bottom:1px solid #eee; font-weight:bold; color:#198754;'>+ ₱" . number_format($pay['amount'], 2) . "</td>
            </tr>";
        }
    }

    // Send data back separated by ||
    echo $student['fname'] . " " . $student['lname'] . "||" . 
         ucfirst($student['track']) . "||" . 
         number_format($total_fee, 2) . "||" . 
         number_format($total_paid, 2) . "||" . 
         number_format($balance, 2) . "||" . 
         $history_html . "||" . 
         $status_badge . "||" . 
         $sid; 
}
?>