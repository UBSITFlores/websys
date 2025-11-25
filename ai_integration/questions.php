<?php
session_start();
require_once '../admin/dbconfig.php'; 

$exam_id = $_GET['exam_id'] ?? null;
if (!$exam_id) die("No Exam ID specified.");

// Add Question
if (isset($_POST['add_question'])) {
    $q_text = $_POST['question_text'];
    $rubric = $_POST['rubric'];
    
    $stmt = $pdo->prepare("INSERT INTO questions (exam_id, question_text, rubric) VALUES (?, ?, ?)");
    $stmt->execute([$exam_id, $q_text, $rubric]);
}

// Get Exam Details and Questions
$exam = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$exam->execute([$exam_id]);
$examData = $exam->fetch();

$questions = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$questions->execute([$exam_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Questions</title>
    <link rel="stylesheet" href="../account/login.css">
</head>
<body>
    <div class="container">
        <div class="login-card" style="max-width: 700px;">
            <div class="login-header">
                <a href="exams.php" class="back-btn">⬅</a>
                <span class="login-title">Editing: <?php echo htmlspecialchars($examData['title']); ?></span>
            </div>

            <form method="POST" class="login-form">
                <label>Question Text</label>
                <textarea name="question_text" rows="3" required style="padding:10px; border-radius:8px; border:1px solid #ccc; width:100%;"></textarea>
                
                <label>AI Grading Rubric (Instructions for Kimi)</label>
                <textarea name="rubric" rows="3" required style="padding:10px; border-radius:8px; border:1px solid #ccc; width:100%;" placeholder="e.g., Grade based on grammar, relevance to history, and creativity."></textarea>
                
                <button type="submit" name="add_question" class="form-btn main-btn">Add Question</button>
            </form>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
            
            <h4>Current Questions:</h4>
            <ul>
                <?php while($row = $questions->fetch()): ?>
                    <li style="margin-bottom: 10px;">
                        <strong>Q:</strong> <?php echo htmlspecialchars($row['question_text']); ?><br>
                        <em style="color:#666; font-size:0.9em;">Rubric: <?php echo htmlspecialchars($row['rubric']); ?></em>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</body>
</html>