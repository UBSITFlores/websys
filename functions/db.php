<?php
// functions/db.php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "portal";
$charset = "utf8mb4";

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $sy_stmt = $pdo->query("SELECT current_year FROM school_settings LIMIT 1");
    $sy_row = $sy_stmt->fetch();
    
    $current_sy = $sy_row['current_year'] ?? date('Y') . '-' . (date('Y') + 1);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>