<?php 
require_once "../functions/instructor_function.php";
require_once "../functions/account.php";
session_start();

$instructor = new Instructor();
$account    = new Account();

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../account/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Instructor Panel</title>
    <link rel="stylesheet" href="index.css" />
</head>
<body>
    <div class="header">
        <div class="logo">University of Saint Louis</div>
        <form method="post" style="margin:0;">
            <button type="submit" name="logout" class="logout-button">Logout</button>
        </form>
    </div>

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
        fetch(page)
            .then(res => res.text())
            .then(html => {
                if (html.includes("__SESSION_EXPIRED__")) {
                    window.location.href = "../account/login.php";
                } else {
                    document.getElementById("center-content").innerHTML = html;
                }
            })
            .catch(() => {
                document.getElementById("center-content").innerHTML = "Error loading content.";
            });
    }

    function loadContentWithParams(form) {
        const params = new URLSearchParams(new FormData(form)).toString();
        loadContent("grading-sheet.php?" + params);
        return false; // prevent normal form submit
    }
    </script>
</body>
</html>
