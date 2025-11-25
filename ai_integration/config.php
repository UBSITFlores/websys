<?php
// ai_integration/config.php

return [
    'api_key' => 'AIzaSyA1qDvuW-NmciStEUdoBZ3baYhsAHTRQVA',
    
    // Keep this URL exactly as is
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai/',
    
    // CRITICAL FIX: Changed 'gemini-1.5-flash-latest' to 'gemini-flash-latest'
    // This exists in your "ListModels" output.
    'model' => 'gemini-flash-latest', 
    
    'temperature' => 0.3 
];
?>