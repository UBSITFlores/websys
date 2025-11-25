<?php
session_start();
set_time_limit(300); // Allow script to run for 5 minutes

// Use absolute paths to prevent "file not found" errors
require_once __DIR__ . '/../admin/dbconfig.php';
require_once __DIR__ . '/KimiHandler.php'; 

// Default student ID for testing
$student_id = $_SESSION['ID'] ?? 1; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_exam'])) {
    
    $exam_id = $_POST['exam_id'];
    $submitted_questions = $_POST['questions'];
    
    if (!class_exists('KimiHandler')) {
        die("Error: KimiHandler class not found.");
    }

    $kimi = new KimiHandler();

    $sql = "INSERT INTO student_answers (student_id, exam_id, question_id, answer_text, ai_score, ai_feedback, graded_at) 
            VALUES (:sid, :eid, :qid, :ans, :score, :feedback, NOW())";
    $stmt = $pdo->prepare($sql);

    foreach ($submitted_questions as $q_id => $data) {
        $answer = $data['answer'];
        $rubric = $data['rubric'];
        $question_text = $data['text'];

        if (trim($answer) === "") {
            continue; 
        }

        // Send to AI
        $ai_response = $kimi->gradeEssay($question_text, $answer, $rubric);

        // Parse Response
        $score = 0;
        $feedback = $ai_response;

        if (preg_match('/Score:\s*(\d+)/i', $ai_response, $matches)) {
            $score = (int)$matches[1];
        }

        // Save
        $stmt->execute([
            ':sid' => $student_id,
            ':eid' => $exam_id,
            ':qid' => $q_id,
            ':ans' => $answer,
            ':score' => $score,
            ':feedback' => $feedback
        ]);
    }

    header("Location: student_result.php?exam_id=" . $exam_id . "&student_id=" . $student_id);
    exit;
} else {
    echo "Invalid Request or no data submitted.";
}
?>