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
        echo '<tr><td colspan="6" class="no-results">No matching records found.</td></tr>';
    } else {
        foreach($results as $u) {
            ?>
            <tr>
                <td class="account-id"><?php echo htmlspecialchars($u['account_id']); ?></td>
                <td class="account-name"><?php echo htmlspecialchars($u['lname'] . ', ' . $u['fname']); ?></td>
                <td><span class="role-badge badge-<?php echo $u['role']; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                <td class="account-grade">
                    <?php 
                        if($u['role'] == 'student') echo htmlspecialchars($u['grade_level'] ?? 'N/A');
                        else echo htmlspecialchars($u['track']);
                    ?>
                </td>
                <td class="account-actions">
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

<link rel="stylesheet" href="manage_accounts.css">

<div class="form-card manage-card">
    <h2>Manage Accounts</h2>

    <div class="filter-container">
        <input type="hidden" id="search_role" value="">
        <button class="filter-btn filter-all" onclick="setRoleFilter('', this)">All</button>
        <button class="filter-btn filter-student" onclick="setRoleFilter('student', this)">Students</button>
        <button class="filter-btn filter-instructor" onclick="setRoleFilter('instructor', this)">Instructors</button>
        <button class="filter-btn filter-management" onclick="setRoleFilter('management', this)">Management</button>
        <button class="filter-btn filter-admin" onclick="setRoleFilter('admin', this)">Admins</button>
    </div>

    <div class="filter-inputs">
        <select id="search_grade" onchange="liveSearch()" class="grade-select">
            <option value="">-- All Grades --</option>
            <option value="Kinder">Kindergarten</option>
            <option value="Grade 7">Grade 7</option>
            <option value="Grade 8">Grade 8</option>
            <option value="Grade 9">Grade 9</option>
            <option value="Grade 10">Grade 10</option>
            <option value="Grade 11">Grade 11</option>
            <option value="Grade 12">Grade 12</option>
        </select>

        <input type="text" id="search_text" oninput="liveSearch()" placeholder="Search Name or ID..." class="search-input">
    </div>

    <div class="overflow-x-auto">
        <table class="acc-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Track / Grade</th> 
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="account_table_body">
                <tr class="loading-row"><td colspan="5" class="loading-text">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>