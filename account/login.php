<!-- <?php 
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
    <title>University Portal - Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header">
                <span class="logo-text">University Portal</span>
            </div>
            
            <form method="POST" class="login-form">
                <div>
                    <label for="accountid">Account ID</label>
                    <input type="text" name="accountid" id="accountid" placeholder="Enter your ID" required>
                </div>
                
                <div>
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>

                <input type="submit" name="submit" value="Sign In" class="form-btn">
            </form>

            <div class="footer-links">
                <a href="#">Forgot Password?</a>
            </div>
        </div>
    </div>
</body>
</html> -->