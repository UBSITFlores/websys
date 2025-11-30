<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') { http_response_code(403); exit; }

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- HANDLE SAVE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("SELECT code, description FROM subjects WHERE id = ?");
        $stmt->execute([$_POST['subject_id']]);
        $subj = $stmt->fetch();

        $stmt2 = $pdo->prepare("SELECT section_name FROM section_list WHERE id = ?");
        $stmt2->execute([$_POST['section_id']]);
        $sec_name = $stmt->fetchColumn();

        if (!$subj || !$sec_name) throw new Exception("Invalid Data");

        $sql = "INSERT INTO sections 
                (track, year_level, code, description, section, schedule_time, room, semester, school_year, instructor_id) 
                VALUES (:track, :year, :code, :desc, :sec, :time, :room, :sem, :sy, :iid)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':track' => $_POST['track'], ':year' => $_POST['year_level'],
            ':code' => $subj['code'], ':desc' => $subj['description'],
            ':sec' => $sec_name,
            ':time' => trim($_POST['schedule']), ':room' => trim($_POST['room']),
            ':sem' => $_POST['semester'], ':sy' => $_POST['school_year'], ':iid' => $_POST['instructor_id']
        ]);

        echo "<script>alert('Class Created!'); loadZone('class_offering.php');</script>";
    } catch(Exception $e){ echo "<script>alert('Error: ".$e->getMessage()."');</script>"; }
    exit;
}

$instructors   = $pdo->query("SELECT * FROM account WHERE role='instructor' ORDER BY lname ASC")->fetchAll(PDO::FETCH_ASSOC);
$subjects_raw  = $pdo->query("SELECT * FROM subjects ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);
$sections_raw  = $pdo->query("SELECT * FROM section_list ORDER BY section_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="class_offering.css">

<div class="form-card">
    
    <h2 class="title">Class Assignment & Offering</h2>

    <form method="POST" onsubmit="event.preventDefault(); submitForm(this,'class_offering.php');">

        <div class="grid-2 mb">
            <div class="form-group">
                <label>Track</label>
                <select name="track" id="sel_track" onchange="filterSubjects()" required>
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
                <select name="year_level" id="sel_year" onchange="filterSubjects()" required>
                    <option value="">-- Select Track First --</option>
                    <option value="Kinder" class="opt-level opt-kinder" hidden>Kindergarten</option>
                    <option value="Grade 7" class="opt-level opt-jhs" hidden>Grade 7</option>
                    <option value="Grade 8" class="opt-level opt-jhs" hidden>Grade 8</option>
                    <option value="Grade 9" class="opt-level opt-jhs" hidden>Grade 9</option>
                    <option value="Grade 10" class="opt-level opt-jhs" hidden>Grade 10</option>
                    <option value="Grade 11" class="opt-level opt-shs" hidden>Grade 11</option>
                    <option value="Grade 12" class="opt-level opt-shs" hidden>Grade 12</option>
                </select>
            </div>
        </div>

        <div class="panel">
            <div class="form-group">
                <label class="lbl-blue">Subject (Curriculum)</label>
                <select name="subject_id" id="sel_subject" required>
                    <option value="">-- Select Track & Year First --</option>
                    <?php foreach($subjects_raw as $s): ?>
                        <option value="<?= $s['id'] ?>" data-track="<?= $s['track']?>" data-year="<?= $s['year_level']?>"><?= htmlspecialchars($s['code']." - ".$s['description'])?></option>
                    <?php endforeach;?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="lbl-blue">Target Section</label>
                <select name="section_id" id="sel_section" required>
                    <option value="">-- Select Track & Year First --</option>
                    <?php foreach($sections_raw as $sec): ?>
                        <option value="<?= $sec['id']?>" data-track="<?= $sec['track']?>" data-year="<?= $sec['year_level']?>"><?= htmlspecialchars($sec['section_name'])?></option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>

        <div class="grid-2 mt">
            <div class="form-group"><label>Schedule</label><input type="text" name="schedule"></div>
            <div class="form-group"><label>Room</label><input type="text" name="room"></div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>Instructor</label>
                <select name="instructor_id" required>
                    <option value="">-- Select Faculty --</option>
                    <?php foreach($instructors as $i): ?>
                        <option value="<?= $i['id']?>"><?= htmlspecialchars($i['lname'].', '.$i['fname'])?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="form-group">
                <label>Term</label>
                <select name="semester"><option>Whole Year</option><option>1st</option><option>2nd</option><option>Summer</option></select>
            </div>
            <div class="form-group">
                <label>SY</label>
                <input type="text" name="school_year" value="2025-2026">
            </div>
        </div>

        <button type="submit" class="btn-save">Create Class Section</button>
    </form>
</div>
