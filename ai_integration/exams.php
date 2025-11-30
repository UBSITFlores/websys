<?php
session_start();
// Using your admin db config
require_once '../admin/dbconfig.php'; 

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_exam'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    // For this test, we default creator to ID 1 if not logged in
    $creator = $_SESSION['ID'] ?? 1; 

    $stmt = $pdo->prepare("INSERT INTO exams (title, description, created_by) VALUES (?, ?, ?)");
    $stmt->execute([$title, $desc, $creator]);
    header("Location: exams.php"); // Refresh
    exit;
}

// Fetch Exams
$stmt = $pdo->query("SELECT * FROM exams ORDER BY id DESC");
$exams = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Exams</title>
    <link rel="stylesheet" href="../account/login.css">
    <style>
        .container { align-items: flex-start; padding-top: 50px; }
        .exam-list { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .exam-list th, .exam-list td { padding: 12px; border: 1px solid #eee; text-align: left; }
        .exam-list th { background: #534d7c; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card" style="max-width: 800px;">
            <div class="login-header">
                <span class="login-title">📂 Exam Management</span>
            </div>

            <!-- Create Exam Form -->
            <form method="POST" class="login-form" style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                <h3>Create New Exam</h3>
                <label>Exam Title</label>
                <input type="text" name="title" required placeholder="e.g., English Essay Midterm">
                <label>Description</label>
                <input type="text" name="description" placeholder="Short description...">
                <button type="submit" name="create_exam" class="form-btn main-btn">Create Exam</button>
            </form>

            <!-- List Exams -->
            <h3>Existing Exams</h3>
            <table class="exam-list">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($exams as $exam): ?>
                    <tr>
                        <td>#<?php echo $exam['id']; ?></td>
                        <td><?php echo htmlspecialchars($exam['title']); ?></td>
                        <td>
                            <a href="questions.php?exam_id=<?php echo $exam['id']; ?>" style="color: #210c51; font-weight: bold;">Add Questions</a> | 
                            <a href="student_answers.php?exam_id=<?php echo $exam['id']; ?>" style="color: #27ae60; font-weight: bold;">Take Test</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>