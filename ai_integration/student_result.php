<?php
session_start();
require_once '../admin/dbconfig.php';

$exam_id = $_GET['exam_id'];
$student_id = $_GET['student_id'];

// Fetch the graded answers
$sql = "SELECT sa.*, q.question_text 
        FROM student_answers sa 
        JOIN questions q ON sa.question_id = q.id 
        WHERE sa.exam_id = ? AND sa.student_id = ?
        ORDER BY sa.id DESC"; // Get latest attempt
$stmt = $pdo->prepare($sql);
$stmt->execute([$exam_id, $student_id]);
$results = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Exam Results</title>
    <link rel="stylesheet" href="../account/login.css">
    <style>
        .score-badge {
            background: #27ae60; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold;
        }
        .feedback-box {
            background: #fdf2f0; border-left: 4px solid #c0392b; padding: 15px; margin-top: 10px; font-size: 0.95em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card" style="max-width: 800px;">
            <div class="login-header">
                <span class="login-title">📊 Exam Results</span>
            </div>

            <?php if (count($results) > 0): ?>
                <?php foreach($results as $res): ?>
                    <div style="margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                        <h4 style="margin-bottom: 5px;"><?php echo htmlspecialchars($res['question_text']); ?></h4>
                        
                        <p><strong>Your Answer:</strong><br>
                        <i style="color: #555;"><?php echo nl2br(htmlspecialchars($res['answer_text'])); ?></i></p>

                        <div style="margin-top: 15px;">
                            <span class="score-badge">AI Score: <?php echo $res['ai_score']; ?> / 100</span>
                        </div>

                        <div class="feedback-box">
                            <strong>🤖 AI Feedback:</strong><br>
                            <?php echo nl2br(htmlspecialchars($res['ai_feedback'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No results found.</p>
            <?php endif; ?>

            <a href="exams.php" class="form-btn main-btn" style="text-align:center; text-decoration:none; display:block;">Back to Exams</a>
        </div>
    </div>
</body>
</html>