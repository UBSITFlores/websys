<?php
session_start();
if (isset($_SESSION['ACCOUNTID']) && isset($_SESSION['ROLE'])) {
    if ($_SESSION['ROLE'] === "admin") {
        header("Location: ./admin_dashboard.php");
        exit();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Portal Home</title>
    <style>body{font-family:Arial; max-width:700px; margin:40px auto;text-align:center;} a.btn{display:inline-block;padding:10px 14px;border:1px solid #333;border-radius:4px;text-decoration:none;}</style>
</head>
<body>
<h1>Portal</h1>
<p><a class="btn" href="admin_dashboard.php">Go to Admin Dashboard</a></p>
</body>
</html>
