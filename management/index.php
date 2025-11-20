<?php
session_start();
if (!isset($_SESSION['ACCOUNTID']) || $_SESSION['ROLE'] !== 'management') {
    header('Location: ../account/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Management Dashboard</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="header">
        Management Dashboard
        <span style="float:right;font-size:14px;">Logged in as: <b><?php echo htmlspecialchars($_SESSION['FNAME']); ?></b> (<a href="../account/logout.php" style="color:#fff;">Logout</a>)</span>
    </div>
    <div class="container">
        <div class="sidebar-left">
            <button id="btn-enroll" class="active" onclick="loadZone('enroll_student.php', this)">Enroll Student</button>
        </div>
        <div id="main-content" class="content-zone">
            <div class="placeholder">Use the left sidebar to enroll a new student.</div>
        </div>
    </div>
    <script src="dashboard.js"></script>
</body>
</html>
