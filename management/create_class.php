<?php
session_start();

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403);
    echo "Session Expired.";
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $track = $_POST['track'];
    $year  = $_POST['year_level'];
    $code  = trim($_POST['code']);
    $desc  = trim($_POST['description']);
    $sec   = trim($_POST['section']);
    $time  = trim($_POST['schedule_time']);
    $room  = trim($_POST['room']);
    $sem   = $_POST['semester'];
    $sy    = $_POST['school_year'];
    $iid   = $_POST['instructor_id'] ?: null; 

    $sql = "INSERT INTO sections 
            (track, year_level, code, description, section, schedule_time, room, semester, school_year, instructor_id) 
            VALUES 
            (:track, :year, :code, :desc, :sec, :time, :room, :sem, :sy, :iid)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':track' => $track, ':year' => $year, ':code' => $code, 
            ':desc' => $desc, ':sec' => $sec, ':time' => $time, 
            ':room' => $room, ':sem' => $sem, ':sy' => $sy, ':iid' => $iid
        ]);
        echo "<script>alert('New Class Created Successfully!'); loadZone('create_class.php');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Database Error: " . $e->getMessage() . "');</script>";
    }
    exit;
}

$instructors = $pdo->query("SELECT * FROM account WHERE role = 'instructor' ORDER BY lname ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="form-card" style="max-width: 900px;">
    <h2>Create New Class Offering</h2>
    
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'create_class.php');">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Track</label>
                <select name="track" id="filter_track" onchange="assign_updateYear()" required>
                    <option value="">-- Select Track --</option>
                    <option value="kinder">Kinder</option>
                    <option value="junior high school">Junior High School</option>
                    <option value="senior high school">Senior High School</option>
                </select>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <select name="year_level" id="filter_year" required>
                    <option value="">-- First Select Track --</option>
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

        <h3 style="color:#002D72; font-size:1rem; border-bottom:1px solid #eee; margin-top:10px;">Subject Details</h3>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
            <div class="form-group">
                <label>Subject Code</label>
                <input type="text" name="code" placeholder="e.g. MATH 101" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="e.g. Algebra & Trigonometry" required>
            </div>
        </div>

        <h3 style="color:#002D72; font-size:1rem; border-bottom:1px solid #eee; margin-top:10px;">Section & Schedule</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Section Name</label>
                <input type="text" name="section" placeholder="e.g. Block A" required>
            </div>
            <div class="form-group">
                <label>Room</label>
                <input type="text" name="room" placeholder="e.g. Room 305">
            </div>
            <div class="form-group">
                <label>Time / Days</label>
                <input type="text" name="schedule_time" placeholder="e.g. MWF 8:00-9:30">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Semester</label>
                <select name="semester" required>
                    <option value="1st">1st Semester</option>
                    <option value="2nd">2nd Semester</option>
                    <option value="Summer">Summer</option>
                    <option value="Whole Year">Whole Year (Kinder/JHS)</option>
                </select>
            </div>
            <div class="form-group">
                <label>School Year</label>
                <input type="text" name="school_year" value="<?php echo date('Y') . '-' . (date('Y')+1); ?>">
            </div>
            <div class="form-group">
                <label>Instructor</label>
                <select name="instructor_id">
                    <option value="">-- TBA --</option>
                    <?php foreach($instructors as $i): ?>
                        <option value="<?php echo $i['id']; ?>">
                            <?php echo htmlspecialchars($i['fname'] . ' ' . $i['lname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-save" style="margin-top:20px;">Create Class</button>
    </form>
</div>