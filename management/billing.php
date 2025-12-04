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
/* --- INLINE STYLES FOR AJAX LOADED CONTENT --- */
.form-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    max-width: 1000px;
    margin: 0 auto;
}

.print-header {
    display: none;
    text-align: center;
    margin-bottom: 40px;
    border-bottom: 2px solid #002D72;
    padding-bottom: 20px;
}

.print-header h1 {
    margin: 0;
    color: #002D72;
    font-size: 24pt;
}

.print-header p {
    margin: 5px 0 0;
    color: #555;
    font-size: 10pt;
}

.search-box {
    background: #f0f8ff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #cce5ff;
}

.search-box label {
    font-weight: bold;
    color: #002D72;
    display: block;
    margin-bottom: 8px;
}

.search-box > div {
    display: flex;
    gap: 10px;
    align-items: stretch;
}

.search-box input {
    padding: 12px 15px;
    border: 1px solid #aaa;
    border-radius: 6px;
    flex: 1;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    height: 48px;
    box-sizing: border-box;
}

.search-box input:focus {
    border-color: #002D72;
    box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.1);
}

#bill_check_status {
    margin-top: 8px;
    font-weight: bold;
    font-size: 0.9rem;
}

#billing_dashboard {
    display: none;
}

.student-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.student-header h1 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 1.8rem;
}

.student-header-info {
    background: #eee;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.9em;
    color: #555;
    display: inline-block;
}

.student-header-actions {
    text-align: right;
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-end;
}

.money-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 30px;
}

.money-card {
    padding: 20px;
    border-radius: 8px;
    color: white;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.mc-blue {
    background: #002D72;
}

.mc-green {
    background: #198754;
}

.mc-red {
    background: #dc3545;
}

.mc-title {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 8px;
    display: block;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.mc-val {
    font-size: 1.8rem;
    font-weight: bold;
    display: block;
}

.billing-content {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
}

.billing-left {
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.billing-left h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #002D72;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    color: #555;
}

.form-group input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-group input:focus {
    border-color: #002D72;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.1);
}

.billing-right {
    grid-column: span 2 / auto;
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.billing-right h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #555;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

.history-table-container {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    background: white;
}

.history-table thead tr {
    background: #002D72;
    color: white;
    text-align: left;
}

.history-table th {
    padding: 12px;
    font-weight: 600;
    border: 1px solid #001f52;
}

.history-table td {
    padding: 12px;
    border: 1px solid #ddd;
}

.history-table tbody tr:hover {
    background: #f8f9fa;
}

.signature-line {
    display: none;
    margin-top: 80px;
    width: 250px;
    border-top: 2px solid black;
    padding-top: 10px;
    text-align: center;
    font-weight: bold;
}

.signature-line span {
    font-weight: normal;
    font-size: 0.8rem;
    color: #666;
}

.btn-save {
    background: #002D72;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: background 0.2s, transform 0.1s;
    height: 48px;
    white-space: nowrap;
}

.btn-save:hover {
    background: #004099;
    transform: translateY(-1px);
}

.btn-save:active {
    transform: translateY(0);
}

.btn-save.secondary {
    background: #6c757d;
}

.btn-save.secondary:hover {
    background: #5a6268;
}

@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .form-card {
        box-shadow: none !important;
        max-width: 100% !important;
        padding: 20px !important;
    }

    .print-header {
        display: block !important;
    }

    .billing-content {
        display: block !important;
    }

    .billing-left {
        display: none !important;
    }

    .billing-right {
        border: none !important;
        padding: 0 !important;
        box-shadow: none !important;
    }

    .money-grid {
        display: flex;
        justify-content: space-between;
        border: 1px solid #000;
        padding: 10px;
        margin-bottom: 20px;
        page-break-inside: avoid;
    }

    .money-card {
        background: white !important;
        color: black !important;
        border: none;
        box-shadow: none;
        text-align: left;
    }

    .mc-title {
        color: black !important;
    }

    .mc-val {
        color: black !important;
    }

    .history-table {
        border: 1px solid #000;
    }

    .history-table th {
        background: #e0e0e0 !important;
        color: black !important;
        border: 1px solid #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .history-table td {
        border: 1px solid #000;
        color: black !important;
    }

    .signature-line {
        display: block !important;
    }
}

@media (max-width: 768px) {
    .money-grid {
        grid-template-columns: 1fr;
    }

    .billing-content {
        grid-template-columns: 1fr;
    }

    .billing-right {
        grid-column: auto;
    }

    .student-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .student-header-actions {
        width: 100%;
        align-items: stretch;
    }

    .btn-save {
        width: 100%;
    }

    .search-box > div {
        flex-direction: column;
    }

    .history-table {
        font-size: 0.75rem;
    }

    .history-table th,
    .history-table td {
        padding: 8px 6px;
    }
}
</style>

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
            
            <div class="billing-left no-print">
                <h4>+ Add Tuition / Fee</h4>
                
                <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'billing.php');">
                    <input type="hidden" name="hidden_sid" class="target_sid">
                    
                    <div class="form-group">
                        <label>Amount (PHP)</label>
                        <input type="number" name="assess_amount" placeholder="0.00" step="0.01" required>
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