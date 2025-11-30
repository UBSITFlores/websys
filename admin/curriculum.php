<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') die("Access Denied.");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){ die("DB Error"); }

// ADD
if(isset($_POST['add_subject'])){
    try{
        $pdo->prepare("INSERT INTO subjects (code, description, year_level, track, type, price) VALUES (?,?,?,?,?,?)")
        ->execute([ strtoupper(trim($_POST['code'])), trim($_POST['description']),
                    $_POST['year_level'], $_POST['track'], $_POST['type'], $_POST['price'] ]);
        echo "<script>alert('Subject Added!'); loadZone('curriculum.php');</script>";
    }catch(Exception $e){ echo "<script>alert('Error: ".$e->getMessage()."');</script>"; }
    exit;
}

// UPDATE
if(isset($_POST['update_subject'])){
    try{
        $pdo->prepare("UPDATE subjects SET code=?, description=?, year_level=?, track=?, type=?, price=? WHERE id=?")
        ->execute([ strtoupper(trim($_POST['code'])), trim($_POST['description']), $_POST['year_level'],
                    $_POST['track'], $_POST['type'], $_POST['price'], $_POST['db_id'] ]);
        echo "<script>alert('Updated!'); loadZone('curriculum.php');</script>";
    }catch(Exception $e){ echo "<script>alert('Error');</script>"; }
    exit;
}

// DELETE
if(isset($_POST['delete_id'])){
    $pdo->prepare("DELETE FROM subjects WHERE id=?")->execute([$_POST['delete_id']]);
    echo "DELETED"; exit;
}

// FETCH
$edit_mode=false; $curr=[];
if(isset($_GET['edit_id'])){
    $curr=$pdo->query("SELECT * FROM subjects WHERE id=".$_GET['edit_id'])->fetch(PDO::FETCH_ASSOC);
    if($curr) $edit_mode=true;
}
$subjects=$pdo->query("SELECT * FROM subjects ORDER BY track,year_level,code ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="curriculum.css">

<div class="form-card">

    <h2 class="title">Curriculum Manager</h2>

    <!-- FORM SECTION -->
    <div class="form-panel <?= $edit_mode?'edit-mode':''; ?>">
        <h3><?= $edit_mode?'✏️ Edit Subject':'+ Add New Subject'; ?></h3>

        <form method="POST" onsubmit="event.preventDefault(); submitForm(this,'curriculum.php');">
            
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_subject" value="1">
                <input type="hidden" name="db_id" value="<?= $curr['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_subject" value="1">
            <?php endif; ?>

            <div class="grid-3">
                <div class="form-group"><label>Code</label><input type="text" name="code" value="<?= $curr['code']??'';?>" required></div>
                <div class="form-group"><label>Description</label><input type="text" name="description" value="<?= $curr['description']??'';?>" required></div>
                <div class="form-group"><label>Type</label>
                    <select name="type">
                        <option <?=($curr['type']??'')=='Core'?'selected':''?>>Core</option>
                        <option <?=($curr['type']??'')=='Applied'?'selected':''?>>Applied</option>
                        <option <?=($curr['type']??'')=='Specialized'?'selected':''?>>Specialized</option>
                    </select>
                </div>
            </div>

            <div class="grid-4">
                <div class="form-group"><label>Track</label>
                    <select name="track">
                        <option <?=($curr['track']??'')=='Regular'?'selected':''?>>Regular</option>
                        <option <?=($curr['track']??'')=='STEM'?'selected':''?>>STEM</option>
                        <option <?=($curr['track']??'')=='ABM'?'selected':''?>>ABM</option>
                        <option <?=($curr['track']??'')=='HUMSS'?'selected':''?>>HUMSS</option>
                    </select>
                </div>

                <div class="form-group"><label>Year Level</label>
                    <select name="year_level">
                        <option <?=($curr['year_level']??'')=='Kinder'?'selected':''?>>Kinder</option>
                        <?php for($i=7;$i<=12;$i++): ?>
                            <option value="Grade <?=$i?>" <?=($curr['year_level']??'')=="Grade $i"?'selected':''?>>Grade <?=$i?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group"><label>Tuition Price (PHP)</label>
                    <input type="number" name="price" value="<?= $curr['price']??'0';?>" required>
                </div>

                <div class="align-bottom">
                    <button class="btn-save"><?= $edit_mode?'Save':'Add'; ?></button>
                </div>
            </div>
        </form>
    </div>

    <!-- SUBJECT LIST -->
    <div class="table-wrap">
        <table class="curr-table">
            <tr class="thead-row">
                <th>Track / Year</th><th>Code</th><th>Desc</th><th>Price</th><th>Action</th>
            </tr>

            <?php foreach($subjects as $s): ?>
            <tr>
                <td><small><?= $s['track']?> - <?= $s['year_level']?></small></td>
                <td><b><?= $s['code']?></b></td>
                <td><?= $s['description']?></td>
                <td class="price">₱<?= number_format($s['price'],2)?></td>
                <td>
                    <button onclick="loadZone('curriculum.php?edit_id=<?= $s['id']?>')" class="edit-btn">✎</button>
                    <button onclick="deleteSubject(<?= $s['id']?>)" class="del-btn">×</button>
                </td>
            </tr>
            <?php endforeach; ?>

        </table>
    </div>
</div>
