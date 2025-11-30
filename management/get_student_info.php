<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "portal";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch(PDOException $e) {
    echo "ERROR|Database Connection Failed";
    exit;
}

if (isset($_POST['student_id'])) {
    $aid = trim($_POST['student_id']);
    $sql = "SELECT fname, lname, track FROM account 
            WHERE account_id = :aid AND role = 'student' LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':aid' => $aid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        echo "FOUND|" . $student['fname'] . " " . $student['lname'] . "|" . $student['track'];
    } else {
        echo "NOT_FOUND";
    }
}
?>