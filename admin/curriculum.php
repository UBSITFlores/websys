<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') { die("Access Denied."); }

try {
    $pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("DB Error"); }

// --- ADD ---
if (isset($_POST['add_subject'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO subjects (code, description, year_level, track, type, price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            strtoupper(trim($_POST['code'])), trim($_POST['description']), $_POST['year_level'], $_POST['track'], $_POST['type'], $_POST['price']
        ]);
        echo "<script>alert('Subject Added!'); loadZone('curriculum.php');</script>";
    } catch (Exception $e) { echo "<script>alert('Error: " . $e->getMessage() . "');</script>"; }
    exit;
}

// --- UPDATE ---
if (isset($_POST['update_subject'])) {
    try {
        $stmt = $pdo->prepare("UPDATE subjects SET code=?, description=?, year_level=?, track=?, type=?, price=? WHERE id=?");
        $stmt->execute([
            strtoupper(trim($_POST['code'])), trim($_POST['description']), $_POST['year_level'], $_POST['track'], $_POST['type'], $_POST['price'], $_POST['db_id']
        ]);
        echo "<script>alert('Updated!'); loadZone('curriculum.php');</script>";
    } catch (Exception $e) { echo "<script>alert('Error');</script>"; }
    exit;
}

// --- DELETE ---
if (isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM subjects WHERE id=?")->execute([$_POST['delete_id']]);
    echo "DELETED"; exit;
}

// --- FETCH ---
$edit_mode = false; $curr = [];
if(isset($_GET['edit_id'])) {
    $curr = $pdo->query("SELECT * FROM subjects WHERE id=" . $_GET['edit_id'])->fetch(PDO::FETCH_ASSOC);
    if($curr) $edit_mode = true;
}
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY track, year_level, code ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* --- INLINE STYLES FOR CURRICULUM PAGE --- */
.form-card {
    max-width: 1200px;
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
    content: "📚";
    font-size: 2rem;
}

/* FORM SECTION */
.form-section {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #dee2e6;
    transition: all 0.3s;
}

.form-section.edit-mode {
    background: #fff3cd;
    border-color: #ffc107;
}

.form-section h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #002D72;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 6px;
    font-size: 0.9rem;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 12px;
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

.form-grid-3 {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-grid-4 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 15px;
}

.btn-save {
    background: #002D72;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.2s;
    width: 100%;
}

.btn-save:hover {
    background: #004099;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,45,114,0.2);
}

.btn-save:active {
    transform: translateY(0);
}

