<?php
require_once('../fpdf/fpdf.php'); // Adjust path to your FPDF location
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    die("Access Denied.");
}

$account_id = $_SESSION['ACCOUNTID'] ?? null;

// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get config
$config = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
$current_sy = $config['current_year'] ?? '2025-2026';

// Check archive mode
$show_archive = isset($_GET['view']) && $_GET['view'] == 'archive';

// Fetch sections
if ($show_archive) {
    $sql = "SELECT * FROM sections WHERE instructor_id = (SELECT id FROM account WHERE account_id = ?) AND school_year != ? ORDER BY school_year DESC";
} else {
    $sql = "SELECT * FROM sections WHERE instructor_id = (SELECT id FROM account WHERE account_id = ?) AND school_year = ? ORDER BY code ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$account_id, $current_sy]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create PDF in LANDSCAPE
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 45, 114); // #002D72
        $this->Cell(0, 10, 'My Class Loads - ' . date('Y-m-d'), 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
    
    function ClassCard($code, $description, $section, $track, $year_level, $schedule, $room) {
        // Card border
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(224, 224, 224);
        $this->Rect($this->GetX(), $this->GetY(), 85, 45, 'D');
        
        // Save position
        $x = $this->GetX();
        $y = $this->GetY();
        
        // Code
        $this->SetXY($x + 3, $y + 3);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 45, 114);
        $this->Cell(50, 6, iconv('UTF-8', 'windows-1252', $code), 0, 0);
        
        // Section
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, iconv('UTF-8', 'windows-1252', $section), 0, 1, 'R');
        
        // Description
        $this->SetXY($x + 3, $y + 10);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(85, 85, 85);
        $this->MultiCell(79, 4, iconv('UTF-8', 'windows-1252', substr($description, 0, 60)), 0);
        
        // Track badge
        $this->SetXY($x + 3, $y + 25);
        $this->SetFillColor(231, 241, 255);
        $this->SetTextColor(0, 45, 114);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(20, 5, iconv('UTF-8', 'windows-1252', strtoupper($track)), 0, 0, 'C', true);
        
        // Year badge
        $this->SetX($x + 25);
        $this->SetFillColor(238, 238, 238);
        $this->SetTextColor(85, 85, 85);
        $this->Cell(25, 5, iconv('UTF-8', 'windows-1252', $year_level), 0, 0, 'C', true);
        
        // Schedule and Room
        if ($schedule || $room) {
            $this->SetXY($x + 3, $y + 32);
            $this->SetFont('Arial', '', 7);
            $this->SetTextColor(100, 100, 100);
            $info = ($schedule ? $schedule : '') . ($schedule && $room ? ' | ' : '') . ($room ? $room : '');
            $this->Cell(79, 4, iconv('UTF-8', 'windows-1252', substr($info, 0, 40)), 0, 1);
        }
        
        // Line
        $this->SetDrawColor(238, 238, 238);
        $this->Line($x + 3, $y + 37, $x + 82, $y + 37);
        
        // Buttons text
        $this->SetXY($x + 3, $y + 39);
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(0, 45, 114);
        $this->Cell(0, 4, 'Gradebook | Attendance', 0, 1, 'C');
    }
}

// Initialize PDF - LANDSCAPE MODE
$pdf = new PDF('L', 'mm', 'Letter'); // 'L' = Landscape
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

if (empty($sections)) {
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->SetTextColor(150);
    $pdf->Cell(0, 20, 'No classes found.', 0, 1, 'C');
} else {
    // Calculate grid layout
    $cardsPerRow = 3;
    $cardWidth = 85;
    $cardHeight = 45;
    $spacing = 5;
    $startX = 15;
    $startY = 30;
    
    $x = $startX;
    $y = $startY;
    $count = 0;
    
    foreach ($sections as $row) {
        // Check if we need a new row
        if ($count > 0 && $count % $cardsPerRow == 0) {
            $x = $startX;
            $y += $cardHeight + $spacing;
            
            // Check if we need a new page
            if ($y > 180) {
                $pdf->AddPage();
                $y = $startY;
            }
        }
        
        // Draw card
        $pdf->SetXY($x, $y);
        $pdf->ClassCard(
            $row['code'],
            $row['description'],
            $row['section'],
            $row['track'],
            $row['year_level'],
            $row['schedule_time'] ?? '',
            $row['room'] ?? ''
        );
        
        // Move to next position
        $x += $cardWidth + $spacing;
        $count++;
    }
}

// Output PDF
$filename = 'Class_Loads_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename); // 'D' = Force download
?>