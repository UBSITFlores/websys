<?php
require_once('../fpdf/fpdf.php'); // Adjust path if needed
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    die("Access Denied.");
}

// Get parameters
$section_name = $_GET['section'] ?? '';
$subject_code = $_GET['code'] ?? '';
$month = $_GET['month'] ?? date('Y-m');

if (!$section_name || !$subject_code) {
    die("Missing parameters.");
}

// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get section ID and info
$stmt = $pdo->prepare("SELECT * FROM sections WHERE section = ? AND code = ? LIMIT 1");
$stmt->execute([$section_name, $subject_code]);
$subject_info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject_info) {
    die("Section not found.");
}
$sid = $subject_info['id'];

// Get students
$stmt = $pdo->prepare("SELECT a.id, a.lname, a.fname, a.mname FROM enrollments e JOIN account a ON e.student_id=a.id WHERE e.section_id=? ORDER BY a.lname, a.fname");
$stmt->execute([$sid]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get days in month
$year = substr($month, 0, 4);
$mon = substr($month, 5, 2);
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $mon, $year);

// Get attendance data
$att_data = [];
$stmt = $pdo->prepare("SELECT * FROM attendance_daily WHERE section_id=? AND month_year=?");
$stmt->execute([$sid, $month]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $att_data[$row['student_id']] = $row;
}

// Create PDF in LANDSCAPE
class PDF extends FPDF {
    private $subject_info;
    private $month;
    
    function __construct($orientation='L', $unit='mm', $size='Letter') {
        parent::__construct($orientation, $unit, $size);
    }
    
    function setSubjectInfo($info, $month) {
        $this->subject_info = $info;
        $this->month = $month;
    }
    
    function Header() {
        // Title
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(0, 45, 114);
        $this->Cell(0, 6, 'ATTENDANCE RECORD', 0, 1, 'C');
        
        // Subject info
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0);
        $this->Cell(0, 5, iconv('UTF-8', 'windows-1252', $this->subject_info['code'] . ' - ' . $this->subject_info['description']), 0, 1, 'C');
        $this->Cell(0, 5, iconv('UTF-8', 'windows-1252', 'Section: ' . $this->subject_info['section'] . ' | ' . date('F Y', strtotime($this->month . '-01'))), 0, 1, 'C');
        $this->Ln(3);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | Legend: P=Present, A=Absent, L=Late, E=Excused', 0, 0, 'C');
    }
}

// Initialize PDF - LANDSCAPE MODE
$pdf = new PDF('L', 'mm', 'Letter'); // 'L' = Landscape
$pdf->setSubjectInfo($subject_info, $month);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 7);

// Calculate column widths
$name_width = 45;
$day_width = ($pdf->GetPageWidth() - 30 - $name_width - 10) / $days_in_month; // 10 for P/A columns
if ($day_width < 3) $day_width = 3;

// Table Header
$pdf->SetFillColor(0, 45, 114);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 7);

// Student Name header
$pdf->Cell($name_width, 6, 'Student Name', 1, 0, 'C', true);

// Day numbers
for ($d = 1; $d <= $days_in_month; $d++) {
    $pdf->Cell($day_width, 6, $d, 1, 0, 'C', true);
}

// P/A columns
$pdf->SetFillColor(25, 135, 84); // Green for P
$pdf->Cell(5, 6, 'P', 1, 0, 'C', true);
$pdf->SetFillColor(220, 53, 69); // Red for A
$pdf->Cell(5, 6, 'A', 1, 1, 'C', true);

// Table Body
$pdf->SetFont('Arial', '', 6);
$pdf->SetTextColor(0);

foreach ($students as $student) {
    $student_id = $student['id'];
    $name = $student['lname'] . ', ' . $student['fname'];
    if (!empty($student['mname'])) {
        $name .= ' ' . substr($student['mname'], 0, 1) . '.';
    }
    
    // Calculate P and A counts
    $p_count = 0;
    $a_count = 0;
    
    for ($d = 1; $d <= $days_in_month; $d++) {
        $status = $att_data[$student_id]["day_$d"] ?? '';
        if ($status === 'P') $p_count++;
        if ($status === 'A') $a_count++;
    }
    
    // Name cell
    $pdf->SetFillColor(250, 250, 250);
    $pdf->SetTextColor(0);
    $pdf->Cell($name_width, 5, iconv('UTF-8', 'windows-1252', substr($name, 0, 30)), 1, 0, 'L', true);
    
    // Day cells
    for ($d = 1; $d <= $days_in_month; $d++) {
        $status = $att_data[$student_id]["day_$d"] ?? '';
        
        // Color coding
        if ($status === 'P') {
            $pdf->SetFillColor(220, 255, 220); // Light green
            $pdf->SetTextColor(0, 100, 0); // Dark green
        } elseif ($status === 'A') {
            $pdf->SetFillColor(255, 220, 220); // Light red
            $pdf->SetTextColor(200, 0, 0); // Dark red
        } elseif ($status === 'L') {
            $pdf->SetFillColor(255, 240, 200); // Light orange
            $pdf->SetTextColor(150, 100, 0); // Dark orange
        } elseif ($status === 'E') {
            $pdf->SetFillColor(220, 220, 255); // Light blue
            $pdf->SetTextColor(0, 0, 150); // Dark blue
        } else {
            $pdf->SetFillColor(255, 255, 255); // White
            $pdf->SetTextColor(200); // Gray
        }
        
        $pdf->Cell($day_width, 5, $status, 1, 0, 'C', true);
    }
    
    // Reset colors for totals
    $pdf->SetFillColor(220, 255, 220); // Light green
    $pdf->SetTextColor(0, 100, 0);
    $pdf->Cell(5, 5, $p_count, 1, 0, 'C', true);
    
    $pdf->SetFillColor(255, 220, 220); // Light red
    $pdf->SetTextColor(200, 0, 0);
    $pdf->Cell(5, 5, $a_count, 1, 1, 'C', true);
    
    $pdf->SetTextColor(0);
}

// Output PDF
$filename = 'Attendance_' . $subject_code . '_' . $section_name . '_' . $month . '.pdf';
$pdf->Output('D', $filename); // 'D' = Force download
?>