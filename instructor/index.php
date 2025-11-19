<?php 
include "../functions/instructor_function.php";
include "../functions/account.php";
session_start();
$instructor = new instructor();
$account = new account();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Instructor Panel</title>
    <link rel="stylesheet" href="index.css" /> <!-- linked external CSS file -->
</head>
<body>
    <div class="header"> <!-- updated: header bar with left/right layout and no extra div -->
        <div class="logo">University of Saint Louis</div> <!-- left-aligned school name -->
        <form method="post" style="margin:0;">
            <button type="submit" name="logout" class="logout-button">Logout</button> <!-- right-aligned logout -->
        </form>
    </div>

<?php 
if (isset($_POST['logout'])) {
    header("Location: ../account/login.php");
    exit();
}
// moved user-info outside the header if needed
?>

    <div class="container">
        <div class="sidebar">
            <button onclick="loadContent('grading-sheet.php')">Grading Sheet</button>
            <button onclick="loadContent('class-list.php')">Class List</button>
        </div>
        <div class="main" id="center-content">
            Please select an option from the left.
        </div>
    </div>

   <script>
    function loadContent(page) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.includes("__SESSION_EXPIRED__")) {
                // Redirect the whole browser to login
                window.location.href = "../account/login.php";
            } else {
                document.getElementById("center-content").innerHTML = this.responseText;
            }
        }
    };
    xhttp.open("GET", page, true);
    xhttp.send();
}
    </script>
</body>
</html>
