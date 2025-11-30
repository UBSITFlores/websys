<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Access Denied."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- HANDLE UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    try {
        // Removed 'sex' from update query
        $sql = "UPDATE students SET 
                lrn=?, civilstatus=?, nationality=?, gender=?, contactno=?, email=?,
                housenum_street=?, barangay=?, city=?, province=?, zipcode=?,
                fLname=?, fFname=?, fMname=?, fContactnum=?, fOccupation=?,
                mLname=?, mFname=?, mMname=?, mContactnum=?, mOccupation=?,
                gLname=?, gFname=?, gMname=?, gContactnum=?, gRelationship=?
                WHERE student_id = (SELECT id FROM account WHERE account_id = ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['lrn'], $_POST['civilstatus'], $_POST['nationality'], $_POST['gender'], $_POST['contactno'], $_POST['email'],
            $_POST['street'], $_POST['barangay'], $_POST['city'], $_POST['province'], $_POST['zipcode'],
            $_POST['fLname'], $_POST['fFname'], $_POST['fMName'], $_POST['fContactnum'], $_POST['fOccupation'],
            $_POST['mLname'], $_POST['mFname'], $_POST['mMName'], $_POST['mContactnum'], $_POST['mOccupation'],
            $_POST['gLname'], $_POST['gFname'], $_POST['gMName'], $_POST['gContactnum'], $_POST['gRelationship'],
            $_POST['student_id_display']
        ]);

        echo "<script>alert('Student Record Updated Successfully!'); loadZone('edit_student.php?sid=" . $_POST['student_id_display'] . "');</script>";

    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
    exit;
}

// --- FETCH DATA ---
$student = [];
$search_id = $_GET['sid'] ?? '';
if ($search_id) {
    $stmt = $pdo->prepare("SELECT s.*, a.fname as first, a.lname as last, a.mname as middle FROM students s JOIN account a ON s.student_id = a.id WHERE a.account_id = ?");
    $stmt->execute([$search_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="form-card" style="max-width: 900px;">
    <h2 style="color:#002D72; border-bottom:2px solid #febb3f; padding-bottom:10px;">Update Student Records</h2>

    <div style="background:#f0f8ff; padding:20px; border-radius:8px; margin-bottom:20px; border:1px solid #cce5ff; display:flex; gap:10px;">
        <input type="text" id="edit_search" value="<?php echo htmlspecialchars($search_id); ?>" placeholder="Enter Student ID (e.g. 20260001)" style="flex:1; padding:10px; border:1px solid #aaa; border-radius:4px;">
        <button type="button" onclick="loadZone('edit_student.php?sid=' + document.getElementById('edit_search').value)" class="btn-save" style="width:auto; padding:10px 30px;">Load Profile</button>
    </div>

    <?php if ($student): ?>
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'edit_student.php');">
        <input type="hidden" name="update_student" value="1">
        <input type="hidden" name="student_id_display" value="<?php echo $search_id; ?>">

        <h3 style="color:#002D72; margin-top:0;">I. Personal Details</h3>
        
        <div style="background:#eee; padding:10px; border-radius:4px; margin-bottom:15px; font-weight:bold; color:#333;">
            Name: <?php echo htmlspecialchars($student['last'] . ', ' . $student['first'] . ' ' . $student['middle']); ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group"><label>LRN</label><input type="text" name="lrn" value="<?php echo $student['lrn']; ?>"></div>
            <div class="form-group"><label>Civil Status</label>
                <select name="civilstatus">
                    <option <?php if($student['civilstatus']=='Single') echo 'selected'; ?>>Single</option>
                    <option <?php if($student['civilstatus']=='Married') echo 'selected'; ?>>Married</option>
                </select>
            </div>
            <div class="form-group"><label>Gender</label>
                <select name="gender">
                    <option <?php if($student['gender']=='Male') echo 'selected'; ?>>Male</option>
                    <option <?php if($student['gender']=='Female') echo 'selected'; ?>>Female</option>
                </select>
            </div>
        </div>

        <h3>II. Contact & Address</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group"><label>Mobile</label><input type="text" name="contactno" value="<?php echo $student['contactno']; ?>"></div>
            <div class="form-group"><label>Email</label><input type="text" name="email" value="<?php echo $student['email']; ?>"></div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; gap: 10px;">
                <input type="text" name="street" value="<?php echo $student['housenum_street']; ?>" placeholder="Street">
                <input type="text" name="barangay" value="<?php echo $student['barangay']; ?>" placeholder="Brgy">
                <input type="text" name="city" value="<?php echo $student['city']; ?>" placeholder="City">
                <input type="text" name="province" value="<?php echo $student['province']; ?>" placeholder="Prov">
                <input type="text" name="zipcode" value="<?php echo $student['zipcode']; ?>" placeholder="Zip">
            </div>
        </div>

        <h3>III. Family Background</h3>
        <div class="family-box">
            <label class="family-label">Father</label>
            <div class="grid-3-col">
                <input type="text" name="fLname" value="<?php echo $student['fLname']; ?>">
                <input type="text" name="fFname" value="<?php echo $student['fFname']; ?>">
                <input type="text" name="fMName" value="<?php echo $student['fMname']; ?>">
            </div>
            <div class="grid-2-col">
                <input type="text" name="fContactnum" value="<?php echo $student['fContactnum']; ?>">
                <input type="text" name="fOccupation" value="<?php echo $student['fOccupation']; ?>">
            </div>
        </div>
        <div class="family-box">
            <label class="family-label">Mother</label>
            <div class="grid-3-col">
                <input type="text" name="mLname" value="<?php echo $student['mLname']; ?>">
                <input type="text" name="mFname" value="<?php echo $student['mFname']; ?>">
                <input type="text" name="mMName" value="<?php echo $student['mMname']; ?>">
            </div>
            <div class="grid-2-col">
                <input type="text" name="mContactnum" value="<?php echo $student['mContactnum']; ?>">
                <input type="text" name="mOccupation" value="<?php echo $student['mOccupation']; ?>">
            </div>
        </div>

        <button type="submit" class="btn-save" style="margin-top:20px;">Update Student Record</button>
    </form>
    <?php elseif($search_id): ?>
        <div style="padding:30px; text-align:center; color:red;">Student ID not found.</div>
    <?php endif; ?>
</div>