<?php
// session_start();
// if (!isset($_SESSION['instructor_id'])) {
//     header('Location: ../login.php');
//     exit;
// }

require_once '../functions/instructor_function.php';

// For UI testing
$instructor_id = 1; // Replace with $_SESSION['instructor_id'] when session enabled
$instructorObj = new Instructor();

// Fetch instructor profile
$profile = $instructorObj->getProfile($instructor_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Instructor Portal - Welcome</title>
<link rel="stylesheet" href="index.css" />
<style>
.profile-icon {
    display: block;
    width: 40px; 
    height: 40px; 
    background: url('profile-icon.png') no-repeat center/contain;
    margin: 20px auto;
    cursor: pointer;
}
</style>
</head>
<body>

<div class="header">
    <div class="logo">AMS Instructor Portal</div>
    <form action="../logout.php" method="post" style="margin:0;">
        <button class="logout-button" type="submit">Logout</button>
    </form>
</div>

<div class="container">
    <div class="sidebar">
        <button onclick="location.href='class-list.php'">Class List</button>
        <button onclick="location.href='grading-sheet.php'">Grading Sheets</button>
        <a href="profile.php" title="Profile">
            <div class="profile-icon" aria-label="Profile"></div>
        </a>
    </div>

    <div class="main">
        <h2>Welcome, <?= htmlspecialchars($profile['full_name'] ?? 'Instructor') ?></h2>
        <p>This is your instructor portal. Use the sidebar to navigate to your classes or grading sheets.</p>

        <h3>Your Profile</h3>
        <ul>
            <li><strong>Username:</strong> <?= htmlspecialchars($profile['username'] ?? '') ?></li>
            <li><strong>Full Name:</strong> <?= htmlspecialchars($profile['full_name'] ?? '') ?></li>
            <li><strong>Email:</strong> <?= htmlspecialchars($profile['email'] ?? 'Not specified') ?></li>
        </ul>
    </div>
</div>

</body>
</html>
