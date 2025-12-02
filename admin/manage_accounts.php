<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') {
    http_response_code(403); echo "Access Denied."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");

// ACTIONS (Delete/Update) - Same as before
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM account WHERE id = :id");
    if($stmt->execute([':id' => $_POST['delete_id']])) echo "DELETED";
    exit;
}
// (Update logic omitted for brevity, it remains the same)

// --- AJAX SEARCH ---
if (isset($_GET['ajax_search'])) {
    $search = $_GET['search'] ?? '';
    $role   = $_GET['role'] ?? '';
    $grade  = $_GET['grade'] ?? ''; // NEW FILTER

    // Base Query with JOIN to get Grade Level
    $sql = "SELECT a.*, s.grade_level 
            FROM account a 
            LEFT JOIN students s ON a.id = s.student_id 
            WHERE 1=1";
    
    $params = [];

    if (!empty($role)) { 
        $sql .= " AND a.role = :r"; 
        $params[':r'] = $role; 
    }
    
    // NEW: Filter by Grade Level
    if (!empty($grade)) {
        $sql .= " AND s.grade_level = :g";
        $params[':g'] = $grade;
    }

    if (!empty($search)) { 
        $sql .= " AND (a.account_id LIKE :s OR a.fname LIKE :s OR a.lname LIKE :s)"; 
        $params[':s'] = "%$search%"; 
    }

    $sql .= " ORDER BY a.id DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(empty($results)) {
        echo '<tr><td colspan="6" style="text-align:center; padding:20px; color:#888;">No matching records found.</td></tr>';
    } else {
        foreach($results as $u) {
            ?>
            <tr>
                <td style="font-weight:bold;"><?php echo htmlspecialchars($u['account_id']); ?></td>
                <td><?php echo htmlspecialchars($u['lname'] . ', ' . $u['fname']); ?></td>
                <td><span class="role-badge badge-<?php echo $u['role']; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                <td>
                    <?php 
                        if($u['role'] == 'student') echo htmlspecialchars($u['grade_level'] ?? 'N/A');
                        else echo htmlspecialchars($u['track']);
                    ?>
                </td>
                <td>
                    <button class="action-btn btn-edit" onclick="loadZone('manage_accounts.php?edit_id=<?php echo $u['id']; ?>')">Edit</button>
                    <button class="action-btn btn-del" onclick="deleteUser(<?php echo $u['id']; ?>)">Delete</button>
                </td>
            </tr>
            <?php
        }
    }
    exit;
}

// If edit requested, load the edit form (reuse admin_edit_account.php)
if (isset($_GET['edit_id'])) {
    // forward parameter expected by admin_edit_account.php
    $_GET['id'] = $_GET['edit_id'];
    include __DIR__ . '/admin_edit_account.php';
    exit;
}
?>

<div class="form-card" style="max-width: 1000px; padding: 20px;">
    <h2 style="color:#002D72; border-bottom:2px solid #eee; padding-bottom:10px;">Manage Accounts</h2>

    <div class="filter-container">
        <input type="hidden" id="search_role" value="">
        <button class="filter-btn filter-all" onclick="setRoleFilter('', this)">All</button>
        <button class="filter-btn filter-student" onclick="setRoleFilter('student', this)">Students</button>
        <button class="filter-btn filter-instructor" onclick="setRoleFilter('instructor', this)">Instructors</button>
        <button class="filter-btn filter-management" onclick="setRoleFilter('management', this)">Management</button>
        <button class="filter-btn filter-admin" onclick="setRoleFilter('admin', this)">Admins</button>
    </div>

    <div style="display:flex; gap:10px; margin-bottom:20px;">
        <select id="search_grade" onchange="liveSearch()" style="padding:10px; border:1px solid #ddd; border-radius:6px; min-width:150px;">
            <option value="">-- All Grades --</option>
            <option value="Kinder">Kindergarten</option>
            <option value="Grade 7">Grade 7</option>
            <option value="Grade 8">Grade 8</option>
            <option value="Grade 9">Grade 9</option>
            <option value="Grade 10">Grade 10</option>
            <option value="Grade 11">Grade 11</option>
            <option value="Grade 12">Grade 12</option>
        </select>

        <input type="text" id="search_text" oninput="liveSearch()" placeholder="Search Name or ID..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
    </div>

    <style>
        .acc-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .acc-table th { background: #002D72; color: white; padding: 10px; text-align: left; }
        .acc-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .role-badge { padding: 3px 8px; border-radius: 4px; font-size: 0.8em; text-transform: uppercase; font-weight: bold; }
        .badge-admin { background: #2c3e50; color: white; }
        .badge-management { background: #d35400; color: white; }
        .badge-instructor { background: #27ae60; color: white; }
        .badge-student { background: #2980b9; color: white; }
        .action-btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; color: white; }
        .btn-edit { background: #f39c12; }
        .btn-del { background: #dc3545; }
    </style>

    <div style="overflow-x:auto;">
        <table class="acc-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Track / Grade</th> <th>Action</th>
                </tr>
            </thead>
            <tbody id="account_table_body">
                <tr><td colspan="5" style="text-align:center; padding:20px;">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>