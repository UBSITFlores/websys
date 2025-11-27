<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    http_response_code(403); echo "Session Expired."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$section_name = $_GET['section'] ?? '';
$subject_code = $_GET['code'] ?? '';
$month = $_GET['month'] ?? date('Y-m'); 

// 1. GET SECTION ID
$stmt = $pdo->prepare("SELECT id FROM sections WHERE section = ? AND code = ? LIMIT 1");
$stmt->execute([$section_name, $subject_code]);
$sec = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$sec) { 
    echo "<div style='padding:20px; color:red;'>Error: Class Section not found.</div>"; 
    exit; 
}
$sid = $sec['id'];

// 2. HANDLE SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month_save = $_POST['month_key'];
    
    $sql = "INSERT INTO attendance_daily (student_id, section_id, month_year, 
            day_1, day_2, day_3, day_4, day_5, day_6, day_7, day_8, day_9, day_10,
            day_11, day_12, day_13, day_14, day_15, day_16, day_17, day_18, day_19, day_20,
            day_21, day_22, day_23, day_24, day_25, day_26, day_27, day_28, day_29, day_30, day_31) 
            VALUES (:uid, :sec, :mo, 
            :d1, :d2, :d3, :d4, :d5, :d6, :d7, :d8, :d9, :d10,
            :d11, :d12, :d13, :d14, :d15, :d16, :d17, :d18, :d19, :d20,
            :d21, :d22, :d23, :d24, :d25, :d26, :d27, :d28, :d29, :d30, :d31)
            ON DUPLICATE KEY UPDATE 
            day_1=:d1, day_2=:d2, day_3=:d3, day_4=:d4, day_5=:d5, day_6=:d6, day_7=:d7, day_8=:d8, day_9=:d9, day_10=:d10,
            day_11=:d11, day_12=:d12, day_13=:d13, day_14=:d14, day_15=:d15, day_16=:d16, day_17=:d17, day_18=:d18, day_19=:d19, day_20=:d20,
            day_21=:d21, day_22=:d22, day_23=:d23, day_24=:d24, day_25=:d25, day_26=:d26, day_27=:d27, day_28=:d28, day_29=:d29, day_30=:d30, day_31=:d31";
    
    $stmt = $pdo->prepare($sql);

    if(isset($_POST['att'])) {
        foreach($_POST['att'] as $uid => $days) {
            $params = [':uid' => $uid, ':sec' => $sid, ':mo' => $month_save];
            for($i=1; $i<=31; $i++) {
                $params[":d$i"] = empty($days[$i]) ? null : strtoupper($days[$i]);
            }
            $stmt->execute($params);
        }
    }
    echo "SAVED"; exit;
}

// 3. FETCH STUDENTS
$students = $pdo->prepare("SELECT a.id, a.lname, a.fname FROM enrollments e JOIN account a ON e.student_id=a.id WHERE e.section_id=? ORDER BY a.lname");
$students->execute([$sid]);
$list = $students->fetchAll(PDO::FETCH_ASSOC);

// 4. FETCH ATTENDANCE DATA
$att_data = [];
$stmt = $pdo->prepare("SELECT * FROM attendance_daily WHERE section_id=? AND month_year=?");
$stmt->execute([$sid, $month]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $att_data[$row['student_id']] = $row;
}

// 5. CALCULATE DAYS
$days_in_month = date('t', strtotime($month));
?>