/* FILTER TABS */
.filter-container {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.filter-btn {
    padding: 10px 24px;
    border: 2px solid #ccc;
    background: white;
    color: #555;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.85rem;
    transition: all 0.2s;
    text-transform: uppercase;
}

.filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.filter-btn.active {
    color: white;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}

.filter-all {
    border-color: #7f8c8d;
    color: #7f8c8d;
}

.filter-all:hover,
.filter-all.active {
    background: #7f8c8d;
    color: white;
    border-color: #7f8c8d;
}

.filter-regular {
    border-color: #3498db;
    color: #3498db;
}

.filter-regular:hover,
.filter-regular.active {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.filter-stem {
    border-color: #e74c3c;
    color: #e74c3c;
}

.filter-stem:hover,
.filter-stem.active {
    background: #e74c3c;
    color: white;
    border-color: #e74c3c;
}

.filter-abm {
    border-color: #27ae60;
    color: #27ae60;
}

.filter-abm:hover,
.filter-abm.active {
    background: #27ae60;
    color: white;
    border-color: #27ae60;
}

.filter-humss {
    border-color: #f39c12;
    color: #f39c12;
}

.filter-humss:hover,
.filter-humss.active {
    background: #f39c12;
    color: white;
    border-color: #f39c12;
}

/* TABLE */
.table-container {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.curr-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.curr-table thead tr {
    background: #002D72;
    color: white;
}

.curr-table th {
    padding: 14px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.curr-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

.curr-table tbody tr {
    transition: background 0.2s;
}

.curr-table tbody tr:hover {
    background: #f8f9fa;
}

.track-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.badge-regular {
    background: #d6eaf8;
    color: #1b4f72;
}

.badge-stem {
    background: #fadbd8;
    color: #922b21;
}

.badge-abm {
    background: #d5f4e6;
    color: #145a32;
}

.badge-humss {
    background: #fdebd0;
    color: #935116;
}

.subject-code {
    font-weight: bold;
    color: #002D72;
    font-size: 1rem;
}

.subject-desc {
    color: #666;
    font-size: 0.9rem;
}

.price-cell {
    color: #198754;
    font-weight: bold;
    font-size: 1.05rem;
}

.action-btn {
    padding: 6px 12px;
    margin-right: 5px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.1rem;
}

.action-btn.edit {
    color: #002D72;
}

.action-btn.edit:hover {
    background: #002D72;
    color: white;
    border-color: #002D72;
}

.action-btn.delete {
    color: #dc3545;
}

.action-btn.delete:hover {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.no-data {
    padding: 40px;
    text-align: center;
    color: #999;
    font-style: italic;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .form-grid-3,
    .form-grid-4 {
        grid-template-columns: 1fr;
    }

    .filter-container {
        gap: 8px;
    }

    .filter-btn {
        font-size: 0.75rem;
        padding: 8px 16px;
    }

    .curr-table {
        font-size: 0.85rem;
    }

    .curr-table th,
    .curr-table td {
        padding: 8px 6px;
    }

    .action-btn {
        padding: 4px 8px;
        font-size: 1rem;
    }
}
</style>

<div class="form-card">
    <h2 class="page-title">Curriculum Manager</h2>

    <!-- FORM -->
    <div class="form-section <?php echo $edit_mode ? 'edit-mode' : ''; ?>">
        <h3><?php echo $edit_mode ? '✏️ Edit Subject' : '➕ Add New Subject'; ?></h3>
        <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'curriculum.php');">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_subject" value="1">
                <input type="hidden" name="db_id" value="<?php echo $curr['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_subject" value="1">
            <?php endif; ?>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" name="code" value="<?php echo $curr['code']??''; ?>" placeholder="e.g. MATH101" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" value="<?php echo $curr['description']??''; ?>" placeholder="e.g. Basic Mathematics" required>
                </div>
                <div class="form-group">
                    <label>Subject Type</label>
                    <select name="type">
                        <option <?php if(($curr['type']??'')=='Core') echo 'selected'; ?>>Core</option>
                        <option <?php if(($curr['type']??'')=='Applied') echo 'selected'; ?>>Applied</option>
                        <option <?php if(($curr['type']??'')=='Specialized') echo 'selected'; ?>>Specialized</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-4">
                <div class="form-group">
                    <label>Track</label>
                    <select name="track">
                        <option <?php if(($curr['track']??'')=='Regular') echo 'selected'; ?>>Regular</option>
                        <option <?php if(($curr['track']??'')=='STEM') echo 'selected'; ?>>STEM</option>
                        <option <?php if(($curr['track']??'')=='ABM') echo 'selected'; ?>>ABM</option>
                        <option <?php if(($curr['track']??'')=='HUMSS') echo 'selected'; ?>>HUMSS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Year Level</label>
                    <select name="year_level">
                        <option <?php if(($curr['year_level']??'')=='Kinder') echo 'selected'; ?>>Kinder</option>
                        <?php for($i=7;$i<=12;$i++): ?>
                            <option value="Grade <?php echo $i; ?>" <?php if(($curr['year_level']??'')=="Grade $i") echo 'selected'; ?>>Grade <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tuition Price (₱)</label>
                    <input type="number" name="price" value="<?php echo $curr['price']??'0'; ?>" placeholder="0.00" step="0.01" required>
                </div>
                <div style="align-self:end;">
                    <button type="submit" class="btn-save">
                        <?php echo $edit_mode ? '💾 Save Changes' : '➕ Add Subject'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- FILTER TABS -->
    <div class="filter-container">
        <button class="filter-btn filter-all active" onclick="filterTrack('all', this)">
            📋 All Tracks
        </button>
        <button class="filter-btn filter-regular" onclick="filterTrack('Regular', this)">
            🎓 Regular
        </button>
        <button class="filter-btn filter-stem" onclick="filterTrack('STEM', this)">
            🔬 STEM
        </button>
        <button class="filter-btn filter-abm" onclick="filterTrack('ABM', this)">
            💼 ABM
        </button>
        <button class="filter-btn filter-humss" onclick="filterTrack('HUMSS', this)">
            📖 HUMSS
        </button>
    </div>

    <!-- SUBJECTS TABLE -->
    <div class="table-container">
        <table class="curr-table">
            <thead>
                <tr>
                    <th style="width:15%;">Track</th>
                    <th style="width:12%;">Year Level</th>
                    <th style="width:12%;">Code</th>
                    <th style="width:35%;">Description</th>
                    <th style="width:10%;">Type</th>
                    <th style="width:10%;">Price</th>
                    <th style="width:6%;">Action</th>
                </tr>
            </thead>
            <tbody id="subject-tbody">
                <?php if(empty($subjects)): ?>
                    <tr><td colspan="7" class="no-data">No subjects found. Add one above!</td></tr>
                <?php else: ?>
                    <?php foreach($subjects as $s): ?>
                    <tr data-track="<?php echo htmlspecialchars($s['track']); ?>">
                        <td>
                            <span class="track-badge badge-<?php echo strtolower($s['track']); ?>">
                                <?php echo htmlspecialchars($s['track']); ?>
                            </span>
                        </td>
                        <td><small><?php echo htmlspecialchars($s['year_level']); ?></small></td>
                        <td><span class="subject-code"><?php echo htmlspecialchars($s['code']); ?></span></td>
                        <td><span class="subject-desc"><?php echo htmlspecialchars($s['description']); ?></span></td>
                        <td><small><?php echo htmlspecialchars($s['type']); ?></small></td>
                        <td class="price-cell">₱<?php echo number_format($s['price'], 2); ?></td>
                        <td style="white-space:nowrap;">
                            <button class="action-btn edit" onclick="loadZone('curriculum.php?edit_id=<?php echo $s['id']; ?>')" title="Edit">✎</button>
                            <button class="action-btn delete" onclick="deleteSubject(<?php echo $s['id']; ?>)" title="Delete">×</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>