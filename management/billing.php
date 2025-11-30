<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Session Expired."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- HANDLE: ADD ASSESSMENT (Set Tuition) ---
if (isset($_POST['btn_assess'])) {
    $sid = $_POST['hidden_sid'];
    $amount = $_POST['assess_amount'];
    
    $config = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
    $sy = $config['current_year'] ?? '2025-2026';

    $stmt = $pdo->prepare("INSERT INTO assessments (student_id, total_amount, school_year) VALUES (?, ?, ?)");
    if($stmt->execute([$sid, $amount, $sy])) {
        echo "<script>alert('Fee Assessed Successfully!'); loadBilling();</script>";
    }
    exit; 
}
?>

<div class="form-card">
    
    <div class="print-header">
        <h1>University of Saint Louis</h1>
        <p>OFFICIAL STATEMENT OF ACCOUNT</p>
        <p>Finance Department | School Year 2025-2026</p>
    </div>

    <div class="search-box no-print">
        <label>Search Student ID:</label>
        <div>
            <input type="text" id="bill_search" 
                   placeholder="e.g. 20260001" 
                   oninput="checkBillingID()">
            
            <button onclick="loadBilling()" id="btn_bill_search" class="btn-save">View Ledger</button>
        </div>
        <div id="bill_check_status"></div>
    </div>

    <div id="billing_dashboard">
        
        <div class="student-header">
            <div>
                <h1 id="lbl_name">-</h1>
                <span id="lbl_track" class="student-header-info">-</span>
            </div>
            
            <div class="student-header-actions">
                <div id="lbl_status"></div>
                <button onclick="window.print()" class="btn-save secondary no-print">🖨️ Print Statement</button>
            </div>
        </div>

        <div class="money-grid">
            <div class="money-card mc-blue">
                <span class="mc-title">TOTAL ASSESSMENT</span>
                <span class="mc-val">₱ <span id="val_total">0.00</span></span>
            </div>
            <div class="money-card mc-green">
                <span class="mc-title">TOTAL PAID</span>
                <span class="mc-val">₱ <span id="val_paid">0.00</span></span>
            </div>
            <div class="money-card mc-red">
                <span class="mc-title">REMAINING BALANCE</span>
                <span class="mc-val">₱ <span id="val_balance">0.00</span></span>
            </div>
        </div>

        <div class="billing-content">
            
            <div class="billing-left no-print">
                <h4>+ Add Tuition / Fee</h4>
                
                <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'billing.php');">
                    <input type="hidden" name="hidden_sid" class="target_sid">
                    
                    <div class="form-group">
                        <label>Amount (PHP)</label>
                        <input type="number" name="assess_amount" placeholder="0.00" required>
                    </div>
                    
                    <button type="submit" name="btn_assess" class="btn-save">Save Fee</button>
                </form>
            </div>

            <div class="billing-right"> 
                <h3>Transaction History</h3>
                <div class="history-table-container">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method / Ref</th>
                                <th>Purpose</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="history_table">
                        </tbody>
                    </table>
                </div>

                <div class="signature-line">
                    Cashier / Finance Officer<br>
                    <span>Date Printed: <?php echo date('Y-m-d'); ?></span>
                </div>

            </div>

        </div>
    </div>
</div>