<?php
session_start();
require_once '../admin/dbconfig.php'; 

$exam_id = $_GET['exam_id'] ?? null;
if (!$exam_id) die("No Exam ID.");

$questions = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$questions->execute([$exam_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Take Exam</title>
    <link rel="stylesheet" href="../account/login.css">
</head>
<body>
    <div class="container">
        <div class="login-card" style="max-width: 800px;">
            <div class="login-header">
                <span class="login-title">📝 Student Answer Sheet</span>
            </div>
            
            <form action="check_essay.php" method="POST" class="login-form">
                <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                
                <?php while($q = $questions->fetch()): ?>
                    <div style="margin-bottom: 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px;">
                        <label style="font-size: 1.1em; color: #210c51;">
                            <?php echo htmlspecialchars($q['question_text']); ?>
                        </label>
                        <!-- Hidden inputs to pass Question ID and Rubric to the checker -->
                        <input type="hidden" name="questions[<?php echo $q['id']; ?>][id]" value="<?php echo $q['id']; ?>">
                        <input type="hidden" name="questions[<?php echo $q['id']; ?>][rubric]" value="<?php echo htmlspecialchars($q['rubric']); ?>">
                        <input type="hidden" name="questions[<?php echo $q['id']; ?>][text]" value="<?php echo htmlspecialchars($q['question_text']); ?>">
                        
                        <textarea name="questions[<?php echo $q['id']; ?>][answer]" rows="6" 
                                  style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-family: sans-serif;" 
                                  placeholder="Type your answer here..."></textarea>
                    </div>
                <?php endwhile; ?>

                <button type="submit" name="submit_exam" class="form-btn main-btn">Submit Exam for AI Grading</button>
            </form>
        </div>
    </div>
</body>
</html>