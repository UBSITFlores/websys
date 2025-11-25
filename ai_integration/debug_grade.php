<?php
// ai_integration/debug_grading.php
$config = require 'config.php';
$apiKey = trim($config['api_key']);
$baseUrl = rtrim($config['base_url'], '/');
$model = $config['model'];

echo "<h2>🕵️ Grading Logic Debugger</h2>";
echo "Testing Model: <strong>$model</strong><br>";
echo "Target URL: <strong>$baseUrl/chat/completions</strong><br><br>";

// Simulate a grading request
$data = [
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user', 'content' => 'Hello. This is a test. Reply with exactly one word: Success.']
    ],
    'temperature' => 0.3
];

$ch = curl_init($baseUrl . '/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>📡 Response Status: $httpCode</h3>";

if ($error) {
    echo "<div style='color:red'>Connection Error: $error</div>";
} else {
    echo "<h4>Raw AI Response (Look here for the cause):</h4>";
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ddd; white-space:pre-wrap;'>" . htmlspecialchars($response) . "</pre>";
    
    $json = json_decode($response, true);
    
    // Diagnosis
    if(isset($json['choices'][0]['message']['content'])) {
        echo "<div style='color:green; font-weight:bold; margin-top:10px;'>✅ SUCCESS: Content found!</div>";
        echo "Content: " . $json['choices'][0]['message']['content'];
    } elseif (isset($json['error'])) {
        echo "<div style='color:red; font-weight:bold; margin-top:10px;'>❌ API ERROR</div>";
        echo "Message: " . $json['error']['message'];
    } elseif (isset($json['candidates'][0]['finishReason'])) {
         // Gemini native format leaking through
         echo "<div style='color:orange; font-weight:bold; margin-top:10px;'>⚠️ BLOCKED (Safety or Format)</div>";
         echo "Finish Reason: " . $json['candidates'][0]['finishReason'];
    } else {
        echo "<div style='color:red; font-weight:bold; margin-top:10px;'>❌ NO CONTENT FOUND</div>";
        echo "The path ['choices'][0]['message']['content'] does not exist.";
    }
}
?>
```

### Step 2: Run and Analyze
1.  Save the file.
2.  Run `http://localhost/portal/ai_integration/debug_grading.php`.

**What to look for in the "Raw AI Response":**
* **404 Not Found:** This usually means the model name `gemini-1.5-flash` is slightly wrong for the API endpoint.
* **FinishReason: SAFETY:** This means Gemini blocked the request.
* **Error 400:** Invalid JSON structure.

### Possible Quick Fix (Model Name)
While you run the debugger, there is a 90% chance the issue is just the model name. Gemini via the OpenAI-compatible endpoint sometimes prefers the "user-friendly" name.

Try changing your `config.php` model to this:
```php
'model' => 'gemini-1.5-flash-latest', 
// OR try removing the dash:
// 'model' => 'gemini-1.5-flash',