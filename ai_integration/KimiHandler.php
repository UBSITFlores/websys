<?php
// ai_integration/KimiHandler.php

class KimiHandler {
    private $apiKey;
    private $baseUrl;
    private $model;
    private $temperature;

    public function __construct() {
        // Use __DIR__ to make sure we find config.php correctly
        $config = require __DIR__ . '/config.php';
        
        // Remove accidental spaces
        $this->apiKey = trim($config['api_key']);
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->model = $config['model'];
        $this->temperature = $config['temperature'];
    }

    public function gradeEssay($question, $studentAnswer, $rubric = "General quality and accuracy.") {
        $systemPrompt = "You are an expert academic evaluator. " .
                        "Grade this answer based STRICTLY on the question and rubric. " .
                        "Return format:\n" .
                        "Score: [0-100]\n" .
                        "Feedback: [2-3 sentences of constructive feedback]";

        $userContent = "Question: $question\n\n" .
                       "Grading Rubric: $rubric\n\n" .
                       "Student Answer: $studentAnswer";

        return $this->sendRequest($systemPrompt, $userContent);
    }

    private function sendRequest($system, $user) {
        $url = $this->baseUrl . '/chat/completions';

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user]
            ],
            'temperature' => $this->temperature
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        // --- TIMEOUT & SSL FIXES ---
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Wait 120s for response
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Wait 30s to connect
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix XAMPP SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return "Connection Error: " . $error;
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            // Handle Gemini specific error structure if needed
            $msg = $result['error']['message'] ?? 'Unknown Error';
            return "API Error: " . $msg;
        }

        return $result['choices'][0]['message']['content'] ?? "No content returned.";
    }

    public function assessText($systemPrompt, $userContent) {
        return $this->sendRequest($systemPrompt, $userContent);
    }
}
?>