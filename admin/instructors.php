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
        echo '<tr><td colspan="6" class="no-results-cell">No instructors match filters.</td></tr>';
    } else {
        foreach($list as $i) {
            $track_class = 'track-shs';
            if($i['track']=='kinder') $track_class = 'track-kinder';
            elseif($i['track']=='junior high school') $track_class = 'track-jhs';
            
            $status_class = ($i['status']=='Active') ? 'status-active' : 'status-inactive';
            ?>
            <tr>
                <td class="fac-name-cell">
                    <strong><?php echo htmlspecialchars($i['lname'] . ', ' . $i['fname']); ?></strong>
                    <span class="fac-id"><?php echo htmlspecialchars($i['account_id']); ?></span>
                </td>
                <td>
                    <span class="track-badge <?php echo $track_class; ?>">
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
                    <select class="mini-select val-status <?php echo $status_class; ?>">
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
    exit;
}
?>

<link rel="stylesheet" href="instructors.css">

<div class="form-card instructors-card">
    <h2>Faculty List (Instructors)</h2>

    <div class="filter-section">
        <select id="f_track" onchange="filterFaculty()" class="filter-select">
            <option value="">All Tracks</option>
            <option value="kinder">Kinder</option>
            <option value="junior high school">Junior High School</option>
            <option value="senior high school">Senior High School</option>
        </select>

        <select id="f_degree" onchange="filterFaculty()" class="filter-select">
            <option value="">All Degrees</option>
            <option value="Bachelor">Bachelor</option>
            <option value="Master">Master</option>
            <option value="PhD">PhD</option>
        </select>

        <select id="f_status" onchange="filterFaculty()" class="filter-select">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>

    <div class="overflow-x-auto">
        <table class="fac-table">
            <thead>
                <tr>
                    <th class="col-name">Name</th>
                    <th class="col-track">Track</th>
                    <th class="col-degree">Degree</th>
                    <th class="col-years">Years</th>
                    <th class="col-status">Status</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>
            <tbody id="faculty_table_body">
            </tbody>
        </table>
    </div>
</div>