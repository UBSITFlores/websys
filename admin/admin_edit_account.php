<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'management') {
    header('Location: index.php');
    exit;
}

$accountId = $_GET['id'] ?? 0;
$stmt = $portalDB->getAccountById($accountId);
$accountData = $stmt->fetch();

if (!$accountData) {
    echo "Account not found.";
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName     = trim($_POST['fname'] ?? '');
    $middleName    = trim($_POST['mname'] ?? '');
    $lastName      = trim($_POST['lname'] ?? '');
    $password      = trim($_POST['password'] ?? '');
    $role          = trim($_POST['role'] ?? '');
    $track         = trim($_POST['track'] ?? '');

    if ($firstName === '' || $lastName === '' || $password === '' || $role === '') {
        $message = "Please fill in all required fields.";
    } else {
        try {
            $portalDB->updateAccount($accountId, $firstName, $middleName, $lastName, $password, $role, $track);
            header('Location: admin_dashboard.php');
            exit;
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Account</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f9f9f9; padding:40px; text-align:center;">
    <h2>Edit Account</h2>
    <?php if ($message): ?>
        <div style="color:red; font-weight: bold; margin-bottom: 15px;"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" action="" style="background:white; padding: 20px; border-radius: 8px; display:inline-block; min-width: 320px; text-align:left;">
        <p>Account ID: <strong><?php echo htmlspecialchars($accountData['account_id']); ?></strong></p>
        <label>First Name</label><br>
        <input name="fname" style="padding: 8px; width: 100%; margin-bottom: 10px;" type="text" value="<?php echo htmlspecialchars($accountData['fname']); ?>"><br>
        <label>Middle Name</label><br>
        <input name="mname" style="padding: 8px; width: 100%; margin-bottom: 10px;" type="text" value="<?php echo htmlspecialchars($accountData['mname']); ?>"><br>
        <label>Last Name</label><br>
        <input name="lname" style="padding: 8px; width: 100%; margin-bottom: 10px;" type="text" value="<?php echo htmlspecialchars($accountData['lname']); ?>"><br>
        <label>Password</label><br>
        <input name="password" style="padding: 8px; width: 100%; margin-bottom: 10px;" type="text" value="<?php echo htmlspecialchars($accountData['password']); ?>"><br>
        <label>Role</label><br>
        <select name="role" style="width: 100%; padding: 8px; margin-bottom: 10px;">
            <option value="student" <?php if ($accountData['role']=='student') echo 'selected'; ?>>Student</option>
            <option value="instructor" <?php if ($accountData['role']=='instructor') echo 'selected'; ?>>Instructor</option>
            <option value="management" <?php if ($accountData['role']=='management') echo 'selected'; ?>>Management</option>
        </select><br>
        <label>Track</label><br>
        <select name="track" style="width: 100%; padding: 8px; margin-bottom: 15px;">
            <option value="" <?php if ($accountData['track']=='') echo 'selected'; ?>>None</option>
            <option value="kinder" <?php if ($accountData['track']=='kinder') echo 'selected'; ?>>Kinder</option>
            <option value="junior high school" <?php if ($accountData['track']=='junior high school') echo 'selected'; ?>>Junior High School</option>
            <option value="senior high school" <?php if ($accountData['track']=='senior high school') echo 'selected'; ?>>Senior High School</option>
        </select><br>
        <button style="background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 4px;" type="submit">
            Update
        </button>
    </form>
    <p style="margin-top: 20px;"><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
