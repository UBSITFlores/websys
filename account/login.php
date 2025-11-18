<?php 
    session_start();
    include "../functions/account.php";
    $account = new account();

    if(isset($_POST['submit'])){
        $accountid = $_POST['accountid'];
        $password = $_POST['password'];
        $account->login($accountid, $password);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacdal Portal</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header">
                <a href="index.php" class="back-btn" title="Back to portal">
                    <img src="back-arrow.png" alt="Back" class="back-img">
                </a>
                <span class="login-title">Login</span>
            </div>
            <form method="POST" class="login-form">
                <label for="accountid">Account ID</label>
                <input type="text" name="accountid" id="accountid" required>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <input type="submit" name="submit" value="Login" class="form-btn main-btn">
            </form>
        </div>
    </div>
</body>
</html>
