<?php
session_start();
if (!isset($_SESSION['ACCOUNTID']) && !isset($_SESSION['account_id'])) {
    header("Location: ../account/login.php");
    exit;
}
$instructorName = $_SESSION['FNAME'] ?? $_SESSION['fname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="header">
        School Portal - Instructor Panel
        <span class="userinfo">Logged in as: <b><?php echo htmlspecialchars($instructorName); ?></b> (<a href="../account/logout.php" style="color:#fff;">Logout</a>)</span>
    </div>
    <div class="container">
        <div id="main-content" class="content-zone">
            <div class="placeholder">Select a function from the right sidebar.</div>
        </div>
        <div class="sidebar-right">
            <button id="btn-grading" class="active" onclick="loadZone('grading-sheet-ajax.php', this)">Grading Sheet</button>
        </div>
    </div>
    <script src="dashboard.js"></script>
</body>
</html>
