<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') {
    http_response_code(403); echo "Access Denied."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// HANDLE SAVE
if (isset($_POST['update_settings'])) {
    $sy = $_POST['current_year'];
    $status = $_POST['enrollment_status'];
    $quarter = $_POST['active_quarter'];

    // Update the single row in school_settings
    // We use ID=1 assuming you ran the SQL to create the table and insert the first row
    $sql = "UPDATE school_settings SET current_year = ?, enrollment_status = ?, active_quarter = ? WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$sy, $status, $quarter])) {
        echo "<script>alert('System Settings Updated!'); loadZone('settings.php');</script>";
    }
    exit;
}

// FETCH CURRENT SETTINGS
// We try to fetch row 1. If it doesn't exist (fresh install), we use defaults.
$stmt = $pdo->query("SELECT * FROM school_settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$settings) {
    // Default fallback
    $settings = [
        'current_year' => date('Y').'-'.(date('Y')+1), 
        'enrollment_status' => 'Open', 
        'active_quarter' => 1
    ];
}
?>

<div class="form-card" style="max-width: 600px;">
    <h2 style="color:#002D72; border-bottom:2px solid #eee; padding-bottom:10px;">System Configuration</h2>
    
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'settings.php');">
        <input type="hidden" name="update_settings" value="1">

        <div class="form-group">
            <label>Current School Year</label>
            <input type="text" name="current_year" value="<?php echo htmlspecialchars($settings['current_year']); ?>" placeholder="e.g. 2025-2026" required>
            <small style="color:#666;">Changing this will update the prefix for NEW Student IDs.</small>
        </div>

        <div class="form-group">
            <label>Enrollment Status</label>
            <select name="enrollment_status">
                <option value="Open" <?php if($settings['enrollment_status']=='Open') echo 'selected'; ?>>Open (Accepting Students)</option>
                <option value="Closed" <?php if($settings['enrollment_status']=='Closed') echo 'selected'; ?>>Closed</option>
            </select>
        </div>

        <div class="form-group">
            <label>Active Grading Period</label>
            <select name="active_quarter">
                <option value="1" <?php if($settings['active_quarter']==1) echo 'selected'; ?>>1st Quarter / Prelims</option>
                <option value="2" <?php if($settings['active_quarter']==2) echo 'selected'; ?>>2nd Quarter / Midterms</option>
                <option value="3" <?php if($settings['active_quarter']==3) echo 'selected'; ?>>3rd Quarter / Pre-Finals</option>
                <option value="4" <?php if($settings['active_quarter']==4) echo 'selected'; ?>>4th Quarter / Finals</option>
            </select>
        </div>

        <button type="submit" class="btn-save">Save Configuration</button>
    </form>
</div>