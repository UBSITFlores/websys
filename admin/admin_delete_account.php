<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'management') {
    header('Location: index.php');
    exit;
}

$accountIdToDelete = $_GET['id'] ?? 0;

if ($accountIdToDelete) {
    $portalDB->deleteAccount($accountIdToDelete);
}

header('Location: admin_dashboard.php');
exit;
?>
