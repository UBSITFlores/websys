<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'management') {
    header('Location: index.php');
    exit;
}

$filterRole = $_GET['role'] ?? 'all';
$searchTerm = trim($_GET['search'] ?? '');

$sqlBase = "SELECT * FROM account";
$whereClauses = [];
$sqlParams = [];

if ($filterRole !== 'all' && $filterRole !== '') {
    $whereClauses[] = "role = :role";
    $sqlParams[':role'] = $filterRole;
}

if ($searchTerm !== '') {
    $whereClauses[] = "(account_id LIKE :search OR fname LIKE :search OR lname LIKE :search)";
    $sqlParams[':search'] = "%" . $searchTerm . "%";
}

$sqlFinal = $sqlBase;
if ($whereClauses) {
    $sqlFinal .= " WHERE " . implode(" AND ", $whereClauses);
}
$sqlFinal .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sqlFinal);
if ($sqlParams) {
    $stmt->execute($sqlParams);
} else {
    $stmt->execute();
}

$totalAccounts = $pdo->query("SELECT COUNT(*) FROM account")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM account WHERE role = 'student'")->fetchColumn();
$totalInstructors = $pdo->query("SELECT COUNT(*) FROM account WHERE role = 'instructor'")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM account WHERE role = 'management'")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head><title>Admin Dashboard</title></head>
<body style="font-family: Arial, sans-serif; background:#f5f6fa; margin:0; padding:0;">
<div style="background:#2c3e50; color:#fff; padding:15px 30px; display:flex; justify-content:space-between; align-items:center;">
  <div>
    <h2 style="margin:0;">School Admin Portal</h2>
    <p style="margin:2px 0 0 0; font-size:13px;">Manage students, instructors, and accounts</p>
  </div>
  <div style="text-align:right;">
    <p style="margin:0;">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['first_name']); ?></strong></p>
    <a href="logout.php" style="color:#fff; text-decoration:none; font-size:13px;">Logout</a>
  </div>
</div>

<div style="padding: 20px 30px;">
  <div style="margin-bottom:15px;">
    <a href="admin_add_account.php" style="background:#27ae60; color:#fff; padding:8px 14px; border-radius:3px; text-decoration:none; font-size:14px; margin-right:10px;">
      + Add Account
    </a>
    <a href="admin_dashboard.php?role=student" style="background:#2980b9; color:#fff; padding:6px 10px; border-radius:3px; text-decoration:none; font-size:12px; margin-right:5px;">View Students</a>
    <a href="admin_dashboard.php?role=instructor" style="background:#8e44ad; color:#fff; padding:6px 10px; border-radius:3px; text-decoration:none; font-size:12px; margin-right:5px;">View Instructors</a>
    <a href="admin_dashboard.php?role=management" style="background:#d35400; color:#fff; padding:6px 10px; border-radius:3px; text-decoration:none; font-size:12px; margin-right:5px;">View Admins</a>
    <a href="admin_dashboard.php" style="background:#7f8c8d; color:#fff; padding:6px 10px; border-radius:3px; text-decoration:none; font-size:12px;">Reset Filters</a>
  </div>

  <div style="background:#fff; padding:10px 15px; border-radius:4px; margin-bottom:15px; border:1px solid #ddd;">
    <form method="get" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
      <div>
        <label style="font-size:13px;" for="roleFilter">Role:</label><br>
        <select id="roleFilter" name="role" style="padding:4px 6px; font-size:13px;">
          <option value="all" <?php if ($filterRole=='all') echo 'selected'; ?>>All</option>
          <option value="student" <?php if ($filterRole=='student') echo 'selected'; ?>>Student</option>
          <option value="instructor" <?php if ($filterRole=='instructor') echo 'selected'; ?>>Instructor</option>
          <option value="management" <?php if ($filterRole=='management') echo 'selected'; ?>>Management</option>
        </select>
      </div>
      <div>
        <label style="font-size:13px;" for="searchInput">Search (ID/Name):</label><br>
        <input id="searchInput" type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" style="padding:4px 6px; font-size:13px; width:200px;">
      </div>
      <div style="margin-top: 17px;">
        <button type="submit" style="background:#3498db; color:#fff; border:none; padding:6px 12px; border-radius:3px; font-size:13px; cursor:pointer;">Apply</button>
      </div>
    </form>
  </div>

  <div style="background:#fff; border-radius:4px; border:1px solid #ddd; padding:10px 15px;">
    <h3 style="margin-top:0; font-size:18px;">Accounts List</h3>
    <div style="overflow-x:auto;">
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="background:#ecf0f1;">
            <th style="border:1px solid #bdc3c7; padding:6px;">ID</th>
            <th style="border:1px solid #bdc3c7; padding:6px;">Account ID</th>
            <th style="border:1px solid #bdc3c7; padding:6px;">Full Name</th>
            <th style="border:1px solid #bdc3c7; padding:6px;">Role</th>
            <th style="border:1px solid #bdc3c7; padding:6px;">Track</th>
            <th style="border:1px solid #bdc3c7; padding:6px;">Date Enrolled</th>
            <th style="border:1px solid #bdc3c7; padding:6px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($account = $stmt->fetch()): ?>
          <tr>
            <td style="border:1px solid #ecf0f1; padding:6px; text-align:center;"><?php echo htmlspecialchars($account['id']); ?></td>
            <td style="border:1px solid #ecf0f1; padding:6px;"><?php echo htmlspecialchars($account['account_id']); ?></td>
            <td style="border:1px solid #ecf0f1; padding:6px;"><?php echo htmlspecialchars($account['fname'] . ' ' . $account['lname']); ?></td>
            <td style="border:1px solid #ecf0f1; padding:6px; text-transform:capitalize;"><?php echo htmlspecialchars($account['role']); ?></td>
            <td style="border:1px solid #ecf0f1; padding:6px;"><?php echo htmlspecialchars($account['track']); ?></td>
            <td style="border:1px solid #ecf0f1; padding:6px;"><?php echo htmlspecialchars($account['date_enrolled']); ?></td>
            <td style="border:1px solid #ecf0f1; padding:6px;">
              <a href="admin_edit_account.php?id=<?php echo $account['id']; ?>" style="color:#2980b9; font-size:12px; margin-right:5px; text-decoration:none;">Edit</a>
              <a href="admin_delete_account.php?id=<?php echo $account['id']; ?>" onclick="return confirm('Confirm delete?')" style="color:#c0392b; font-size:12px; text-decoration:none;">Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
