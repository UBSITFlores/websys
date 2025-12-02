<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') { http_response_code(403); exit; }

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- HANDLE SAVE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Get Subject Info
        $stmt = $pdo->prepare("SELECT code, description FROM subjects WHERE id = ?");
        $stmt->execute([$_POST['subject_id']]);
        $subj = $stmt->fetch();

        // 2. Get Section Name (from ID)
        $stmt2 = $pdo->prepare("SELECT section_name FROM section_list WHERE id = ?");
        $stmt2->execute([$_POST['section_id']]);
        $sec_name = $stmt2->fetchColumn();

        if (!$subj || !$sec_name) throw new Exception("Invalid Data");

        $sql = "INSERT INTO sections 
                (track, year_level, code, description, section, schedule_time, room, semester, school_year, instructor_id) 
                VALUES (:track, :year, :code, :desc, :sec, :time, :room, :sem, :sy, :iid)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':track' => $_POST['track'], 
            ':year'  => $_POST['year_level'],
            ':code'  => $subj['code'], 
            ':desc'  => $subj['description'],
            ':sec'   => $sec_name,
            ':time'  => trim($_POST['schedule']), 
            ':room'  => trim($_POST['room']),
            ':sem'   => $_POST['semester'], 
            ':sy'    => $_POST['school_year'], 
            ':iid'   => $_POST['instructor_id']
        ]);

        echo "<script>alert('Class/Subject Created Successfully!'); loadZone('class_offering.php');</script>";
    } catch (Exception $e) { echo "<script>alert('Error: " . $e->getMessage() . "');</script>"; }
    exit;
}

// FETCH DATA
$instructors = $pdo->query("SELECT * FROM account WHERE role = 'instructor' ORDER BY lname ASC")->fetchAll(PDO::FETCH_ASSOC);
$subjects_raw = $pdo->query("SELECT * FROM subjects ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);
$sections_raw = $pdo->query("SELECT * FROM section_list ORDER BY section_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get School Year from Settings
$config = $pdo->query("SELECT current_year FROM school_settings LIMIT 1")->fetch();
$current_sy = $config['current_year'] ?? '2025-2026';
?>

<div class="form-card" style="max-width: 1000px;">
    <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Class Assignment & Offering</h2>
    
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'class_offering.php');">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div class="form-group">
                <label>Track</label>
                <select name="track" id="sel_track" onchange="resetYear(); filterOptions()" required>
                    <option value="">-- Select Track --</option>
                    <option value="kinder">Kinder</option>
                    <option value="junior high school">Junior High School</option>
                    <option value="STEM">STEM</option>
                    <option value="ABM">ABM</option>
                    <option value="HUMSS">HUMSS</option>
                </select>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <select name="year_level" id="sel_year" onchange="filterOptions()" required>
                    <option value="">-- Select Track First --</option>
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #eef2ff; padding: 15px; border-radius: 8px; border: 1px solid #cce5ff;">
            <div class="form-group">
                <label style="color:#002D72;">Subject (Curriculum)</label>
                <select name="subject_id" id="sel_subject" required>
                    <option value="">-- Select Track & Year First --</option>
                    <?php foreach ($subjects_raw as $s): ?>
                        <option value="<?php echo $s['id']; ?>" 
                                class="sub-opt" 
                                data-track="<?php echo htmlspecialchars($s['track']); ?>" 
                                data-year="<?php echo htmlspecialchars($s['year_level']); ?>" 
                                style="display:none;">
                            <?php echo htmlspecialchars($s['code'] . " - " . $s['description']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label style="color:#002D72;">Target Section</label>
                <select name="section_id" id="sel_section" required>
                    <option value="">-- Select Track & Year First --</option>
                    <?php foreach ($sections_raw as $sec): ?>
                        <option value="<?php echo $sec['id']; ?>" 
                                class="sec-opt" 
                                data-track="<?php echo htmlspecialchars($sec['track']); ?>" 
                                data-year="<?php echo htmlspecialchars($sec['year_level']); ?>" 
                                style="display:none;">
                            <?php echo htmlspecialchars($sec['section_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
            <div class="form-group"><label>Schedule</label><input type="text" name="schedule" placeholder="e.g. MWF 8:00-9:30"></div>
            <div class="form-group"><label>Room</label><input type="text" name="room" placeholder="e.g. Room 304"></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Instructor</label>
                <select name="instructor_id" required>
                    <option value="">-- Select Faculty --</option>
                    <?php foreach($instructors as $i): ?>
                        <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['lname'] . ', ' . $i['fname']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Term / Semester</label>
                <select name="semester" id="sel_term" required>
                    <option value="Whole Year">Whole Year</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>School Year</label>
                <input type="text" name="school_year" value="<?php echo htmlspecialchars($current_sy); ?>" readonly style="background:#eee;">
            </div>
        </div>

        <button type="submit" class="btn-save" style="margin-top: 10px;">Create Class Section</button>
        </form>
    </div>