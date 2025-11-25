<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403);
    echo "Session Expired.";
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("<div style='color:red; padding:20px;'>DB Error: " . $e->getMessage() . "</div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_id = $_POST['section_id'] ?? '';
    $instructor_id = $_POST['instructor_id'] ?? '';

    if(empty($section_id) || empty($instructor_id)){
        echo "<div style='color:red; padding:20px;'>Error: Missing Section or Instructor ID.</div>";
        exit;
    }

    try {
        $sql = "UPDATE sections SET instructor_id = :iid WHERE id = :sid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':iid' => $instructor_id, ':sid' => $section_id]);

        echo "<div style='background:#d4edda; color:#155724; padding:20px; text-align:center; border-radius:8px;'>
                <h3>✅ Instructor Assigned!</h3>
                <p>The database has been updated.</p>
                <button class='btn-save' onclick=\"loadZone('assign_instructor.php')\">Assign Another</button>
              </div>";

        echo "<script>alert('Instructor assigned successfully!');</script>";

    } catch (PDOException $e) {
        echo "<div style='background:#f8d7da; color:#721c24; padding:20px; text-align:center; border-radius:8px;'>
                <h3>❌ Save Failed</h3>
                <p>Database said: " . htmlspecialchars($e->getMessage()) . "</p>
                <button class='btn-save' onclick=\"loadZone('assign_instructor.php')\">Try Again</button>
              </div>";
    }
    exit;
}

try {
    $instructors = $pdo->query("SELECT * FROM account WHERE role = 'instructor' ORDER BY lname ASC")->fetchAll(PDO::FETCH_ASSOC);
    $sections = $pdo->query("SELECT * FROM sections ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching lists: " . $e->getMessage());
}

$subjects = [];
foreach($sections as $s) {
    $key = $s['code'];
    if(!isset($subjects[$key])) {
        $subjects[$key] = [
            'desc' => $s['description'],
            'track' => $s['track'],
            'year' => $s['year_level']
        ];
    }
}
?>

<div class="form-card" style="max-width: 800px;">
    <h2>Assign Instructor to Class</h2>

    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'assign_instructor.php');">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>1. Select Track</label>
                <select id="filter_track" onchange="assign_updateYear()">
                    <option value="">-- All Tracks --</option>
                    <option value="kinder">Kinder</option>
                    <option value="junior high school">Junior High School</option>
                    <option value="senior high school">Senior High School</option>
                </select>
            </div>

            <div class="form-group">
                <label>2. Select Year Level</label>
                <select id="filter_year" onchange="assign_filterAll()">
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

        <div class="form-group">
            <label>3. Select Subject</label>
            <select id="filter_subject" onchange="assign_filterAll()">
                <option value="">-- All Subjects --</option>
                <?php foreach($subjects as $code => $info): ?>
                    <option value="<?php echo $code; ?>" data-track="<?php echo $info['track']; ?>" class="subj-opt">
                        <?php echo "$code - " . $info['desc']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <div class="form-group" style="background: #e6f7ff; padding: 15px; border: 1px solid #b3e0ff; border-radius:5px;">
            <label style="color:#0056b3;">4. Select Specific Section</label>
            <select name="section_id" id="final_section" required>
                <option value="">-- Select Filters First --</option>
                <?php foreach($sections as $sec): ?>
                    <option 
                        value="<?php echo $sec['id']; ?>"
                        class="sec-opt"
                        data-track="<?php echo $sec['track']; ?>"
                        data-year="<?php echo $sec['year_level']; ?>"
                        data-code="<?php echo $sec['code']; ?>"
                    >
                        <?php echo htmlspecialchars($sec['code'] . " - " . $sec['section'] . " (" . $sec['schedule_time'] . ")"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="background: #fff3cd; padding: 15px; border: 1px solid #ffeeba; border-radius:5px;">
            <label style="color:#856404;">5. Select Instructor</label>
            <select name="instructor_id" id="instructor_list" required>
                <option value="">-- Choose Instructor --</option>
                <?php foreach($instructors as $i): ?>
                    <option 
                        value="<?php echo $i['id']; ?>"
                        data-track="<?php echo $i['track']; ?>"
                    >
                        <?php echo htmlspecialchars($i['fname'] . ' ' . $i['lname']); ?> 
                        (<?php echo ucfirst($i['track']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" name="assign_btn" class="btn-save">
            Assign Instructor
        </button>
    </form>
</div>