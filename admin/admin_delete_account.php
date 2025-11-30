<?php
session_start();
require_once 'pdo_functions.php';
if (!isset($_SESSION['ROLE']) || !in_array($_SESSION['ROLE'], ['management', 'admin'])) {
    header('Location: ../account/login.php');
    exit;
}
$accountIdToDelete = $_GET['id'] ?? 0;
if ($accountIdToDelete) {
    $portalDB->deleteAccount($accountIdToDelete);
}
header('Location: admin_dashboard.php');
exit;
?>