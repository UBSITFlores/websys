<?php
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403);
    echo "Session Expired.";
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// HANDLE SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_display_id = trim($_POST['student_id']); 
    $section_pk         = $_POST['section_id'];

    $stmt = $pdo->prepare("SELECT id FROM account WHERE account_id = :aid");
    $stmt->execute([':aid' => $student_display_id]);
    $student_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$student_row) {
        echo "<script>alert('Error: Student not found.');</script>";
    } else {
        $student_pk = $student_row['id'];
        $check = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = :sid AND section_id = :sec");
        $check->execute([':sid' => $student_pk, ':sec' => $section_pk]);
        
        if($check->rowCount() > 0) {
            echo "<script>alert('Student already in this class.');</script>";
        } else {
            $enroll = $pdo->prepare("INSERT INTO enrollments (student_id, section_id, date_enrolled) VALUES (:sid, :sec, CURDATE())");
            if($enroll->execute([':sid' => $student_pk, ':sec' => $section_pk])) {
                echo "<script>alert('Enrollment Successful.'); loadZone('enroll_subject.php');</script>";
            }
        }
    }
    exit; 
}

$sections = $pdo->query("SELECT * FROM sections ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);
$subjects = [];
foreach($sections as $s) { $subjects[$s['code']] = $s['description']; }
?>

<div class="form-card" style="max-width: 800px;">
    <h2>Enroll Student to Subject</h2>
    
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'enroll_subject.php');">
        
        <div class="form-group">
            <label>Student ID Number</label>
            <input type="text" id="stu_id_input" name="student_id" 
                   placeholder="e.g. 20250001" 
                   autocomplete="off" 
                   oninput="verifyStudent()">
            
            <div id="check_result" style="display:none; margin-top:5px; padding:10px; font-weight:bold; border-radius:4px;"></div>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>1. Student Track</label>
                <input type="text" id="display_track" disabled placeholder="Waiting for ID..." style="background:#f0f0f0;">
                <input type="hidden" id="filter_track">
            </div>

            <div class="form-group">
                <label>2. Year Level</label>
                <select id="filter_year" onchange="filterClasses()">
                    <option value="">-- Select ID First --</option>
                    
                    <option value="Kinder" class="opt-level opt-kinder" style="display:none;">Kindergarten</option>
                    
                    <option value="Grade 7" class="opt-level opt-jhs" style="display:none;">Grade 7</option>
                    <option value="Grade 8" class="opt-level opt-jhs" style="display:none;">Grade 8</option>
                    <option value="Grade 9" class="opt-level opt-jhs" style="display:none;">Grade 9</option>
                    <option value="Grade 10" class="opt-level opt-jhs" style="display:none;">Grade 10</option>
                    
                    <option value="Grade 11" class="opt-level opt-shs" style="display:none;">Grade 11</option>
                    <option value="Grade 12" class="opt-level opt-shs" style="display:none;">Grade 12</option>
                </select>
            </div>
        </div>

        <div class="form-group" id="strand_container" style="display:none;">
            <label>2.5. SHS Strand</label>
            <select id="filter_strand" onchange="filterClasses()" style="background:#fff3cd; border-color:#ffeeba; color:#856404;">
                <option value="">-- Select Strand --</option>
                <option value="STEM">STEM (Science, Tech, Eng, Math)</option>
                <option value="ABM">ABM (Accountancy, Business, Mgt)</option>
                <option value="HUMSS">HUMSS (Humanities & Social Sciences)</option>
                <option value="GAS">GAS (General Academic Strand)</option>
                <option value="TVL">TVL (Technical-Vocational-Livelihood)</option>
            </select>
        </div>

        <div class="form-group">
            <label>3. Subject</label>
            <select id="filter_subject" onchange="filterClasses()">
                <option value="">-- All Subjects --</option>
                <?php foreach(array_unique($subjects) as $code => $desc): ?>
                    <option value="<?php echo $code; ?>"><?php echo "$code - $desc"; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="background: #e6f7ff; padding: 15px; border: 1px solid #b3e0ff; border-radius:5px;">
            <label style="color:#0056b3;">4. Select Class Section</label>
            <select name="section_id" id="final_section" required disabled>
                <option value="">-- Verify Student ID First --</option>
                <?php foreach($sections as $sec): ?>
                    <option 
                        value="<?php echo $sec['id']; ?>"
                        class="sec-opt"
                        data-track="<?php echo $sec['track']; ?>"
                        data-year="<?php echo $sec['year_level']; ?>"
                        data-strand="<?php echo $sec['strand'] ?? ''; // Handle null strand ?>" 
                        data-code="<?php echo $sec['code']; ?>"
                        style="display:none;" 
                    >
                        <?php echo "Section: " . $sec['section'] . " (" . $sec['schedule_time'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" id="btn_enroll" class="btn-save" disabled style="opacity:0.6; cursor:not-allowed;">
            Enroll Student
        </button>
    </form>
</div>