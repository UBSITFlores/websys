<?php
session_start();
require_once 'pdo_functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountId   = trim($_POST['account_id'] ?? '');
    $firstName   = trim($_POST['fname'] ?? '');
    $middleName  = trim($_POST['mname'] ?? '');
    $lastName    = trim($_POST['lname'] ?? '');
    $password    = trim($_POST['password'] ?? '');
    $track       = trim($_POST['track'] ?? '');

    if ($accountId === '' || $firstName === '' || $lastName === '' || $password === '') {
        $message = 'Please complete all required fields.';
    } else {
        try {
            $portalDB->addStudent($accountId, $firstName, $middleName, $lastName, $password, $track);
            $message = 'Registration successful! You may now log in.';
        } catch (PDOException $e) {
            $message = 'Error: '. $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f9f9f9; text-align:center; padding:40px;">
    <h2>Student Registration</h2>
    <?php if($message): ?>
        <div style="margin-bottom: 15px; font-weight: bold; color: <?php echo strpos($message,'Error')===0?'red':'green'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form method="post" action="" style="display:inline-block; background:white; padding: 20px; border-radius: 8px; text-align:left; min-width:320px;">
        <label>Student ID</label><br>
        <input style="width:100%; padding: 8px; margin-bottom: 10px;" type="text" name="account_id"><br>
        <label>First Name</label><br>
        <input style="width:100%; padding: 8px; margin-bottom: 10px;" type="text" name="fname"><br>
        <label>Middle Name</label><br>
        <input style="width:100%; padding: 8px; margin-bottom: 10px;" type="text" name="mname"><br>
        <label>Last Name</label><br>
        <input style="width:100%; padding: 8px; margin-bottom: 10px;" type="text" name="lname"><br>
        <label>Password</label><br>
        <input style="width:100%; padding: 8px; margin-bottom: 15px;" type="password" name="password"><br>
        <label>Track</label><br>
        <select name="track" style="width:100%; padding: 8px; margin-bottom: 15px;">
            <option value="kinder">Kinder</option>
            <option value="junior high school">Junior High School</option>
            <option value="senior high school">Senior High School</option>
            <option value="">None</option>
        </select><br>
        <button style="background:#27ae60; color:white; padding: 10px 20px; border:none; border-radius: 4px;" type="submit">
            Register
        </button>
    </form>
    <p style="margin-top: 15px;"><a href="index.php">Back to login</a></p>
</body>
</html>
