<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $portal->show_account_by_id($user_id);
$student = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>
    <h2>Student Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($student['fname']); ?>!</p>

    <h3>Profile</h3>
    <p>ID: <?php echo htmlspecialchars($student['account_id']); ?></p>
    <p>Name: <?php echo htmlspecialchars($student['fname'] . ' ' . $student['mname'] . ' ' . $student['lname']); ?></p>
    <p>Track: <?php echo htmlspecialchars($student['track']); ?></p>
    <p>Date Enrolled: <?php echo htmlspecialchars($student['date_enrolled']); ?></p>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
