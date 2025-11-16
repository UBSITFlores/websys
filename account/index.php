<?php 
    include '../functions/account.php';

    $account = new account();

    if(isset($_POST['login'])){
        header("Location: login.php");
    }
    if(isset($_POST['apply'])){
        header("Location: apply.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacdal Portal</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <header class="main-header">
        <div class="brand-bar">
            <!-- Optional: Replace src with the actual logo file if available -->
            <img src="school-logo.png" alt="School Logo" class="school-logo">
            <div class="site-info">
                <span class="school-name">Saint Louis School of Pacdal, Inc.</span>
                <span class="school-tagline">Transformative Education, Integral Faith, & Indigenous Formation</span>
            </div>
        </div>
    </header>
    <div class="container">
        <form method="POST" class="portal-form">
            <button type="submit" name="login" class="form-btn">Login</button>
            <button type="submit" name="apply" class="form-btn secondary">Apply as Student</button>
        </form>
    </div>
</body>
</html>
