<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') {
    http_response_code(403); echo "Access Denied."; exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// --- HANDLE ADD ---
if (isset($_POST['add_section'])) {
    try {
        $sql = "INSERT INTO section_list (section_name, year_level, track, adviser_id) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $adviser = empty($_POST['adviser_id']) ? null : $_POST['adviser_id'];
        
        $stmt->execute([trim($_POST['section_name']), $_POST['year_level'], $_POST['track'], $adviser]);
        echo "<script>alert('Section Created Successfully!'); loadZone('section_manager.php');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error: Section name might already exist for this track/year.');</script>";
    }
    exit;
}

// --- HANDLE UPDATE ---
if (isset($_POST['update_section'])) {
    try {
        $sql = "UPDATE section_list SET section_name=?, year_level=?, track=?, adviser_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $adviser = empty($_POST['adviser_id']) ? null : $_POST['adviser_id'];
        
        $stmt->execute([
            trim($_POST['section_name']), 
            $_POST['year_level'], 
            $_POST['track'], 
            $adviser,
            $_POST['db_id']
        ]);
        echo "<script>alert('Section Updated Successfully!'); loadZone('section_manager.php');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
    exit;
}

// --- HANDLE DELETE ---
if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM section_list WHERE id=?")->execute([$_POST['delete_id']]);
    echo "DELETED"; exit;
}

// --- FETCH DATA ---
$edit_mode = false;
$curr = [];

// Check for Edit Mode
if(isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM section_list WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $curr = $stmt->fetch(PDO::FETCH_ASSOC);
    if($curr) $edit_mode = true;
}

// Fetch Lists
$sections = $pdo->query("SELECT s.*, a.fname, a.lname FROM section_list s LEFT JOIN account a ON s.adviser_id = a.id ORDER BY s.track, s.year_level, s.section_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$instructors = $pdo->query("SELECT * FROM account WHERE role='instructor' ORDER BY lname ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* --- INLINE STYLES FOR SECTION MANAGER --- */
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
    content: "📂";
    font-size: 2rem;
}

.form-section {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #dee2e6;
    transition: all 0.3s;
}

.form-section.edit-mode {
    background: #fff3cd;
    border-color: #ffc107;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h3 {
    margin: 0;
    color: #002D72;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-section.edit-mode .section-header h3 {
    color: #856404;
}

.btn-cancel {
    background: #6c757d;
    color: white;
    padding: 6px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.2s;
}

.btn-cancel:hover {
    background: #5a6268;
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

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 15px;
    align-items: end;
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
    margin-top: 10px;
}

.btn-save:hover {
    background: #004099;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,45,114,0.2);
}

.btn-save:active {
    transform: translateY(0);
}

.table-container {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.sec-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 0.9rem;
}

.sec-table thead tr {
    background: #002D72;
    color: white;
}

.sec-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sec-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

.sec-table tbody tr {
    transition: background 0.2s;
}

.sec-table tbody tr:hover {
    background: #f0f8ff;
}

.section-name {
    font-weight: bold;
    color: #002D72;
    font-size: 1rem;
}

.track-info {
    color: #666;
    font-size: 0.85rem;
}

.action-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 3px;
    font-size: 1.1rem;
    transition: all 0.2s;
}

.btn-edit-icon {
    background: #ffc107;
    color: #000;
}

.btn-edit-icon:hover {
    background: #e0a800;
    transform: scale(1.1);
}

.btn-del-icon {
    background: #dc3545;
    color: white;
}

.btn-del-icon:hover {
    background: #a71d2a;
    transform: scale(1.1);
}

.no-adviser {
    color: #ccc;
    font-style: italic;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .sec-table {
        font-size: 0.85rem;
    }

    .sec-table th,
    .sec-table td {
        padding: 8px 6px;
    }

    .action-icon {
        width: 28px;
        height: 28px;
        font-size: 1rem;
    }
}
</style>

