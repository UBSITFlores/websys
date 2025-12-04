<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') {
    http_response_code(403); echo "Access Denied."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// HANDLE SAVE
if (isset($_POST['update_settings'])) {
    $sy = trim($_POST['current_year'] ?? '');
    $status = trim($_POST['enrollment_status'] ?? 'Open');
    $quarter = (int)($_POST['active_quarter'] ?? 1);

    try {
        // Check if a settings row exists
        $existing = $pdo->query("SELECT id FROM school_settings LIMIT 1")->fetchColumn();
        if ($existing) {
            $sql = "UPDATE school_settings SET current_year = ?, enrollment_status = ?, active_quarter = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([$sy, $status, $quarter, $existing]);
        } else {
            $sql = "INSERT INTO school_settings (current_year, enrollment_status, active_quarter) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([$sy, $status, $quarter]);
        }

        if ($ok) {
            echo "<script>alert('System Settings Updated!'); loadZone('settings.php');</script>";
        } else {
            echo "<script>alert('Failed to save settings.');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }

    exit;
}

// FETCH CURRENT SETTINGS
$stmt = $pdo->query("SELECT * FROM school_settings LIMIT 1");
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

<style>
/* --- INLINE STYLES FOR SETTINGS PAGE --- */
.form-card {
    max-width: 700px;
    margin: 0 auto;
    background: #fff;
    padding: 35px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.page-title {
    color: #002D72;
    border-bottom: 3px solid #febb3f;
    padding-bottom: 15px;
    margin-top: 0;
    margin-bottom: 25px;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-title::before {
    content: "⚙️";
    font-size: 2rem;
}

.settings-intro {
    background: #eef2ff;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border-left: 4px solid #002D72;
}

.settings-intro p {
    margin: 0;
    color: #555;
    font-size: 0.95rem;
    line-height: 1.6;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #002D72;
    margin-bottom: 8px;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 1rem;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #002D72;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.1);
}

.info-hint {
    color: #666;
    font-size: 0.85rem;
    margin-top: 6px;
    display: block;
    line-height: 1.5;
}

.setting-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
    transition: all 0.3s;
}

.setting-card:hover {
    border-color: #002D72;
    box-shadow: 0 2px 8px rgba(0,45,114,0.1);
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 700;
    margin-left: 10px;
}

.badge-open {
    background: #d1e7dd;
    color: #0f5132;
}

.badge-closed {
    background: #f8d7da;
    color: #842029;
}

.quarter-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 10px;
}

.quarter-option {
    padding: 10px;
    background: #e9ecef;
    border-radius: 6px;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 600;
    color: #495057;
}

.quarter-option.active {
    background: #002D72;
    color: white;
}

.btn-save {
    background: #002D72;
    color: white;
    padding: 14px 28px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1.05rem;
    transition: all 0.2s;
    width: 100%;
    margin-top: 10px;
}

.btn-save:hover {
    background: #004099;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,45,114,0.2);
}

.btn-save:active {
    transform: translateY(0);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .quarter-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-card {
        padding: 20px;
    }
}
</style>

<div class="form-card">
    <h2 class="page-title">System Configuration</h2>
    
    <div class="settings-intro">
        <p>
            <strong>⚠️ Important:</strong> These settings affect the entire school system. 
            Changes to the school year will impact student ID generation and enrollment periods.
        </p>
    </div>
    
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'settings.php');">
        <input type="hidden" name="update_settings" value="1">

        <div class="setting-card">
            <div class="form-group">
                <label>📅 Current School Year</label>
                <input type="text" 
                       name="current_year" 
                       value="<?php echo htmlspecialchars($settings['current_year']); ?>" 
                       placeholder="e.g. 2025-2026" 
                       pattern="\d{4}-\d{4}"
                       required>
                <small class="info-hint">
                    Format: YYYY-YYYY (e.g. 2025-2026)<br>
                    Changing this will update the prefix for NEW Student IDs.
                </small>
            </div>
        </div>

        <div class="setting-card">
            <div class="form-group">
                <label>
                    🎓 Enrollment Status
                    <?php if($settings['enrollment_status'] == 'Open'): ?>
                        <span class="status-badge badge-open">OPEN</span>
                    <?php else: ?>
                        <span class="status-badge badge-closed">CLOSED</span>
                    <?php endif; ?>
                </label>
                <select name="enrollment_status">
                    <option value="Open" <?php if($settings['enrollment_status']=='Open') echo 'selected'; ?>>
                        ✅ Open (Accepting Students)
                    </option>
                    <option value="Closed" <?php if($settings['enrollment_status']=='Closed') echo 'selected'; ?>>
                        🚫 Closed (Not Accepting)
                    </option>
                </select>
                <small class="info-hint">
                    Controls whether new students can enroll in the system
                </small>
            </div>
        </div>

        <div class="setting-card">
            <div class="form-group">
                <label>📊 Active Grading Period</label>
                <select name="active_quarter">
                    <option value="1" <?php if($settings['active_quarter']==1) echo 'selected'; ?>>
                        Q1 - 1st Quarter / Prelims
                    </option>
                    <option value="2" <?php if($settings['active_quarter']==2) echo 'selected'; ?>>
                        Q2 - 2nd Quarter / Midterms
                    </option>
                    <option value="3" <?php if($settings['active_quarter']==3) echo 'selected'; ?>>
                        Q3 - 3rd Quarter / Pre-Finals
                    </option>
                    <option value="4" <?php if($settings['active_quarter']==4) echo 'selected'; ?>>
                        Q4 - 4th Quarter / Finals
                    </option>
                </select>
                <small class="info-hint">
                    This determines which quarter grades instructors can currently input
                </small>
                
                <div class="quarter-grid">
                    <div class="quarter-option <?php if($settings['active_quarter']==1) echo 'active'; ?>">Q1</div>
                    <div class="quarter-option <?php if($settings['active_quarter']==2) echo 'active'; ?>">Q2</div>
                    <div class="quarter-option <?php if($settings['active_quarter']==3) echo 'active'; ?>">Q3</div>
                    <div class="quarter-option <?php if($settings['active_quarter']==4) echo 'active'; ?>">Q4</div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-save">💾 Save Configuration</button>
    </form>
</div>