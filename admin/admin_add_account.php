<?php
session_start();
require_once 'pdo_functions.php'; // creates $portalDB instance

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'management') {
    header('Location: index.php');
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
<head><title>Add Account</title></head>
<body style="font-family: Arial, sans-serif; background:#f9f9f9; padding:40px; text-align:center;">
<h2>Add Account</h2>
<?php if ($message): ?>
  <div style="color: red; font-weight: bold; margin-bottom: 15px;"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<form method="post" style="background: white; padding: 20px; border-radius: 8px; display: inline-block; text-align:left; max-width: 400px;">
  <label>Account ID</label><br>
  <input name="account_id" type="text" style="width: 100%; padding: 8px; margin-bottom:10px;"><br>
  <label>First Name</label><br>
  <input name="fname" type="text" style="width: 100%; padding: 8px; margin-bottom:10px;"><br>
  <label>Middle Name</label><br>
  <input name="mname" type="text" style="width: 100%; padding: 8px; margin-bottom:10px;"><br>
  <label>Last Name</label><br>
  <input name="lname" type="text" style="width: 100%; padding: 8px; margin-bottom:10px;"><br>
  <label>Password</label><br>
  <input name="password" type="text" style="width: 100%; padding: 8px; margin-bottom:10px;"><br>
  <label>Role</label><br>
  <select name="role" style="width: 100%; padding: 8px; margin-bottom:10px;">
    <option value="">Select Role</option>
    <option value="student">Student</option>
    <option value="instructor">Instructor</option>
    <option value="management">Management</option>
  </select><br>
  <label>Track</label><br>
  <select name="track" style="width: 100%; padding: 8px; margin-bottom:15px;">
    <option value="">None</option>
    <option value="kinder">Kinder</option>
    <option value="junior high school">Junior High School</option>
    <option value="senior high school">Senior High School</option>
  </select><br>
  <button type="submit" style="background: #27ae60; color: white; border:none; padding: 10px 20px; border-radius: 4px;">Save</button>
</form>
<p style="margin-top: 20px;"><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
