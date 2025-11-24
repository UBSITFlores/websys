<?php
session_start();
require_once 'pdo_functions.php';

$sessionRole = $_SESSION['role'] ?? $_SESSION['ROLE'] ?? null;
if (!$sessionRole || !in_array($sessionRole, ['admin','management'])) {
    header('Location: index.php');
    exit;
}

$filterRole = $_GET['role'] ?? 'all';
$searchTerm = trim($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f5f6fa; margin:0; padding:0; }
        .header { background:#2c3e50; color:#fff; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
        .header h2, .header p, .header a { margin:0; }
        .header p { font-size:13px; margin-top:2px; }
        .btn { padding:6px 12px; border-radius:3px; font-size:13px; cursor:pointer; text-decoration:none; }
        .btn-add { background:#27ae60; color:#fff; padding:8px 14px; font-size:14px; }
        .btn-student { background:#2980b9; color:#fff; }
        .btn-instructor { background:#8e44ad; color:#fff; }
        .btn-management { background:#d35400; color:#fff; }
        .btn-reset { background:#7f8c8d; color:#fff; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th, td { border:1px solid #bdc3c7; padding:6px; }
        thead tr { background:#ecf0f1; }
        tbody tr td { border:1px solid #ecf0f1; }
        .filter-container { background:#fff; padding:10px 15px; border-radius:4px; margin-bottom:15px; border:1px solid #ddd; display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end; }
        label { font-size:13px; display:block; margin-bottom:4px; }
        select, input[type="text"] { padding:4px 6px; font-size:13px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="header">
    <div>
        <h2>School Admin Portal</h2>
        <p>Manage students, instructors, and accounts</p>
    </div>
    <div style="text-align:right;">
        <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['FNAME'] ?? $_SESSION['fname'] ?? ''); ?></strong></p>
        <a href="logout.php" style="color:#fff; text-decoration:none; font-size:13px;">Logout</a>
    </div>
</div>

<div style="padding: 20px 30px;">

    <div style="margin-bottom:15px;">
        <a href="admin_add_account.php" class="btn btn-add">+ Add Account</a>
        <a href="admin_dashboard.php?role=student" class="btn btn-student">View Students</a>
        <a href="admin_dashboard.php?role=instructor" class="btn btn-instructor">View Instructors</a>
        <a href="admin_dashboard.php?role=management" class="btn btn-management">View Admins</a>
        <a href="admin_dashboard.php" class="btn btn-reset">Reset Filters</a>
    </div>

    <div class="filter-container">
        <div>
            <label for="roleFilter">Role:</label>
            <select id="roleFilter" name="role">
                <option value="all" <?php if ($filterRole == 'all') echo 'selected'; ?>>All</option>
                <option value="student" <?php if ($filterRole == 'student') echo 'selected'; ?>>Student</option>
                <option value="instructor" <?php if ($filterRole == 'instructor') echo 'selected'; ?>>Instructor</option>
                <option value="management" <?php if ($filterRole == 'management') echo 'selected'; ?>>Management</option>
            </select>
        </div>
        <div>
            <label for="searchInput">Search (ID/Name):</label>
            <input id="searchInput" type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" style="width:220px;">
        </div>
    </div>

    <div style="background:#fff; border-radius:4px; border:1px solid #ddd; padding:10px 15px;">
        <h3 style="margin-top:0; font-size:18px;">Accounts List</h3>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Account ID</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Track</th>
                        <th>Date Enrolled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="accountsTableBody">
                    <!-- AJAX-loaded rows will appear here -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
$(document).ready(function(){
    function fetchAccounts() {
        var role = $('#roleFilter').val();
        var search = $('#searchInput').val();

        $.ajax({
            url: 'fetch_accounts.php',
            type: 'GET',
            data: { ROLE: role, search: search },
            success: function(data) {
                $('#accountsTableBody').html(data);
            },
            error: function() {
                alert('Error fetching data.');
            }
        });
    }

    $('#roleFilter').on('change', fetchAccounts);
    $('#searchInput').on('input', fetchAccounts);

    fetchAccounts();
});
</script>
</body>
</html>