<div class="form-card">
    <h2 class="page-title">Section Manager</h2>

    <div class="form-section <?php echo $edit_mode ? 'edit-mode' : ''; ?>">
        
        <div class="section-header">
            <h3><?php echo $edit_mode ? '✏️ Edit Section' : '➕ Create New Section'; ?></h3>
            <?php if($edit_mode): ?>
                <button onclick="loadZone('section_manager.php')" class="btn-cancel">✕ Cancel</button>
            <?php endif; ?>
        </div>

        <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'section_manager.php');">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_section" value="1">
                <input type="hidden" name="db_id" value="<?php echo $curr['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_section" value="1">
            <?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Track</label>
                    <select name="track" required>
                        <?php $t = $edit_mode ? $curr['track'] : ''; ?>
                        <option value="kinder" <?php if($t=='kinder') echo 'selected'; ?>>Kinder</option>
                        <option value="junior high school" <?php if($t=='junior high school') echo 'selected'; ?>>Junior High School</option>
                        <option value="STEM" <?php if($t=='STEM') echo 'selected'; ?>>STEM</option>
                        <option value="ABM" <?php if($t=='ABM') echo 'selected'; ?>>ABM</option>
                        <option value="HUMSS" <?php if($t=='HUMSS') echo 'selected'; ?>>HUMSS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Year Level</label>
                    <select name="year_level">
                        <?php $y = $edit_mode ? $curr['year_level'] : ''; ?>
                        <option <?php if($y=='Kinder') echo 'selected'; ?>>Kinder</option>
                        <option <?php if($y=='Grade 7') echo 'selected'; ?>>Grade 7</option>
                        <option <?php if($y=='Grade 8') echo 'selected'; ?>>Grade 8</option>
                        <option <?php if($y=='Grade 9') echo 'selected'; ?>>Grade 9</option>
                        <option <?php if($y=='Grade 10') echo 'selected'; ?>>Grade 10</option>
                        <option <?php if($y=='Grade 11') echo 'selected'; ?>>Grade 11</option>
                        <option <?php if($y=='Grade 12') echo 'selected'; ?>>Grade 12</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section Name</label>
                    <input type="text" name="section_name" value="<?php echo $edit_mode ? htmlspecialchars($curr['section_name']) : ''; ?>" placeholder="e.g. Einstein" required>
                </div>
                <div class="form-group">
                    <label>Class Adviser</label>
                    <select name="adviser_id">
                        <option value="">-- None --</option>
                        <?php foreach($instructors as $i): ?>
                            <option value="<?php echo $i['id']; ?>" <?php if($edit_mode && $curr['adviser_id'] == $i['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($i['lname'] . ', ' . $i['fname']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-save">
                <?php echo $edit_mode ? '💾 Save Changes' : '➕ Create Section'; ?>
            </button>
        </form>
    </div>

    <div class="table-container">
        <table class="sec-table">
            <thead>
                <tr>
                    <th style="width:25%;">Track / Level</th>
                    <th style="width:25%;">Section Name</th>
                    <th style="width:35%;">Class Adviser</th>
                    <th style="width:15%; text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($sections)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:40px; color:#999; font-style:italic;">No sections found. Create one above!</td></tr>
                <?php else: ?>
                    <?php foreach($sections as $s): ?>
                    <tr>
                        <td><span class="track-info"><?php echo ucfirst($s['track']) . ' - ' . $s['year_level']; ?></span></td>
                        <td><span class="section-name"><?php echo htmlspecialchars($s['section_name']); ?></span></td>
                        <td>
                            <?php if($s['fname']): ?>
                                <?php echo htmlspecialchars($s['lname'] . ', ' . $s['fname']); ?>
                            <?php else: ?>
                                <span class="no-adviser">-- No Adviser --</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <button class="action-icon btn-edit-icon" onclick="loadZone('section_manager.php?edit_id=<?php echo $s['id']; ?>')" title="Edit Section">✎</button>
                            <button class="action-icon btn-del-icon" onclick="deleteSection(<?php echo $s['id']; ?>)" title="Delete Section">×</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>