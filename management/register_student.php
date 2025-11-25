<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost"; $user = "root"; $pass = ""; $db = "portal";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $year = date('Y');
        $stmt = $pdo->prepare("SELECT account_id FROM account WHERE account_id LIKE ? ORDER BY account_id DESC LIMIT 1");
        $stmt->execute([$year . '%']);
        $last = $stmt->fetchColumn();
        $nextSeq = $last ? ((int)substr($last, 4) + 1) : 1;
        $new_account_id = $year . str_pad($nextSeq, 4, "0", STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO account (account_id, fname, mname, lname, date_enrolled, password, role, track) VALUES (?, ?, ?, ?, CURDATE(), ?, 'student', ?)");
        $fname = $_POST['fname'] ?? ''; 
        $mname = $_POST['mname'] ?? '';
        $lname = $_POST['lname'] ?? '';
        $track = $_POST['track'] ?? '';

        $stmt->execute([$new_account_id, $fname, $mname, $lname, $new_account_id, $track]);
        $pk_id = $pdo->lastInsertId();
        $sql_student = "INSERT INTO students (
            student_id, track, date_enrolled, familyname, fname, mname, suffix, birthdate, birthplace, religion,
            civilstatus, nationality, gender, sex, contactno, email, 
            housenum_street, barangay, city, province, zipcode,
            fLname, fFname, fMName, fContactnum, fOccupation,
            mLname, mFname, mMName, mContactnum, mOccupation,
            gLname, gFname, gMName, gContactnum, gRelationship
        ) VALUES (
            ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?
        )";

        $stmt2 = $pdo->prepare($sql_student);
        $stmt2->execute([
            $pk_id, $track, $lname, $fname, $mname, $_POST['suffix'] ?? '',
            $_POST['birthdate'], $_POST['birthplace'] ?? '', $_POST['religion'] ?? '',
            $_POST['civilstatus'] ?? '', $_POST['nationality'] ?? '', $_POST['gender'] ?? '', $_POST['sex'] ?? '',
            $_POST['contactno'], $_POST['email'] ?? '',
            $_POST['housenum_street'] ?? '', $_POST['barangay'] ?? '', $_POST['city'] ?? '', $_POST['province'] ?? '', $_POST['zipcode'] ?? '',
            $_POST['fLname'] ?? '', $_POST['fFname'] ?? '', $_POST['fMName'] ?? '', $_POST['fContactnum'] ?? '', $_POST['fOccupation'] ?? '',
            $_POST['mLname'] ?? '', $_POST['mFname'] ?? '', $_POST['mMName'] ?? '', $_POST['mContactnum'] ?? '', $_POST['mOccupation'] ?? '',
            $_POST['gLname'] ?? '', $_POST['gFname'] ?? '', $_POST['gMName'] ?? '', $_POST['gContactnum'] ?? '', $_POST['gRelationship'] ?? ''
        ]);

        echo "<div style='background:#d4edda; color:#155724; padding:20px; border-radius:5px; text-align:center;'>
                <h2>✅ Student Registered!</h2>
                <p>Student ID: <strong>$new_account_id</strong></p>
                <button class='btn-save' onclick=\"loadZone('enroll-student-ajax.php', this)\">Register Another</button>
              </div>";

    } catch (PDOException $e) {
        echo "<div style='color:red; border:1px solid red; padding:15px;'>
                <h3>Database Error</h3>
                <p>" . $e->getMessage() . "</p>
              </div>";
    }
}
?>