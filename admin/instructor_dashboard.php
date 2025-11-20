<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $portal->show_account_by_id($user_id);
$instructor = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Instructor Dashboard</title>
</head>
<body>
    <h2>Instructor Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($instructor['fname']); ?>!</p>

    <h3>Profile</h3>
    <p>ID: <?php echo htmlspecialchars($instructor['account_id']); ?></p>
    <p>Name: <?php echo htmlspecialchars($instructor['fname'] . ' ' . $instructor['mname'] . ' ' . $instructor['lname']); ?></p>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
