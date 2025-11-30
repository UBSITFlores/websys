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
    
    // Fetch current year from settings
    $config = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
    $sy = $config['current_year'] ?? '2025-2026';

    $stmt = $pdo->prepare("INSERT INTO assessments (student_id, total_amount, school_year) VALUES (?, ?, ?)");
    if($stmt->execute([$sid, $amount, $sy])) {
        // Reload the billing data via JS
        echo "<script>alert('Fee Assessed Successfully!'); loadBilling();</script>";
    }
    exit; // Stop execution to prevent page reload issues
}
?>

<div class="form-card" style="max-width: 1000px;">
    <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Student Billing & Accounts</h2>

    <div style="background:#f0f8ff; padding:20px; border-radius:8px; margin-bottom:20px; border:1px solid #cce5ff;">
        <label style="font-weight:bold; color:#002D72;">Search Student ID:</label>
        <div style="display:flex; gap:10px; margin-top:5px;">
            <input type="text" id="bill_search" 
                   placeholder="e.g. 20260001" 
                   oninput="checkBillingID()"
                   style="padding:10px; border:1px solid #aaa; border-radius:4px; flex:1;">
            
            <button onclick="loadBilling()" id="btn_bill_search" class="btn-save" style="width:auto; padding:10px 30px;">View Ledger</button>
        </div>
        <div id="bill_check_status" style="margin-top:5px; font-weight:bold; font-size:0.9rem;"></div>
    </div>

    <div id="billing_dashboard" style="display:none;">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h1 id="lbl_name" style="margin:0; color:#333; font-size:1.8rem;">-</h1>
                <span id="lbl_track" style="background:#eee; padding:2px 8px; border-radius:4px; font-size:0.9em; color:#555;">-</span>
            </div>
            <div id="lbl_status"></div>
        </div>

        <style>
            .money-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 30px; }
            .money-card { padding: 20px; border-radius: 8px; color: white; text-align: center; }
            .mc-blue { background: #002D72; }
            .mc-green { background: #198754; }
            .mc-red { background: #dc3545; }
            .mc-title { font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px; display:block; letter-spacing: 1px; }
            .mc-val { font-size: 1.8rem; font-weight: bold; }
        </style>

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

        <div style="display:grid; grid-template-columns: 1fr 2fr; gap: 20px;">
            
            <div>
                <div style="border:1px solid #ddd; padding:20px; border-radius:8px; background:#fff;">
                    <h4 style="margin-top:0; color:#002D72; border-bottom:1px solid #eee; padding-bottom:10px;">+ Add Tuition / Fee</h4>
                    
                    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'billing.php');">
                        <input type="hidden" name="hidden_sid" class="target_sid">
                        
                        <div class="form-group">
                            <label>Amount (PHP)</label>
                            <input type="number" name="assess_amount" placeholder="0.00" required>
                        </div>
                        
                        <button type="submit" name="btn_assess" class="btn-save">Save Fee</button>
                    </form>
                </div>
            </div>

            <div>
                <h3 style="margin-top:0; color:#555;">Payment History</h3>
                <div style="border:1px solid #ddd; border-radius:8px; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.9rem; background:white;">
                        <thead>
                            <tr style="background:#f8f9fa; text-align:left; border-bottom:2px solid #ddd;">
                                <th style="padding:12px;">Date</th>
                                <th style="padding:12px;">Method</th>
                                <th style="padding:12px;">Purpose</th>
                                <th style="padding:12px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="history_table">
                            </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>