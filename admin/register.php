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
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <h2>Student Registration</h2>
    <?php if($message): ?>
        <div class="message <?php echo strpos($message,'Error')===0?'error':'success'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form method="post" action="" class="form-container">
        <label>Student ID</label>
        <input type="text" name="account_id">
        
        <label>First Name</label>
        <input type="text" name="fname">
        
        <label>Middle Name</label>
        <input type="text" name="mname">
        
        <label>Last Name</label>
        <input type="text" name="lname">
        
        <label>Password</label>
        <input type="password" name="password">
        
        <label>Track</label>
        <select name="track">
            <option value="kinder">Kinder</option>
            <option value="junior high school">Junior High School</option>
            <option value="senior high school">Senior High School</option>
            <option value="">None</option>
        </select>
        
        <button type="submit">Register</button>
    </form>
    <div class="back-link">
        <p><a href="index.php">Back to login</a></p>
    </div>
</body>
</html>
