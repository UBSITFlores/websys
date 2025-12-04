<?php
session_start();

// 1. SECURITY
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') {
    http_response_code(403);
    echo "Access Denied.";
    exit;
}

// 2. DB CONNECTION
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 3. HANDLE SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO account 
                (account_id, fname, mname, lname, password, role, track, date_enrolled, degree, status) 
                VALUES 
                (:aid, :fname, :mname, :lname, :pass, :role, :track, CURDATE(), 'Bachelor', 'Active')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':aid' => $_POST['account_id'],
            ':fname' => $_POST['fname'],
            ':mname' => $_POST['mname'],
            ':lname' => $_POST['lname'],
            ':pass'  => $_POST['password'], 
            ':role'  => $_POST['role'],
            ':track' => $_POST['track'] ?? ''
        ]);

        echo "<div style='background:#d4edda; color:#155724; padding:15px; margin-bottom:20px; border-radius:5px; text-align:center; border:1px solid #c3e6cb;'>
                <strong>✅ User Created Successfully!</strong>
                <br>User ID: " . htmlspecialchars($_POST['account_id']) . "
              </div>";

    } catch (PDOException $e) {
        echo "<div style='background:#f8d7da; color:#721c24; padding:15px; margin-bottom:20px; border-radius:5px; border:1px solid #f5c6cb;'>
                <strong>❌ Error:</strong> " . $e->getMessage() . "
              </div>";
    }
}
?>

<style>
/* --- INLINE STYLES FOR ADD USER --- */
.form-card {
    max-width: 800px;
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
    content: "👤";
    font-size: 2rem;
}

.form-group {
    margin-bottom: 18px;
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

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 18px;
}

.form-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
}

.info-hint {
    color: #666;
    font-size: 0.8rem;
    margin-top: 4px;
    display: block;
}

.section-divider {
    border-top: 2px solid #e9ecef;
    margin: 25px 0;
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

.role-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 8px;
}

.badge-management {
    background: #ffeaa7;
    color: #6c5ce7;
}

.badge-instructor {
    background: #dfe6e9;
    color: #2d3436;
}

.badge-admin {
    background: #ff7675;
    color: white;
}

.badge-student {
    background: #74b9ff;
    color: #0984e3;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .form-grid-2,
    .form-grid-3 {
        grid-template-columns: 1fr;
    }

    .form-card {
        padding: 20px;
    }
}
</style>

<div class="form-card">
    <h2 class="page-title">Create New User</h2>
    
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'add_user.php');">
        
        <div class="form-grid-2">
            <div class="form-group">
                <label>🆔 User ID (Username)</label>
                <input type="text" name="account_id" placeholder="e.g. prof_smith or 20260001" required>
                <small class="info-hint">This will be used for login</small>
            </div>
            <div class="form-group">
                <label>🎭 Role</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="management">Management <span class="role-badge badge-management">(Staff)</span></option>
                    <option value="instructor">Instructor <span class="role-badge badge-instructor">(Teacher)</span></option>
                    <option value="admin">Administrator <span class="role-badge badge-admin">(Full Access)</span></option>
                    <option value="student">Student <span class="role-badge badge-student">(Learner)</span></option>
                </select>
            </div>
        </div>

        <div class="section-divider"></div>

        <div class="form-group">
            <label>👤 Full Name</label>
            <div class="form-grid-3">
                <input type="text" name="fname" placeholder="First Name" required>
                <input type="text" name="mname" placeholder="Middle Name">
                <input type="text" name="lname" placeholder="Last Name" required>
            </div>
        </div>

        <div class="section-divider"></div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>🔐 Password</label>
                <input type="text" name="password" placeholder="Enter password" required>
                <small class="info-hint">User can change this after first login</small>
            </div>
            <div class="form-group">
                <label>📚 Track (For Instructors)</label>
                <select name="track">
                    <option value="">-- None / Not Applicable --</option>
                    <option value="kinder">Kinder</option>
                    <option value="junior high school">Junior High School</option>
                    <option value="senior high school">Senior High School</option>
                </select>
                <small class="info-hint">Required if role is Instructor</small>
            </div>
        </div>

        <button type="submit" class="btn-save">✅ Create User Account</button>
    </form>
</div>