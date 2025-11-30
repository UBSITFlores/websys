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

<style>
    /* --- SCREEN STYLES --- */
    .money-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 30px; }
    .money-card { padding: 20px; border-radius: 8px; color: white; text-align: center; }
    .mc-blue { background: #002D72; }
    .mc-green { background: #198754; }
    .mc-red { background: #dc3545; }
    .mc-title { font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px; display:block; letter-spacing: 1px; }
    .mc-val { font-size: 1.8rem; font-weight: bold; }
    
    /* --- PRINT ONLY STYLES --- */
    .print-header { display: none; }
    .signature-line { display: none; }

    @media print {
        /* 1. Hide UI Elements */
        .no-print, .sidebar-right, .header, .btn-save, #bill_search, label { 
            display: none !important; 
        }
        
        /* 2. Reset Layout */
        body, .container, .content-zone, .form-card { 
            background: white !important; 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            box-shadow: none !important; 
            display: block !important;
            max-width: 100% !important;
        }

        /* 3. Show Letterhead */
        .print-header {
            display: block;
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #002D72;
            padding-bottom: 20px;
        }
        .print-header h1 { margin: 0; color: #002D72; font-size: 24pt; }
        .print-header p { margin: 5px 0 0; color: #555; font-size: 12pt; }

        /* 4. Improve Table for Print */
        table { width: 100% !important; border: 1px solid #000; }
        th { background: #eee !important; color: black !important; border: 1px solid #000; }
        td { border: 1px solid #000; color: black !important; }

        /* 5. Improve Cards for Print (Make them simple text boxes) */
        .money-grid { 
            display: flex; 
            justify-content: space-between; 
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 20px;
        }
        .money-card { 
            background: white !important; 
            color: black !important; 
            border: none; 
            box-shadow: none; 
            text-align: left;
        }
        .mc-val { color: black !important; }

        /* 6. Signature Line */
        .signature-line {
            display: block;
            margin-top: 80px;
            width: 250px;
            border-top: 1px solid black;
            text-align: center;
            font-weight: bold;
        }
    }
</style>

<div class="form-card" style="max-width: 1000px;">
    
    <div class="print-header">
        <h1>University of Saint Louis</h1>
        <p>OFFICIAL STATEMENT OF ACCOUNT</p>
        <p style="font-size: 10pt;">Finance Department | School Year 2025-2026</p>
    </div>

    <div class="no-print" style="background:#f0f8ff; padding:20px; border-radius:8px; margin-bottom:20px; border:1px solid #cce5ff;">
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
            
            <div style="text-align:right;">
                <div id="lbl_status"></div>
                <button onclick="window.print()" class="btn-save no-print" style="background:#6c757d; margin-top:5px; font-size:0.9rem;">🖨️ Print Statement</button>
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

        <div style="display:grid; grid-template-columns: 1fr 2fr; gap: 20px;">
            
            <div class="no-print">
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

            <div style="grid-column: span 2 / auto;"> 
                <h3 style="margin-top:0; color:#555;">Transaction History</h3>
                <div style="border:1px solid #ddd; border-radius:8px; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.9rem; background:white;">
                        <thead>
                            <tr style="background:#f8f9fa; text-align:left; border-bottom:2px solid #ddd;">
                                <th style="padding:12px;">Date</th>
                                <th style="padding:12px;">Method / Ref</th>
                                <th style="padding:12px;">Purpose</th>
                                <th style="padding:12px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="history_table">
                            </tbody>
                    </table>
                </div>

                <div class="signature-line">
                    Cashier / Finance Officer<br>
                    <span style="font-weight:normal; font-size:0.8rem;">Date Printed: <?php echo date('Y-m-d'); ?></span>
                </div>

            </div>

        </div>
    </div>
</div>