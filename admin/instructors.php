<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') { http_response_code(403); echo "Access Denied."; exit; }

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");

// --- 1. HANDLE UPDATE (Via JS Fetch) ---
if (isset($_POST['update_id'])) {
    $sql = "UPDATE account SET degree = ?, status = ?, years_active = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([
        $_POST['degree'], 
        $_POST['status'], 
        $_POST['years_active'], 
        $_POST['update_id']
    ])) {
        echo "UPDATED";
    } else {
        echo "ERROR";
    }
    exit;
}

// --- 2. AJAX SEARCH HANDLER ---
if (isset($_GET['ajax_search'])) {
    $track  = $_GET['track'] ?? '';
    $degree = $_GET['degree'] ?? '';
    $status = $_GET['status'] ?? '';

    $sql = "SELECT * FROM account WHERE role = 'instructor'";
    $params = [];

    if (!empty($track)) { $sql .= " AND track = :t"; $params[':t'] = $track; }
    if (!empty($degree)) { $sql .= " AND degree = :d"; $params[':d'] = $degree; }
    if (!empty($status)) { $sql .= " AND status = :s"; $params[':s'] = $status; }

    $sql .= " ORDER BY lname ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(empty($list)) {
        echo '<tr><td colspan="6" style="text-align:center; padding:20px; color:#888;">No instructors match filters.</td></tr>';
    } else {
        foreach($list as $i) {
            $track_color = ($i['track']=='kinder') ? '#e8f5e9' : (($i['track']=='junior high school') ? '#e3f2fd' : '#fff3cd');
            $status_color = ($i['status']=='Active') ? 'color:#198754; font-weight:bold;' : 'color:#dc3545; font-weight:bold;';
            ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($i['lname'] . ', ' . $i['fname']); ?></strong><br>
                    <small style="color:#666;"><?php echo htmlspecialchars($i['account_id']); ?></small>
                </td>
                <td>
                    <span style="background:<?php echo $track_color; ?>; padding:3px 8px; border-radius:4px; font-size:0.85em;">
                        <?php echo htmlspecialchars(ucfirst($i['track'])); ?>
                    </span>
                </td>
                
                <td>
                    <select class="mini-select val-degree">
                        <option value="Bachelor" <?php if($i['degree']=='Bachelor') echo 'selected'; ?>>Bachelor</option>
                        <option value="Master" <?php if($i['degree']=='Master') echo 'selected'; ?>>Master</option>
                        <option value="PhD" <?php if($i['degree']=='PhD') echo 'selected'; ?>>PhD</option>
                    </select>
                </td>
                
                <td>
                    <input type="number" class="mini-input val-years" value="<?php echo $i['years_active']; ?>" placeholder="0" min="0">
                </td>

                <td>
                    <select class="mini-select val-status" style="<?php echo $status_color; ?>">
                        <option value="Active" <?php if($i['status']=='Active') echo 'selected'; ?>>Active</option>
                        <option value="Inactive" <?php if($i['status']=='Inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </td>
                
                <td>
                    <button type="button" class="btn-mini" onclick="updateInstructor(this, <?php echo $i['id']; ?>)">Update</button>
                </td>
            </tr>
            <?php
        }
    }
    exit; // Stop here
}
?>

<div class="form-card" style="max-width: 1200px;">
    <h2 style="color:#002D72; border-bottom:2px solid #eee; padding-bottom:10px;">Faculty List (Instructors)</h2>

    <div style="background:#f9f9f9; padding:15px; margin-bottom:20px; border-radius:5px; display:flex; gap:10px; flex-wrap:wrap;">
        <select id="f_track" onchange="filterFaculty()" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex:1;">
            <option value="">All Tracks</option>
            <option value="kinder">Kinder</option>
            <option value="junior high school">Junior High School</option>
            <option value="senior high school">Senior High School</option>
        </select>

        <select id="f_degree" onchange="filterFaculty()" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex:1;">
            <option value="">All Degrees</option>
            <option value="Bachelor">Bachelor</option>
            <option value="Master">Master</option>
            <option value="PhD">PhD</option>
        </select>

        <select id="f_status" onchange="filterFaculty()" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex:1;">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>

    <style>
        .fac-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .fac-table th { background: #002D72; color: white; padding: 12px; text-align: left; }
        .fac-table td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .fac-table tr:hover { background: #f9f9f9; }
        
        .mini-select { padding: 5px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; width: 100%; }
        .mini-input { padding: 5px; border: 1px solid #ddd; border-radius: 4px; width: 60px; text-align: center; }
        
        .btn-mini { padding: 6px 12px; background: #002D72; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem; }
        .btn-mini:hover { background: #004099; }
    </style>

    <div style="overflow-x:auto;">
        <table class="fac-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Name</th>
                    <th style="width: 15%;">Track</th>
                    <th style="width: 15%;">Degree</th>
                    <th style="width: 10%;">Years</th>
                    <th style="width: 20%;">Status</th>
                    <th style="width: 15%;">Action</th>
                </tr>
            </thead>
            <tbody id="faculty_table_body">
                </tbody>
        </table>
    </div>
</div>