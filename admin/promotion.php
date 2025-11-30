<?php
// 1. ERROR REPORTING
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') {
    http_response_code(403); echo "Access Denied."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- HANDLE ACTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_level = $_POST['from_level'];
    $action_type = $_POST['action_type'];
    
    // 1. GET STUDENTS
    $sql = "SELECT st.student_id, a.id as account_pk 
            FROM students st 
            JOIN account a ON st.student_id = a.id 
            WHERE st.grade_level = ? AND a.status = 'Active'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$from_level]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = 0;
    $total = count($students); // Count how many students

    // 2. CHECK ACTION
    if ($action_type == 'promote') {
        
        // MAP LEVELS MANUALLY (Simple If-Else)
        $next_level = "";
        
        // Note: Kinder is removed here because they now Graduate
        if ($from_level == 'Grade 1') { $next_level = 'Grade 2'; }
        else if ($from_level == 'Grade 2') { $next_level = 'Grade 3'; }
        else if ($from_level == 'Grade 3') { $next_level = 'Grade 4'; }
        else if ($from_level == 'Grade 4') { $next_level = 'Grade 5'; }
        else if ($from_level == 'Grade 5') { $next_level = 'Grade 6'; }
        else if ($from_level == 'Grade 6') { $next_level = 'Grade 7'; }
        else if ($from_level == 'Grade 7') { $next_level = 'Grade 8'; }
        else if ($from_level == 'Grade 8') { $next_level = 'Grade 9'; }
        else if ($from_level == 'Grade 9') { $next_level = 'Grade 10'; }
        else if ($from_level == 'Grade 10') { $next_level = 'Grade 11'; }
        else if ($from_level == 'Grade 11') { $next_level = 'Grade 12'; }

        if ($next_level != "") {
            $upd = $pdo->prepare("UPDATE students SET grade_level = ? WHERE student_id = ?");
            
            // SIMPLE FOR LOOP
            for ($i = 0; $i < $total; $i++) {
                $pk = $students[$i]['account_pk'];
                $upd->execute([$next_level, $pk]);
                $count = $count + 1;
            }
            echo "<script>alert('Success! Promoted $count students to $next_level.'); loadZone('promotion.php');</script>";
        } else {
            echo "<script>alert('Error: No next level found for $from_level');</script>";
        }

    } else if ($action_type == 'graduate') {
        
        // GRADUATE LOGIC
        $upd = $pdo->prepare("UPDATE account SET status = 'Graduated', last_active_date = CURDATE() WHERE id = ?");
        
        // SIMPLE FOR LOOP
        for ($i = 0; $i < $total; $i++) {
            $pk = $students[$i]['account_pk'];
            $upd->execute([$pk]);
            $count = $count + 1;
        }
        echo "<script>alert('Success! $count students graduated.'); loadZone('promotion.php');</script>";
    }
    exit;
}
?>

<div class="form-card" style="max-width: 800px;">
    <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">End of School Year Manager</h2>
    
    <div style="background:#e8f5e9; border:1px solid #c3e6cb; padding:15px; border-radius:5px; margin-bottom:20px; color:#0f5132;">
        <strong>📝 Instructions:</strong>
        <ul style="margin:5px 0 0 20px;">
            <li><strong>Promote:</strong> Moves students to the next level.</li>
            <li><strong>Graduate:</strong> For <strong>Kinder</strong> and <strong>Grade 12</strong>. Marks them as Graduated.</li>
        </ul>
    </div>

    <div style="border:1px solid #ddd; padding:20px; border-radius:8px; background:#fff;">
        <h3 style="margin-top:0; color:#002D72;">Select Batch to Process</h3>
        
        <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'promotion.php');">
            
            <div style="margin-bottom:15px;">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Current Grade Level</label>
                <select name="from_level" id="batch_level" onchange="updateActionUI()" required style="padding:10px; width:100%; border:1px solid #ccc; border-radius:4px;">
                    <option value="">-- Select Level --</option>
                    <option value="Kinder">Kindergarten (Graduating)</option>
                    <option value="Grade 7">Grade 7</option>
                    <option value="Grade 8">Grade 8</option>
                    <option value="Grade 9">Grade 9</option>
                    <option value="Grade 10">Grade 10</option>
                    <option value="Grade 11">Grade 11</option>
                    <option value="Grade 12">Grade 12 (Graduating)</option>
                </select>
            </div>

            <div id="action_preview" style="margin-bottom:20px; padding:15px; background:#f8f9fa; border-left:4px solid #002D72; display:none;">
                <strong>Action:</strong> <span id="action_text">Please select a level.</span>
                <input type="hidden" name="action_type" id="real_action">
            </div>

            <button type="submit" id="btn_process" class="btn-save" style="width:100%;" disabled>Process Batch</button>
        </form>
    </div>
</div>