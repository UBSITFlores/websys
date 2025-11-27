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

<div class="form-card" style="max-width: 1000px;">
    <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Section Manager</h2>

    <div style="background:<?php echo $edit_mode ? '#fff3cd' : '#f8f9fa'; ?>; padding:20px; border-radius:8px; border:1px solid <?php echo $edit_mode ? '#ffecb5' : '#e9ecef'; ?>; margin-bottom:30px;">
        
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin-top:0; color:<?php echo $edit_mode ? '#856404' : '#002D72'; ?>;">
                <?php echo $edit_mode ? '✏️ Edit Section' : '+ Create New Section'; ?>
            </h3>
            <?php if($edit_mode): ?>
                <button onclick="loadZone('section_manager.php')" style="background:#6c757d; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">Cancel</button>
            <?php endif; ?>
        </div>

        <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'section_manager.php');">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_section" value="1">
                <input type="hidden" name="db_id" value="<?php echo $curr['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_section" value="1">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; align-items: end;">
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
            <button type="submit" class="btn-save" style="margin-top:10px; width:100%;">
                <?php echo $edit_mode ? 'Save Changes' : 'Create Section'; ?>
            </button>
        </form>
    </div>

    <style>
        .sec-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .sec-table th { background: #002D72; color: white; padding: 10px; text-align: left; }
        .sec-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .sec-table tr:hover { background: #f0f8ff; }
        
        .action-icon { width: 30px; height: 30px; border-radius: 50%; border: none; color: white; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; margin: 0 2px; }
        .btn-edit-icon { background: #ffc107; color: #000; }
        .btn-del-icon { background: #dc3545; }
        .btn-edit-icon:hover { background: #e0a800; }
        .btn-del-icon:hover { background: #a71d2a; }
    </style>

    <div style="overflow-x:auto;">
        <table class="sec-table">
            <thead>
                <tr>
                    <th>Track / Level</th>
                    <th>Section Name</th>
                    <th>Class Adviser</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($sections as $s): ?>
                <tr>
                    <td><small><?php echo ucfirst($s['track']) . ' - ' . $s['year_level']; ?></small></td>
                    <td style="font-weight:bold; color:#002D72;"><?php echo htmlspecialchars($s['section_name']); ?></td>
                    <td>
                        <?php if($s['fname']): ?>
                            <?php echo htmlspecialchars($s['lname'] . ', ' . $s['fname']); ?>
                        <?php else: ?>
                            <span style="color:#ccc;">--</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <button class="action-icon btn-edit-icon" onclick="loadZone('section_manager.php?edit_id=<?php echo $s['id']; ?>')">✎</button>
                        <button class="action-icon btn-del-icon" onclick="deleteSection(<?php echo $s['id']; ?>)">×</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>