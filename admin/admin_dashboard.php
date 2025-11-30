<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['ROLE']) || !in_array($_SESSION['ROLE'], ['admin'])) {
    header('Location: index.php');
    exit;
}

$filterRole = $_GET['ROLE'] ?? 'all';
$searchTerm = trim($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

<div class="header">
    <div>
        <h2>School Admin Portal</h2>
        <p>Manage students, instructors, and accounts</p>
    </div>

    <div class="user-panel">
        <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['FNAME']); ?></strong></p>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
</div>

<div class="page-wrapper">

    <div class="action-btns">
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
            <input id="searchInput" type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
        </div>
    </div>

    <div class="table-wrapper">
        <h3>Accounts List</h3>
        <div class="table-scroll">
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
                <tbody id="accountsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    function fetchAccounts() {
        $.ajax({
            url: 'fetch_accounts.php',
            type: 'GET',
            data: { 
                role: $('#roleFilter').val(), 
                search: $('#searchInput').val() 
            },
            success: function(data) { $('#accountsTableBody').html(data); },
            error: function() { alert('Error fetching data.'); }
        });
    }

    $('#roleFilter').change(fetchAccounts);
    $('#searchInput').on('input', fetchAccounts);
    fetchAccounts();
});
</script>

</body>
</html>
