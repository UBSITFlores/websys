<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Session Expired."; exit;
}

// Use centralized connection
require_once '../functions/db.php';

// --- HANDLE: ADD ASSESSMENT (Set Tuition) ---
if (isset($_POST['btn_assess'])) {
    $sid = $_POST['hidden_sid'];
    $amount = $_POST['assess_amount'];
    
    // Fetch School Year from centralized $current_sy if available, or query
    // Since we required db.php, $current_sy is available
    global $current_sy; 
    $sy = $current_sy ?? '2025-2026';

    $stmt = $pdo->prepare("INSERT INTO assessments (student_id, total_amount, school_year) VALUES (?, ?, ?)");
    if($stmt->execute([$sid, $amount, $sy])) {
        echo "<script>alert('Fee Assessed Successfully!'); loadBilling();</script>";
    }
    exit; 
}
?>

<style>
/* --- Main Card --- */
.form-card {
    max-width: 1100px;
    margin: 0 auto;
    background: #ffffff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* --- Search Box --- */
.search-box {
    background: #f0f8ff; /* Lightest blue */
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #cce5ff;
}

.search-box label {
    font-weight: 700;
    color: #002D72;
    display: block;
    margin-bottom: 10px;
    font-size: 1rem;
}

.search-flex {
    display: flex;
    gap: 15px;
    align-items: center; 
}

.search-box input {
    flex: 1;
    padding: 0 15px; 
    height: 45px;    
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
}

.search-box input:focus {
    border-color: #002D72;
    box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.1);
}

/* --- SEARCH BUTTON FIX --- */
#btn_bill_search {
    height: 45px; 
    padding: 0 20px;
    font-size: 0.95rem;
    white-space: nowrap;
    color:white;
    background:#002D72;
    border:none;
    border-radius:6px;
}

#bill_check_status {
    margin-top: 10px;
    font-weight: 600;
    font-size: 0.95rem;
}

/* --- Student Header --- */
#billing_dashboard { display: none; }

.student-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e8ed;
}

.student-header h1 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 2rem;
}

.student-header-info {
    background: #eef4fb;
    color: #002D72;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.95rem;
    font-weight: 600;
    display: inline-block;
}

/* --- Money Cards Grid --- */
.money-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 35px;
}

.money-card {
    padding: 25px;
    border-radius: 10px;
    color: white;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.mc-blue { background: linear-gradient(135deg, #002D72, #004099); }
.mc-green { background: linear-gradient(135deg, #198754, #20c997); }
.mc-red { background: linear-gradient(135deg, #dc3545, #f06595); }

.mc-title {
    font-size: 0.85rem;
    opacity: 0.9;
    margin-bottom: 10px;
    display: block;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 600;
}

.mc-val {
    font-size: 2rem;
    font-weight: 700;
    display: block;
}

/* --- Billing Content Layout --- */
.billing-content {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 25px;
}

/* --- Left Panel (Form) --- */
.billing-left {
    border: 1px solid #e1e8ed;
    padding: 25px;
    border-radius: 8px;
    background: #f9fbfd;
}

.billing-left h4 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #002D72;
    font-size: 1.1rem;
    border-bottom: 2px solid #e1e8ed;
    padding-bottom: 10px;
}

.form-group { margin-bottom: 15px; }
.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #555;
    font-size: 0.9rem;
}
.form-group input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    box-sizing: border-box; 
}

/* --- Right Panel (Table) --- */
.billing-right {
    grid-column: span 2 / auto; /* Fallback */
}

.billing-right h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #444;
    font-size: 1.2rem;
}

.history-table-container {
    border: 1px solid #e1e8ed;
    border-radius: 8px;
    overflow: hidden;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
    background: white;
}

.history-table th {
    background: #002D72;
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
}

.history-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    color: #333;
}

.history-table tbody tr:hover { background: #f8f9fa; }

/* --- Buttons --- */
.btn-save {
    background: #002D72;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 700;
    font-size: 1rem;
    transition: background 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.btn-save:hover { background: #004099; }

.btn-save.secondary {
    background: #6c757d;
    padding: 10px 20px;
    font-size: 0.9rem;
}
.btn-save.secondary:hover { background: #5a6268; }

.signature-line {
    margin-top: 80px;
    width: 250px;
    border-top: 2px solid black;
    padding-top: 10px;
    text-align: center;
    font-weight: bold;
    display: none; /* Only for old print method, can keep hidden */
}

@media (max-width: 768px) {
    .money-grid, .billing-content, .search-flex { grid-template-columns: 1fr; flex-direction: column; }
    .billing-right { grid-column: auto; }
}
</style>

<div class="form-card">
    
    <div class="search-box">
        <label>Search Student ID:</label>
        <div class="search-flex">
            <input type="text" id="bill_search" 
                   placeholder="e.g. 20260001" 
                   oninput="checkBillingID()">
            
            <button onclick="loadBilling()" id="btn_bill_search">View Ledger</button>
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
                <button onclick="openPDF()" class="btn-save secondary">🖨️ Print Statement (PDF)</button>
            </div>
        </div>

        <div class="money-grid">
            <div class="money-card mc-blue">
                <span class="mc-title">Total Assessment</span>
                <span class="mc-val">₱ <span id="val_total">0.00</span></span>
            </div>
            <div class="money-card mc-green">
                <span class="mc-title">Total Paid</span>
                <span class="mc-val">₱ <span id="val_paid">0.00</span></span>
            </div>
            <div class="money-card mc-red">
                <span class="mc-title">Remaining Balance</span>
                <span class="mc-val">₱ <span id="val_balance">0.00</span></span>
            </div>
        </div>

        <div class="billing-content">
            
            <div class="billing-left">
                <h4>+ Add Tuition / Fee</h4>
                <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'billing.php');">
                    <input type="hidden" name="hidden_sid" class="target_sid">
                    
                    <div class="form-group">
                        <label>Amount (PHP)</label>
                        <input type="number" name="assess_amount" placeholder="0.00" step="0.01" required>
                    </div>
                    
                    <button type="submit" name="btn_assess" class="btn-save" style="width:100%">Save Fee</button>
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
            </div>

        </div>
    </div>
</div>