<style>
    /* --- WEB VIEW --- */
    .att-container { background: #fff; padding: 20px; border-radius: 8px; overflow-x: auto; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .att-table { border-collapse: collapse; font-size: 0.8rem; width: 100%; min-width: max-content; }
    .att-table th, .att-table td { border: 1px solid #ddd; text-align: center; padding: 4px 2px; }
    .att-table th { background: #002D72; color: white; height: 30px; min-width: 25px; }
    
    .att-name { 
        text-align: left; padding: 5px 10px !important; min-width: 150px; font-weight: bold; 
        position: sticky; left: 0; background: #fff; z-index: 2; 
    }
    
    .att-input { 
        width: 25px; height: 25px; border: none; text-align: center; text-transform: uppercase; 
        font-weight: bold; font-size: 0.85rem; cursor: pointer;
    }
    .att-input:focus { background: #eef2ff; outline: none; }
    
    .att-input[value="A"] { color: red; background: #ffe6e6; }
    .att-input[value="P"] { color: green; }
    .att-input[value="L"] { color: orange; }

    .btn-save { background: #198754; color: white; padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; }
    .month-sel { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }

    /* --- PRINT STYLES --- */
    @media print {
        @page { size: landscape; margin: 0.5cm; }
        
        /* Hide UI */
        .no-print, .sidebar-right, .header, .btn-save, .month-sel, #save_msg { 
            display: none !important; 
        }

        /* Reset Layout */
        body, .content-zone, .att-container, .container { 
            background: white !important; 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            height: auto !important;
            box-shadow: none !important; 
            display: block !important; 
            zoom: 85%; /* Scale down to fit */
        }

        /* Table Styling */
        .att-table { 
            width: 100% !important; 
            border: 2px solid black; 
            font-size: 9pt; 
            page-break-inside: avoid;
        }
        .att-table th { 
            background-color: #eee !important; 
            color: black !important; 
            border: 1px solid black !important; 
            -webkit-print-color-adjust: exact; 
        }
        .att-table td { 
            border: 1px solid black !important; 
        }

        /* Name Column */
        .att-name { 
            position: static !important; 
            white-space: nowrap;
            border-right: 2px solid black !important;
        }
        
        /* Inputs */
        .att-input { 
            color: black !important; 
            background: transparent !important; 
            border: none !important;
            padding: 0 !important;
            height: auto !important;
        }

        /* Colors */
        .att-input[value="A"] { color: red !important; font-weight: bold; -webkit-print-color-adjust: exact; }
        .att-input[value="P"] { color: green !important; font-weight: bold; -webkit-print-color-adjust: exact; }
    }
</style>

<div class="att-container">
    <div class="no-print" style="display:flex; justify-content:space-between; margin-bottom:15px;">
        <div>
            <h2 style="margin:0; color:#002D72;">Attendance Sheet</h2>
            <p style="margin:0; color:#666;"><?php echo htmlspecialchars($section_name . ' | ' . $subject_code); ?></p>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()" style="padding:8px 15px; background:#6c757d; color:white; border:none; border-radius:4px; cursor:pointer;">Print / PDF</button>
            <input type="month" id="month_picker" value="<?php echo $month; ?>" class="month-sel" onchange="changeMonth()">
            <button onclick="loadZone('grading-sheet-ajax.php', this)" style="padding:8px 15px; cursor:pointer; border:1px solid #ccc; background:#fff; border-radius:4px;">Back</button>
        </div>
    </div>

    <input type="hidden" id="att_sec_name" value="<?php echo htmlspecialchars($section_name); ?>">
    <input type="hidden" id="att_sub_code" value="<?php echo htmlspecialchars($subject_code); ?>">

    <form id="attForm" onsubmit="event.preventDefault(); saveAttendance();">
        <input type="hidden" name="month_key" value="<?php echo $month; ?>">
        
        <?php if(empty($list)): ?>
            <div style="text-align:center; padding:30px; color:#999;">No students enrolled.</div>
        <?php else: ?>
            <table class="att-table">
                <thead>
                    <tr>
                        <th class="att-name">Student Name</th>
                        <?php for($i=1; $i<=$days_in_month; $i++): ?>
                            <th><?php echo $i; ?></th>
                        <?php endfor; ?>
                        <th style="background:#444;">P</th>
                        <th style="background:#444;">A</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($list as $stu): 
                        $uid = $stu['id'];
                        $p_count = 0; $a_count = 0;
                    ?>
                    <tr>
                        <td class="att-name"><?php echo htmlspecialchars($stu['lname'] . ', ' . $stu['fname']); ?></td>
                        <?php for($d=1; $d<=$days_in_month; $d++): 
                            $val = $att_data[$uid]["day_$d"] ?? '';
                            if($val == 'P') $p_count++;
                            if($val == 'A') $a_count++;
                        ?>
                        <td>
                            <input type="text" class="att-input" maxlength="1" 
                                   name="att[<?php echo $uid; ?>][<?php echo $d; ?>]" 
                                   value="<?php echo $val; ?>"
                                   oninput="updateCounts(this)"> </td>
                        <?php endfor; ?>
                        
                        <td class="count-p" style="background:#f9f9f9; font-weight:bold;"><?php echo $p_count; ?></td>
                        <td class="count-a" style="background:#f9f9f9; font-weight:bold; color:red;"><?php echo $a_count; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="no-print" style="margin-top:20px; text-align:right;">
                <span id="save_msg" style="margin-right:10px; font-weight:bold;"></span>
                <button type="submit" class="btn-save">Save Attendance</button>
            </div>
        <?php endif; ?>
    </form>
</div>