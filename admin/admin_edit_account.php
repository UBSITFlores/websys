<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['ROLE']) || !in_array($_SESSION['ROLE'], ['management', 'admin'])) {
    header('Location: ../account/login.php');
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
    <link rel="stylesheet" href="admin_edit_account.css">
</head>

<body>

    <h2 class="title">Edit Account</h2>

    <?php if ($message): ?>
        <div class="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post" class="edit-box">

        <p class="acc-id">Account ID: <strong><?php echo htmlspecialchars($accountData['account_id']); ?></strong></p>

        <label>First Name</label>
        <input name="fname" type="text" value="<?php echo htmlspecialchars($accountData['fname']); ?>">

        <label>Middle Name</label>
        <input name="mname" type="text" value="<?php echo htmlspecialchars($accountData['mname']); ?>">

        <label>Last Name</label>
        <input name="lname" type="text" value="<?php echo htmlspecialchars($accountData['lname']); ?>">

        <label>Password</label>
        <input name="password" type="text" value="<?php echo htmlspecialchars($accountData['password']); ?>">

        <label>Role</label>
        <select name="role">
            <option value="student" <?php if ($accountData['role']=='student') echo 'selected'; ?>>Student</option>
            <option value="instructor" <?php if ($accountData['role']=='instructor') echo 'selected'; ?>>Instructor</option>
            <option value="management" <?php if ($accountData['role']=='management') echo 'selected'; ?>>Management</option>
        </select>

        <label>Track</label>
        <select name="track">
            <option value="" <?php if ($accountData['track']=='') echo 'selected'; ?>>None</option>
            <option value="kinder" <?php if ($accountData['track']=='kinder') echo 'selected'; ?>>Kinder</option>
            <option value="junior high school" <?php if ($accountData['track']=='junior high school') echo 'selected'; ?>>Junior High School</option>
            <option value="senior high school" <?php if ($accountData['track']=='senior high school') echo 'selected'; ?>>Senior High School</option>
        </select>

        <button type="submit" class="btn-save">Update</button>
    </form>

    <p class="back"><a href="admin_dashboard.php">← Back to Dashboard</a></p>

</body>
</html>
