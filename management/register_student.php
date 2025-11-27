<?php
ini_set('display_errors', 1);
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
        $pdo->beginTransaction(); // Start Transaction

        // --- 1. GENERATE ID (Based on School Year Settings) ---
        $config_stmt = $pdo->query("SELECT current_year FROM school_settings LIMIT 1");
        $config = $config_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Default to current year if setting is missing
        $sy = $config['current_year'] ?? date('Y').'-'.(date('Y')+1);
        $year_prefix = substr($sy, 0, 4);

        // Find last ID
        $stmt = $pdo->prepare("SELECT account_id FROM account WHERE account_id LIKE ? ORDER BY account_id DESC LIMIT 1");
        $stmt->execute([$year_prefix . '%']);
        $last = $stmt->fetchColumn();
        
        // Increment
        $nextSeq = $last ? ((int)substr($last, 4) + 1) : 1;
        $new_account_id = $year_prefix . str_pad($nextSeq, 4, "0", STR_PAD_LEFT);

        // --- 2. INSERT ACCOUNT ---
        $fname = $_POST['fname'] ?? ''; 
        $mname = $_POST['mname'] ?? '';
        $lname = $_POST['lname'] ?? '';
        $track = $_POST['track'] ?? '';

        $stmt = $pdo->prepare("INSERT INTO account (account_id, fname, mname, lname, date_enrolled, password, role, track) VALUES (?, ?, ?, ?, CURDATE(), ?, 'student', ?)");
        $stmt->execute([$new_account_id, $fname, $mname, $lname, $new_account_id, $track]);
        $pk_id = $pdo->lastInsertId();

        // --- 3. INSERT STUDENT PROFILE (With Split Addresses) ---
        $sql_student = "INSERT INTO students (
            student_id, track, grade_level, date_enrolled, 
            lrn, previous_school, prev_street, prev_barangay, prev_city, prev_province, prev_zip, 
            esc_grantee, has_sibling_enrolled,
            familyname, fname, mname, suffix, birthdate, birthplace, religion,
            civilstatus, nationality, gender, sex, contactno, email, 
            housenum_street, barangay, city, province, zipcode,
            fLname, fFname, fMName, fContactnum, fOccupation,
            mLname, mFname, mMName, mContactnum, mOccupation,
            gLname, gFname, gMName, gContactnum, gRelationship
        ) VALUES (
            ?, ?, ?, CURDATE(), 
            ?, ?, ?, ?, ?, ?, ?, 
            ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?
        )";

        $stmt2 = $pdo->prepare($sql_student);
        $stmt2->execute([
            $pk_id, $track, $_POST['grade_level'],
            $_POST['lrn'] ?? '', 
            $_POST['previous_school'] ?? '', 
            $_POST['prev_street'] ?? '', $_POST['prev_barangay'] ?? '', $_POST['prev_city'] ?? '', $_POST['prev_province'] ?? '', $_POST['prev_zip'] ?? '',
            $_POST['esc_grantee'] ?? 'No', $_POST['has_sibling'] ?? 0,
            $lname, $fname, $mname, $_POST['suffix'] ?? '',
            $_POST['birthdate'], $_POST['birthplace'] ?? '', $_POST['religion'] ?? '',
            $_POST['civilstatus'] ?? '', $_POST['nationality'] ?? 'Filipino', $_POST['gender'] ?? '', $_POST['sex'] ?? '',
            $_POST['contactno'], $_POST['email'] ?? '',
            $_POST['housenum_street'] ?? '', $_POST['barangay'] ?? '', $_POST['city'] ?? '', $_POST['province'] ?? '', $_POST['zipcode'] ?? '',
            $_POST['fLname'] ?? '', $_POST['fFname'] ?? '', $_POST['fMName'] ?? '', $_POST['fContactnum'] ?? '', $_POST['fOccupation'] ?? '',
            $_POST['mLname'] ?? '', $_POST['mFname'] ?? '', $_POST['mMName'] ?? '', $_POST['mContactnum'] ?? '', $_POST['mOccupation'] ?? '',
            $_POST['gLname'] ?? '', $_POST['gFname'] ?? '', $_POST['gMName'] ?? '', $_POST['gContactnum'] ?? '', $_POST['gRelationship'] ?? ''
        ]);

        // --- 4. AUTO-ENROLLMENT & BILLING ---
        $section_name = $_POST['section'];
        $grade_level = $_POST['grade_level'];

        // Find all classes (Sections) that match this Name + Grade
        // We join with 'subjects' table to get the price
        $sql_classes = "SELECT s.id, sub.price, sub.description 
                        FROM sections s
                        JOIN subjects sub ON s.code = sub.code
                        WHERE s.section = ? AND s.year_level = ?";
        
        $cls_stmt = $pdo->prepare($sql_classes);
        $cls_stmt->execute([$section_name, $grade_level]);
        $classes = $cls_stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_fee = 0;
        $count = 0;

        foreach($classes as $cls) {
            // Enroll in the specific section
            $ins = $pdo->prepare("INSERT INTO enrollments (student_id, section_id, date_enrolled) VALUES (?, ?, CURDATE())");
            $ins->execute([$pk_id, $cls['id']]);
            
            // Add to total tuition
            $total_fee += $cls['price'];
            $count++;
        }

        // Create the Billing Assessment
        if($total_fee > 0) {
            $assess = $pdo->prepare("INSERT INTO assessments (student_id, total_amount, school_year, term_mode) VALUES (?, ?, ?, ?)");
            $assess->execute([$pk_id, $total_fee, $sy, "Tuition: $grade_level - $section_name"]);
        }

        $pdo->commit(); // Commit all changes

        echo "<div style='background:#d4edda; color:#155724; padding:20px; border-radius:5px; text-align:center;'>
                <h2>✅ Student Registered!</h2>
                <p>Student ID: <strong>$new_account_id</strong></p>
                <p>Assigned to: <strong>$grade_level - $section_name</strong></p>
                <p>Subjects Enrolled: <strong>$count</strong></p>
                <p>Total Tuition Assessed: <strong>₱" . number_format($total_fee, 2) . "</strong></p>
                <button class='btn-save' onclick=\"loadZone('enroll-student-ajax.php', this)\">Register Another</button>
              </div>";

    } catch (Exception $e) {
        $pdo->rollBack(); // Undo if anything fails
        echo "<div style='color:red; border:1px solid red; padding:15px;'>
                <h3>Database Error</h3>
                <p>" . $e->getMessage() . "</p>
              </div>";
    }
}
?>