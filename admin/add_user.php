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

        echo "<div style='background:#d4edda; color:#155724; padding:15px; margin-bottom:20px; border-radius:5px; text-align:center;'>
                <strong>✅ User Created Successfully!</strong>
                <br>User ID: " . htmlspecialchars($_POST['account_id']) . "
              </div>";

    } catch (PDOException $e) {
        echo "<div style='background:#f8d7da; color:#721c24; padding:15px; margin-bottom:20px; border-radius:5px;'>
                <strong>Error:</strong> " . $e->getMessage() . "
              </div>";
    }
}
?>

<div class="form-card">
    <h2>Create New User</h2>
    
    <form method="POST" onsubmit="event.preventDefault(); submitForm(this, 'add_user.php');">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div class="form-group">
                <label>User ID (Username)</label>
                <input type="text" name="account_id" placeholder="e.g. prof_smith" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="management">Management (Staff)</option>
                    <option value="instructor">Instructor (Teacher)</option>
                    <option value="admin">Administrator</option>
                    <option value="student">Student</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <input type="text" name="fname" placeholder="First Name" required>
                <input type="text" name="mname" placeholder="Middle">
                <input type="text" name="lname" placeholder="Last Name" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" required>
            </div>
            <div class="form-group">
                <label>Track (For Instructors)</label>
                <select name="track">
                    <option value="">-- None / Staff --</option>
                    <option value="kinder">Kinder</option>
                    <option value="junior high school">Junior High School</option>
                    <option value="senior high school">Senior High School</option>
                </select>
                <small style="color:#666; font-size: 0.8rem;">Required if role is Instructor</small>
            </div>
        </div>

        <button type="submit" class="btn-save">Create User</button>
    </form>
</div>