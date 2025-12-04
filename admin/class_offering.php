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

<style>
/* --- INLINE STYLES FOR CLASS OFFERING --- */
.form-card {
    max-width: 1100px;
    margin: 0 auto;
    background: #fff;
    padding: 35px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.page-title {
    color: #002D72;
    border-bottom: 3px solid #febb3f;
    padding-bottom: 15px;
    margin-top: 0;
    margin-bottom: 25px;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-title::before {
    content: "📅";
    font-size: 2rem;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 6px;
    font-size: 0.9rem;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 1rem;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #002D72;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.1);
}

.form-group input[readonly] {
    background: #e9ecef;
    cursor: not-allowed;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 15px;
}

.highlight-section {
    background: #eef2ff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #cce5ff;
    margin-bottom: 15px;
}

.highlight-section label {
    color: #002D72 !important;
    font-weight: 700;
}

.btn-save {
    background: #002D72;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.2s;
    width: 100%;
    margin-top: 15px;
}

.btn-save:hover {
    background: #004099;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,45,114,0.2);
}

.btn-save:active {
    transform: translateY(0);
}

.info-text {
    color: #666;
    font-size: 0.85rem;
    margin-top: 4px;
    font-style: italic;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .form-grid-2,
    .form-grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="form-card">
    <h2 class="page-title">Class Assignment & Offering</h2>
    
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'class_offering.php');">
        
        <div class="form-grid-2">
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

        <div class="highlight-section">
            <div class="form-grid-2">
                <div class="form-group">
                    <label>📖 Subject (From Curriculum)</label>
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
                    <small class="info-text">Choose the subject to be taught</small>
                </div>
                
                <div class="form-group">
                    <label>👥 Target Section</label>
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
                    <small class="info-text">Assign this class to a section</small>
                </div>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>🕐 Schedule</label>
                <input type="text" name="schedule" placeholder="e.g. MWF 8:00-9:30 AM" required>
            </div>
            <div class="form-group">
                <label>🏫 Room</label>
                <input type="text" name="room" placeholder="e.g. Room 304" required>
            </div>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label>👨‍🏫 Instructor</label>
                <select name="instructor_id" required>
                    <option value="">-- Select Faculty --</option>
                    <?php foreach($instructors as $i): ?>
                        <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['lname'] . ', ' . $i['fname']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>📆 Term / Semester</label>
                <select name="semester" id="sel_term" required>
                    <option value="Whole Year">Whole Year</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>📅 School Year</label>
                <input type="text" name="school_year" value="<?php echo htmlspecialchars($current_sy); ?>" readonly>
            </div>
        </div>

        <button type="submit" class="btn-save">✅ Create Class Section</button>
    </form>
</div>

<script>
function resetYear() {
    const yearSel = document.getElementById('sel_year');
    yearSel.value = '';
    
    // Hide all year options
    document.querySelectorAll('.opt-level').forEach(opt => opt.style.display = 'none');
    
    // Show relevant year options based on track
    const track = document.getElementById('sel_track').value;
    
    if (track === 'kinder') {
        document.querySelectorAll('.opt-kinder').forEach(opt => opt.style.display = 'block');
    } else if (track === 'junior high school') {
        document.querySelectorAll('.opt-jhs').forEach(opt => opt.style.display = 'block');
    } else if (track === 'STEM' || track === 'ABM' || track === 'HUMSS') {
        document.querySelectorAll('.opt-shs').forEach(opt => opt.style.display = 'block');
    }
}

function filterOptions() {
    const track = document.getElementById('sel_track').value;
    const year = document.getElementById('sel_year').value;
    
    // Hide all subject and section options first
    document.querySelectorAll('.sub-opt').forEach(opt => opt.style.display = 'none');
    document.querySelectorAll('.sec-opt').forEach(opt => opt.style.display = 'none');
    
    if (!track || !year) return;
    
    // Show matching subjects
    document.querySelectorAll('.sub-opt').forEach(opt => {
        if (opt.dataset.track === track && opt.dataset.year === year) {
            opt.style.display = 'block';
        }
    });
    
    // Show matching sections
    document.querySelectorAll('.sec-opt').forEach(opt => {
        if (opt.dataset.track === track && opt.dataset.year === year) {
            opt.style.display = 'block';
        }
    });
}
</script>