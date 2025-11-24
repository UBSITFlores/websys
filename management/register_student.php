
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['ACCOUNTID']) || $_SESSION['ROLE'] !== 'management') {
    echo "ERROR: Not authenticated/session missing.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=portal;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $year = date('Y');
        $stmt = $pdo->prepare("SELECT account_id FROM account WHERE account_id LIKE ? ORDER BY account_id DESC LIMIT 1");
        $stmt->execute([$year . '%']);
        $last = $stmt->fetchColumn();
        $nextSeq = $last ? ((int)substr($last, 4) + 1) : 1;
        $account_id = $year . str_pad($nextSeq, 4, "0", STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO account (account_id, fname, mname, lname, date_enrolled, password, role, track)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute([
            $account_id,
            $_POST['fname'] ?? '',
            $_POST['mname'] ?? '',
            $_POST['lname'] ?? '',
            date('Y-m-d'),
            $account_id,
            'student',
            $_POST['track'] ?? ''
        ]);
        if (!$ok) exit('Account insert failed');
        $account_pk = $pdo->lastInsertId();

        // Make sure all these names match HTML form field 'name' exactly!
        $stmt = $pdo->prepare(
            "INSERT INTO students (
                student_id, track, date_enrolled, familyname, fname, mname, suffix, birthdate, birthplace, religion,
                civilstatus, nationality, gender, sex, first_gen_question, ethnicity, contactno, email, housenum_street,
                barangay, city, province, zipcode, year_graduated, sLast, sStreet, sBarangay, sCity, sProvince, sZipcode,
                gLname, gFname, gMName, gContactnum, gOccupation, gAddress, gRelationship,
                mLname, mFname, mMName, mContactnum, mOccupation, mAddress,
                fLname, fFname, fMName, fContactnum, fOccupation, fAddress
            ) VALUES (
                :student_id, :track, :date_enrolled, :familyname, :fname, :mname, :suffix, :birthdate, :birthplace, :religion,
                :civilstatus, :nationality, :gender, :sex, :first_gen_question, :ethnicity, :contactno, :email, :housenum_street,
                :barangay, :city, :province, :zipcode, :year_graduated, :sLast, :sStreet, :sBarangay, :sCity, :sProvince, :sZipcode,
                :gLname, :gFname, :gMName, :gContactnum, :gOccupation, :gAddress, :gRelationship,
                :mLname, :mFname, :mMName, :mContactnum, :mOccupation, :mAddress,
                :fLname, :fFname, :fMName, :fContactnum, :fOccupation, :fAddress
            )"
        );
        $ok = $stmt->execute([
            ':student_id' => $account_pk,
            ':track' => $_POST['track'] ?? '',
            ':date_enrolled' => date('Y-m-d'),
            ':familyname' => $_POST['lname'] ?? '',
            ':fname' => $_POST['fname'] ?? '',
            ':mname' => $_POST['mname'] ?? '',
            ':suffix' => $_POST['suffix'] ?? '',
            ':birthdate' => $_POST['birthdate'] ?? '',
            ':birthplace' => $_POST['birthplace'] ?? '',
            ':religion' => $_POST['religion'] ?? '',
            ':civilstatus' => $_POST['civilstatus'] ?? '',
            ':nationality' => $_POST['nationality'] ?? '',
            ':gender' => $_POST['gender'] ?? '',
            ':sex' => $_POST['sex'] ?? '',
            ':first_gen_question' => $_POST['first_gen_question'] ?? '',
            ':ethnicity' => $_POST['ethnicity'] ?? '',
            ':contactno' => $_POST['contactno'] ?? '',
            ':email' => $_POST['email'] ?? '',
            ':housenum_street' => $_POST['housenum_street'] ?? '',
            ':barangay' => $_POST['barangay'] ?? '',
            ':city' => $_POST['city'] ?? '',
            ':province' => $_POST['province'] ?? '',
            ':zipcode' => $_POST['zipcode'] ?? '',
            ':year_graduated' => $_POST['year_graduated'] ?? '',
            ':sLast' => $_POST['sLast'] ?? '',
            ':sStreet' => $_POST['sStreet'] ?? '',
            ':sBarangay' => $_POST['sBarangay'] ?? '',
            ':sCity' => $_POST['sCity'] ?? '',
            ':sProvince' => $_POST['sProvince'] ?? '',
            ':sZipcode' => $_POST['sZipcode'] ?? '',
            ':gLname' => $_POST['gLname'] ?? '',
            ':gFname' => $_POST['gFname'] ?? '',
            ':gMName' => $_POST['gMName'] ?? '',
            ':gContactnum' => $_POST['gContactnum'] ?? '',
            ':gOccupation' => $_POST['gOccupation'] ?? '',
            ':gAddress' => $_POST['gAddress'] ?? '',
            ':gRelationship' => $_POST['gRelationship'] ?? '',
            ':mLname' => $_POST['mLname'] ?? '',
            ':mFname' => $_POST['mFname'] ?? '',
            ':mMName' => $_POST['mMName'] ?? '',
            ':mContactnum' => $_POST['mContactnum'] ?? '',
            ':mOccupation' => $_POST['mOccupation'] ?? '',
            ':mAddress' => $_POST['mAddress'] ?? '',
            ':fLname' => $_POST['fLname'] ?? '',
            ':fFname' => $_POST['fFname'] ?? '',
            ':fMName' => $_POST['fMName'] ?? '',
            ':fContactnum' => $_POST['fContactnum'] ?? '',
            ':fOccupation' => $_POST['fOccupation'] ?? '',
            ':fAddress' => $_POST['fAddress'] ?? ''
        ]);
        if (!$ok) exit('Student insert failed');
        echo "<h3>Student registered successfully!</h3>
              Student ID: <b>$account_id</b><br>
              Initial password: <b>$account_id</b>
              <br><br><a href='index.php'>Back to Dashboard</a>";
    } catch(Exception $e) {
        echo "<h3>Error:</h3> " . htmlspecialchars($e->getMessage());
    }
}
?>
