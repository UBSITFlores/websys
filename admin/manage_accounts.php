<?php
session_start();

// 1. SECURITY
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') {
    http_response_code(403); echo "Access Denied."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- HANDLE ACTIONS ---

// A. DELETE USER
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM account WHERE id = :id");
    if($stmt->execute([':id' => $_POST['delete_id']])) {
        echo "DELETED"; // JS will handle reload
    }
    exit;
}

// B. UPDATE USER
if (isset($_POST['update_user'])) {
    $id = $_POST['db_id'];
    $pass = trim($_POST['password']);
    try {
        if (!empty($pass)) {
            $sql = "UPDATE account SET fname=?, mname=?, lname=?, role=?, track=?, password=? WHERE id=?";
            $params = [$_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['role'], $_POST['track'], $pass, $id];
        } else {
            $sql = "UPDATE account SET fname=?, mname=?, lname=?, role=?, track=? WHERE id=?";
            $params = [$_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['role'], $_POST['track'], $id];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo "<script>alert('User Updated Successfully!'); loadZone('manage_accounts.php');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
    exit;
}

// --- AJAX SEARCH HANDLER ---
if (isset($_GET['ajax_search'])) {
    $search = $_GET['search'] ?? '';
    $role   = $_GET['role'] ?? '';

    $sql = "SELECT * FROM account WHERE 1=1";
    $params = [];

    if (!empty($role)) { $sql .= " AND role = :r"; $params[':r'] = $role; }
    if (!empty($search)) { $sql .= " AND (fname LIKE :s OR lname LIKE :s OR account_id LIKE :s)"; $params[':s'] = "%$search%"; }

    $sql .= " ORDER BY id DESC LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(empty($results)) {
        echo '<tr><td colspan="5" style="text-align:center; padding:20px; color:#888;">No matching records found.</td></tr>';
    } else {
        foreach($results as $u) {
            echo '<tr>';
            echo '<td style="font-weight:bold;">' . htmlspecialchars($u['account_id']) . '</td>';
            echo '<td>' . htmlspecialchars($u['lname'] . ', ' . $u['fname']) . '</td>';
            echo '<td><span class="role-badge badge-' . $u['role'] . '">' . strtoupper($u['role']) . '</span></td>';
            echo '<td>' . htmlspecialchars($u['track']) . '</td>';
            echo '<td>';
            echo '<button class="action-btn btn-edit" onclick="loadZone(\'manage_accounts.php?edit_id=' . $u['id'] . '\')">Edit</button> ';
            echo '<button class="action-btn btn-del" onclick="deleteUser(' . $u['id'] . ')">Delete</button>';
            echo '</td>';
            echo '</tr>';
        }
    }
    exit;
}

// --- EDIT MODE ---
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM account WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <div class="form-card">
        <div style="display:flex; justify-content:space-between;">
            <h2>Edit User: <?php echo htmlspecialchars($user['account_id']); ?></h2>
            <button onclick="loadZone('manage_accounts.php')" style="background:#7f8c8d; border:none; color:white; padding:5px 10px; border-radius:4px; cursor:pointer;">Cancel</button>
        </div>
        <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'manage_accounts.php');">
            <input type="hidden" name="db_id" value="<?php echo $user['id']; ?>">
            <input type="hidden" name="update_user" value="1">
            
            <div class="form-group">
                <label>Full Name</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <input type="text" name="fname" value="<?php echo $user['fname']; ?>">
                    <input type="text" name="mname" value="<?php echo $user['mname']; ?>">
                    <input type="text" name="lname" value="<?php echo $user['lname']; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Role & Track</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <select name="role">
                        <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                        <option value="management" <?php if($user['role']=='management') echo 'selected'; ?>>Management</option>
                        <option value="instructor" <?php if($user['role']=='instructor') echo 'selected'; ?>>Instructor</option>
                        <option value="student" <?php if($user['role']=='student') echo 'selected'; ?>>Student</option>
                    </select>
                    <select name="track">
                        <option value="">-- None --</option>
                        <option value="kinder" <?php if($user['track']=='kinder') echo 'selected'; ?>>Kinder</option>
                        <option value="junior high school" <?php if($user['track']=='junior high school') echo 'selected'; ?>>JHS</option>
                        <option value="senior high school" <?php if($user['track']=='senior high school') echo 'selected'; ?>>SHS</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Reset Password (Leave blank to keep current)</label>
                <input type="text" name="password" placeholder="New password...">
            </div>
            <button type="submit" class="btn-save">Save Changes</button>
        </form>
    </div>
    <?php exit; 
}
?>

<div class="form-card" style="max-width: 1000px; padding: 20px;">
    <h2 style="color:#c0392b; border-bottom:2px solid #eee; padding-bottom:10px;">Manage Accounts</h2>

    <div style="background:#f9f9f9; padding:15px; margin-bottom:20px; border-radius:5px; display:flex; gap:10px;">
        <select id="search_role" onchange="liveSearch()" style="padding:8px; border:1px solid #ddd; border-radius:4px;">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="management">Management</option>
            <option value="instructor">Instructor</option>
            <option value="student">Student</option>
        </select>
        <input type="text" id="search_text" oninput="liveSearch()" placeholder="Type Name or ID to search..." style="padding:8px; border:1px solid #ddd; border-radius:4px; flex:1;">
    </div>

    <style>
        .acc-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .acc-table th { background: #c0392b; color: white; padding: 10px; text-align: left; }
        .acc-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .acc-table tr:hover { background: #fdf2f0; }
        .role-badge { padding: 3px 8px; border-radius: 4px; font-size: 0.8em; text-transform: uppercase; font-weight: bold; }
        .badge-admin { background: #2c3e50; color: white; }
        .badge-management { background: #d35400; color: white; }
        .badge-instructor { background: #27ae60; color: white; }
        .badge-student { background: #2980b9; color: white; }
        .action-btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; color: white; }
        .btn-edit { background: #f39c12; }
        .btn-del { background: #c0392b; }
    </style>

    <div style="overflow-x:auto;">
        <table class="acc-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Track</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="account_table_body">
                </tbody>
        </table>
    </div>
</div>