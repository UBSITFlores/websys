<?php
// ai_integration/debug_auth.php
// Run this file to test your API key validity

$config = require 'config.php';
$apiKey = trim($config['api_key']);
$baseUrl = rtrim($config['base_url'], '/');

echo "<h2>🔑 Kimi API Debugger</h2>";
echo "<strong>Base URL:</strong> $baseUrl<br>";
echo "<strong>Key Length:</strong> " . strlen($apiKey) . " characters<br>";
echo "<strong>Key Preview:</strong> " . substr($apiKey, 0, 5) . "..." . substr($apiKey, -5) . "<br><br>";

// Test 1: List Models
// FIX: Now uses the Base URL from config instead of hardcoded .cn
$url = $baseUrl . "/models"; 

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>📡 Connection Test Result:</h3>";

if ($httpCode == 200) {
    echo "<div style='color:green; font-weight:bold;'>✅ SUCCESS: API Key is Valid!</div>";
    echo "<p>The API accepted your key. The issue might be in the code logic elsewhere.</p>";
} elseif ($httpCode == 401) {
    echo "<div style='color:red; font-weight:bold;'>❌ ERROR 401: Invalid Authentication</div>";
    echo "<p>The API rejected the key. Possibilities:</p>";
    echo "<ul>
            <li>The key is for the wrong endpoint (Try switching .cn to .ai in config).</li>
            <li>The key was revoked.</li>
            <li>Balance is empty.</li>
          </ul>";
} else {
    echo "<div style='color:orange; font-weight:bold;'>⚠️ Unexpected HTTP Code: $httpCode</div>";
}

echo "<h4>Raw API Response:</h4>";
echo "<pre style='background:#f4f4f4; padding:10px;'>" . htmlspecialchars($response) . "</pre>";
?>