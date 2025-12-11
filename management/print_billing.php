<?php
// Adjust paths: management -> root -> functions/db.php
require_once '../functions/db.php';
// Adjust paths: management -> root -> fpdf/fpdf.php
require('../fpdf/fpdf.php');

// 1. SECURITY & INPUT CHECK
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    die("Access Denied");
}

if (!isset($_GET['student_id'])) { die("Error: Student ID is missing."); }
$sid_input = $_GET['student_id'];

// 2. FETCH DATA
// A. Get Student Info
$stmt = $pdo->prepare("SELECT s.*, a.account_id, a.fname, a.lname
                       FROM students s
                       JOIN account a ON s.student_id = a.id
                       WHERE a.account_id = ?");
$stmt->execute([$sid_input]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) { die("Error: Student not found."); }

$pk = $student['student_id'];

// B. Get Active School Year
$sy_row = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
$current_sy = $sy_row['current_year'] ?? date('Y').'-'.(date('Y')+1);

// C. Get Enrolled Subjects & Prices
$subjects_list = [];
$sub_sql = "SELECT sub.code, sub.description, sub.price
            FROM enrollments e
            JOIN sections s ON e.section_id = s.id
            JOIN subjects sub ON s.code = sub.code
            WHERE e.student_id = ? AND s.school_year = ?";
$sub_stmt = $pdo->prepare($sub_sql);
$sub_stmt->execute([$pk, $current_sy]);
$subjects_list = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

// D. Get Transactions (History)
$history = [];

// Assessments
$stmt = $pdo->prepare("SELECT * FROM assessments WHERE student_id = ?");
$stmt->execute([$pk]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $date = $row['created_at'] ?? $row['date_assessed'] ?? date('Y-m-d');
    $history[] = ['date'=>$date, 'type'=>'Assessment', 'desc'=>$row['term_mode'], 'amount'=>$row['total_amount'], 'is_payment'=>false];
}

// Payments
$stmt = $pdo->prepare("SELECT * FROM payments WHERE student_id = ?");
$stmt->execute([$pk]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pDate = $row['payment_date'] ?? $row['created_at'] ?? date('Y-m-d');
    $history[] = ['date'=>$pDate, 'type'=>'Payment', 'desc'=>$row['method'].' - Ref: '.($row['reference_no']??'-'), 'amount'=>$row['amount'], 'is_payment'=>true];
}

// Sort by Date
usort($history, function($a, $b) { return strtotime($a['date']) - strtotime($b['date']); });

// Totals
$total_fees = 0; $total_paid = 0;
foreach($history as $h) {
    if($h['is_payment']) $total_paid += $h['amount'];
    else $total_fees += $h['amount'];
}
$balance = $total_fees - $total_paid;

// ==========================================
// 3. PDF GENERATION
// ==========================================

class PDF extends FPDF {
    function Header() {
        // --- LOGO SNIPPET START ---
        // Path is ../assets because we are in management folder
        if(file_exists('../assets/logo.png')) {
            $this->Image('../assets/logo.png', 170, 8, 25);
        }
        // --- LOGO SNIPPET END ---

        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(0, 45, 114);
        // Changed University Name
        $this->Cell(0, 10, 'Saint Louis School of Pacdal, Inc.', 0, 1, 'L');

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Finance Department | Official Statement of Account', 0, 1, 'L');

        // Gold Divider Line Removed
        $this->Ln(15);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i') . ' | Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// --- STUDENT DETAILS ---
$pdf->SetFillColor(245, 247, 250);
$pdf->Rect(10, 35, 190, 25, 'F');
$pdf->SetDrawColor(200, 200, 200);
$pdf->Rect(10, 35, 190, 25, 'D');

$pdf->SetXY(15, 40);
$pdf->SetFont('Arial', 'B', 10); $pdf->SetTextColor(0);
$pdf->Cell(30, 5, 'Student Name:', 0, 0);
$pdf->SetFont('Arial', '', 10);
$name = $student['lname'] . ', ' . $student['fname'];
$pdf->Cell(80, 5, utf8_decode($name), 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 5, 'Student ID:', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 5, $sid_input, 0, 1);

$pdf->SetXY(15, 50);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 5, 'Track / Level:', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(80, 5, ucfirst($student['track']) . ' - ' . $student['grade_level'], 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 5, 'School Year:', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 5, $current_sy, 0, 1);

$pdf->Ln(15);

// --- SUBJECTS BREAKDOWN ---
if(!empty($subjects_list)) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 10, 'CURRENT CHARGES (Subjects & Fees)', 0, 1, 'L');

    // Header
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(0, 45, 114);
    $pdf->SetTextColor(255);
    $pdf->Cell(35, 8, 'CODE', 1, 0, 'L', true);
    $pdf->Cell(125, 8, 'DESCRIPTION', 1, 0, 'L', true);
    $pdf->Cell(30, 8, 'AMOUNT', 1, 1, 'R', true);

    // Body
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', '', 9);
    $total_sub_price = 0;

    foreach($subjects_list as $sub) {
        $pdf->Cell(35, 7, $sub['code'], 1, 0, 'L');
        $pdf->Cell(125, 7, substr($sub['description'], 0, 70), 1, 0, 'L');
        $pdf->Cell(30, 7, number_format($sub['price'], 2), 1, 1, 'R');
        $total_sub_price += $sub['price'];
    }

    // Subtotal
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(160, 7, 'TOTAL SUBJECT FEES', 1, 0, 'R');
    $pdf->Cell(30, 7, number_format($total_sub_price, 2), 1, 1, 'R');

    $pdf->Ln(10);
}

// --- SUMMARY TOTALS ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'FINANCIAL SUMMARY', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255);

// Headers
$pdf->SetFillColor(100, 100, 100); // Gray
$pdf->Cell(63, 10, 'TOTAL ASSESSED', 1, 0, 'C', true);
$pdf->SetFillColor(25, 135, 84); // Green
$pdf->Cell(63, 10, 'TOTAL PAID', 1, 0, 'C', true);
$pdf->SetFillColor(220, 53, 69); // Red
$pdf->Cell(64, 10, 'CURRENT BALANCE', 1, 1, 'C', true);

// Values
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(63, 15, number_format($total_fees, 2), 1, 0, 'C');
$pdf->Cell(63, 15, number_format($total_paid, 2), 1, 0, 'C');
$pdf->Cell(64, 15, number_format($balance, 2), 1, 1, 'C');

$pdf->Ln(15);

// --- HISTORY TABLE ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'PAYMENT & ASSESSMENT HISTORY', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(30, 8, 'DATE', 1, 0, 'C', true);
$pdf->Cell(100, 8, 'DESCRIPTION / PURPOSE', 1, 0, 'L', true);
$pdf->Cell(30, 8, 'TYPE', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'AMOUNT', 1, 1, 'R', true);

$pdf->SetFont('Arial', '', 9);
foreach($history as $h) {
    $date = date('Y-m-d', strtotime($h['date']));
    $desc = substr($h['desc'], 0, 55);
    $amount_txt = number_format($h['amount'], 2);

    $pdf->Cell(30, 8, $date, 1, 0, 'C');
    $pdf->Cell(100, 8, $desc, 1, 0, 'L');
    $pdf->Cell(30, 8, $h['type'], 1, 0, 'C');
    $pdf->Cell(30, 8, $amount_txt, 1, 1, 'R');
}

// --- SIGNATURES ---
$pdf->Ln(25);
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(70, 5, '__________________________', 0, 0, 'C');
$pdf->Cell(30, 5, '', 0, 0);
$pdf->Cell(70, 5, '__________________________', 0, 1, 'C');

$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(70, 5, 'Cashier / Finance Officer', 0, 0, 'C');
$pdf->Cell(30, 5, '', 0, 0);
$pdf->Cell(70, 5, 'Student / Parent Signature', 0, 1, 'C');

$pdf->Output();
?>