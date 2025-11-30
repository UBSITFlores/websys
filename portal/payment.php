<?php
require_once '../functions/student_function.php';

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

$studentFunc = new Student();
$student_pk = $studentFunc->getStudentId($_SESSION['ACCOUNTID']);

// --- HANDLE PAYMENT SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $amount = $_POST['amount'];
    $ref = $_POST['reference_no'];
    $method = "GCash"; // Hardcoded for testing phase
    $purpose = "Tuition";

    if($studentFunc->submitPayment($student_pk, $amount, $method, $ref, $purpose)) {
        echo "<script>alert('Payment Successful! Thank you.'); window.location.href='index.php?page=payment';</script>";
    } else {
        echo "<script>alert('Error processing payment.');</script>";
    }
}

// --- FETCH DATA ---
$billing = $studentFunc->getBillingSummary($student_pk);
$history = $studentFunc->getPaymentHistory($student_pk);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Payments</title>
    <link rel="stylesheet" href="payment.css">
</head>
<body>

    <h2 style="color:#002D72; margin-bottom:20px;">Student Accounts & Billing</h2>

    <div class="billing-grid">
        <div class="bill-card bc-blue">
            <span class="bc-label">Total Assessment</span>
            <div class="bc-amount">₱ <?php echo number_format($billing['total_fee'], 2); ?></div>
        </div>
        <div class="bill-card bc-green">
            <span class="bc-label">Total Paid</span>
            <div class="bc-amount">₱ <?php echo number_format($billing['total_paid'], 2); ?></div>
        </div>
        <div class="bill-card bc-red">
            <span class="bc-label">Remaining Balance</span>
            <div class="bc-amount" style="color:#dc3545;">₱ <?php echo number_format($billing['balance'], 2); ?></div>
        </div>
    </div>

    <?php if($billing['balance'] > 0): ?>
    <div class="pay-box">
        <div class="pay-header">
            <span class="gcash-logo">G-Cash Payment</span>
            <p style="margin:5px 0; color:#666; font-size:0.9rem;">Enter your payment details below.</p>
        </div>
        
        <form method="POST">
            <div class="pay-input-group">
                <label>Amount to Pay (PHP)</label>
                <input type="number" name="amount" placeholder="0.00" min="1" max="<?php echo $billing['balance']; ?>" required>
            </div>
            
            <div class="pay-input-group">
                <label>Reference Number</label>
                <input type="text" name="reference_no" placeholder="e.g. 1002348292" required>
            </div>

            <button type="submit" name="pay_now" class="btn-pay">
                PAY NOW ₱
            </button>
        </form>
    </div>
    <?php else: ?>
        <div style="background:#d1e7dd; color:#0f5132; padding:20px; text-align:center; border-radius:10px; margin-bottom:30px;">
            <h3>🎉 Fully Paid!</h3>
            <p>You have no outstanding balance for this school year.</p>
        </div>
    <?php endif; ?>

    <div class="history-container">
        <h3 style="color:#002D72; margin-top:0;">Transaction History</h3>
        <table class="hist-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method / Ref No.</th>
                    <th>Purpose</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($history)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:20px;">No transactions found.</td></tr>
                <?php else: ?>
                    <?php foreach($history as $row): ?>
                    <tr>
                        <td><?php echo date("M d, Y h:i A", strtotime($row['transaction_date'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['method']); ?></strong><br>
                            <span style="font-size:0.85em; color:#888;"><?php echo htmlspecialchars($row['reference_no']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                        <td style="font-weight:bold; color:#198754;">
                            + ₱ <?php echo number_format($row['amount'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>