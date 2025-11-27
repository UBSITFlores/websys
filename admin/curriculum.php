<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') { die("Access Denied."); }

try {
    $pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("DB Error"); }

// --- ADD ---
if (isset($_POST['add_subject'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO subjects (code, description, year_level, track, type, price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            strtoupper(trim($_POST['code'])), trim($_POST['description']), $_POST['year_level'], $_POST['track'], $_POST['type'], $_POST['price']
        ]);
        echo "<script>alert('Subject Added!'); loadZone('curriculum.php');</script>";
    } catch (Exception $e) { echo "<script>alert('Error: " . $e->getMessage() . "');</script>"; }
    exit;
}

// --- UPDATE ---
if (isset($_POST['update_subject'])) {
    try {
        $stmt = $pdo->prepare("UPDATE subjects SET code=?, description=?, year_level=?, track=?, type=?, price=? WHERE id=?");
        $stmt->execute([
            strtoupper(trim($_POST['code'])), trim($_POST['description']), $_POST['year_level'], $_POST['track'], $_POST['type'], $_POST['price'], $_POST['db_id']
        ]);
        echo "<script>alert('Updated!'); loadZone('curriculum.php');</script>";
    } catch (Exception $e) { echo "<script>alert('Error');</script>"; }
    exit;
}

// --- DELETE ---
if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM subjects WHERE id=?")->execute([$_POST['delete_id']]);
    echo "DELETED"; exit;
}

// --- FETCH ---
$edit_mode = false; $curr = [];
if(isset($_GET['edit_id'])) {
    $curr = $pdo->query("SELECT * FROM subjects WHERE id=" . $_GET['edit_id'])->fetch(PDO::FETCH_ASSOC);
    if($curr) $edit_mode = true;
}
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY track, year_level, code ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="form-card" style="max-width: 1100px;">
    <h2 style="color:#002D72; border-bottom:2px solid #eee; padding-bottom:10px;">Curriculum Manager</h2>

    <!-- FORM -->
    <div style="background:<?php echo $edit_mode?'#fff3cd':'#f8f9fa'; ?>; padding:20px; border-radius:8px; margin-bottom:30px; border:1px solid #ddd;">
        <h3 style="margin-top:0;"><?php echo $edit_mode?'✏️ Edit Subject':'+ Add New Subject'; ?></h3>
        <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'curriculum.php');">
            <?php if($edit_mode): ?> <input type="hidden" name="update_subject" value="1"><input type="hidden" name="db_id" value="<?php echo $curr['id']; ?>">
            <?php else: ?> <input type="hidden" name="add_subject" value="1"> <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group"><label>Code</label><input type="text" name="code" value="<?php echo $curr['code']??''; ?>" required></div>
                <div class="form-group"><label>Description</label><input type="text" name="description" value="<?php echo $curr['description']??''; ?>" required></div>
                <div class="form-group"><label>Type</label>
                    <select name="type">
                        <option <?php if(($curr['type']??'')=='Core') echo 'selected'; ?>>Core</option>
                        <option <?php if(($curr['type']??'')=='Applied') echo 'selected'; ?>>Applied</option>
                        <option <?php if(($curr['type']??'')=='Specialized') echo 'selected'; ?>>Specialized</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group"><label>Track</label>
                    <select name="track">
                        <option <?php if(($curr['track']??'')=='Regular') echo 'selected'; ?>>Regular</option>
                        <option <?php if(($curr['track']??'')=='STEM') echo 'selected'; ?>>STEM</option>
                        <option <?php if(($curr['track']??'')=='ABM') echo 'selected'; ?>>ABM</option>
                        <option <?php if(($curr['track']??'')=='HUMSS') echo 'selected'; ?>>HUMSS</option>
                    </select>
                </div>
                <div class="form-group"><label>Year Level</label>
                    <select name="year_level">
                        <option <?php if(($curr['year_level']??'')=='Kinder') echo 'selected'; ?>>Kinder</option>
                        <?php for($i=7;$i<=12;$i++): ?>
                            <option value="Grade <?php echo $i; ?>" <?php if(($curr['year_level']??'')=="Grade $i") echo 'selected'; ?>>Grade <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <!-- NEW PRICE FIELD -->
                <div class="form-group"><label>Tuition Price (PHP)</label>
                    <input type="number" name="price" value="<?php echo $curr['price']??'0'; ?>" placeholder="0.00" required>
                </div>
                <div style="align-self:end;"><button class="btn-save" style="width:100%;"><?php echo $edit_mode?'Save':'Add'; ?></button></div>
            </div>
        </form>
    </div>

    <!-- LIST -->
    <div style="overflow-x:auto;">
        <table class="curr-table" style="width:100%; border-collapse:collapse;">
            <tr style="background:#002D72; color:white; text-align:left;">
                <th style="padding:10px;">Track / Year</th><th>Code</th><th>Desc</th><th>Price</th><th>Action</th>
            </tr>
            <?php foreach($subjects as $s): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:10px;"><small><?php echo $s['track']; ?> - <?php echo $s['year_level']; ?></small></td>
                <td style="font-weight:bold;"><?php echo $s['code']; ?></td>
                <td><?php echo $s['description']; ?></td>
                <td style="color:#198754; font-weight:bold;">₱<?php echo number_format($s['price'], 2); ?></td>
                <td>
                    <button onclick="loadZone('curriculum.php?edit_id=<?php echo $s['id']; ?>')" style="cursor:pointer;">✎</button>
                    <button onclick="deleteSubject(<?php echo $s['id']; ?>)" style="cursor:pointer; color:red;">×</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>