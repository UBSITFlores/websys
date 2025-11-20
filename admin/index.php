<?php
session_start();
require_once 'pdo_functions.php';

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputAccountId = trim($_POST['account_id'] ?? '');
    $inputPassword  = trim($_POST['password'] ?? '');

    if ($inputAccountId === '' || $inputPassword === '') {
        $errorMessage = 'Please fill in both fields.';
    } else {
        $stmt = $portalDB->getUserByCredentials($inputAccountId, $inputPassword);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['account_id'] = $user['account_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['fname'];

            switch ($user['role']) {
                case 'student':
                    header('Location: student_dashboard.php');
                    break;
                case 'instructor':
                    header('Location: instructor_dashboard.php');
                    break;
                case 'management':
                    header('Location: admin_dashboard.php');
                    break;
                default:
                    $errorMessage = 'Unknown user role.';
            }
            exit;
        } else {
            $errorMessage = 'Invalid ID or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Portal Login</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f9f9f9; text-align:center; padding:40px;">
    <h2>Login</h2>
    <?php if($errorMessage): ?>
        <div style="color: red; font-weight: bold; margin-bottom: 10px;"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>
    <form method="post" action="" style="display: inline-block; background: white; padding: 20px; border-radius: 8px;">
        <label>Account ID</label><br>
        <input style="padding: 8px; width: 250px; margin-bottom: 10px;" type="text" name="account_id"><br>
        <label>Password</label><br>
        <input style="padding: 8px; width: 250px; margin-bottom: 20px;" type="password" name="password"><br>
        <button style="padding: 10px 30px; background-color: #27ae60; color: white; border: none; border-radius: 4px;"
                type="submit">Login</button>
    </form>
    <p style="margin-top: 15px;">New student? <a href="register.php">Register here</a></p>
</body>
</html>
