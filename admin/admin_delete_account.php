<?php
session_start();
require_once 'pdo_functions.php';

$sessionRole = $_SESSION['role'] ?? $_SESSION['ROLE'] ?? null;
if ($sessionRole !== 'management') {
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
