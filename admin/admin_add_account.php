<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['ROLE']) || !in_array($_SESSION['ROLE'], ['management', 'admin'])) {
    header('Location: ../account/login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountId  = trim($_POST['account_id'] ?? '');
    $firstName  = trim($_POST['fname'] ?? '');
    $middleName = trim($_POST['mname'] ?? '');
    $lastName   = trim($_POST['lname'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $role       = trim($_POST['role'] ?? '');
    $track      = trim($_POST['track'] ?? '');

    if ($accountId === '' || $firstName === '' || $lastName === '' || $password === '' || $role === '') {
        $message = 'Please fill in all required fields.';
    } else {
        try {
            $portalDB->addAccount($accountId, $firstName, $middleName, $lastName, $password, $role, $track);
            header('Location: admin_dashboard.php');
            exit;
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Account</title>
    <link rel="stylesheet" href="add_account.css">
</head>

<body>
    <h2 class="page-title">Add Account</h2>

    <?php if ($message): ?>
        <div class="alert-error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post" class="form-box">
        <label>Account ID</label>
        <input name="account_id" type="text">

        <label>First Name</label>
        <input name="fname" type="text">

        <label>Middle Name</label>
        <input name="mname" type="text">

        <label>Last Name</label>
        <input name="lname" type="text">

        <label>Password</label>
        <input name="password" type="text">

        <label>Role</label>
        <select name="role">
            <option value="">Select Role</option>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
            <option value="management">Management</option>
        </select>

        <label>Track</label>
        <select name="track">
            <option value="">None</option>
            <option value="kinder">Kinder</option>
            <option value="junior high school">Junior High School</option>
            <option value="senior high school">Senior High School</option>
        </select>

        <button type="submit" class="btn-submit">Save</button>
    </form>

    <p class="back-link"><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
