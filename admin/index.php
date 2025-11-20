<?php
session_start();
if (isset($_SESSION['ACCOUNTID']) && isset($_SESSION['ROLE'])) {
    if ($_SESSION['ROLE'] === "admin") {
        header("Location: ./admin_dashboard.php");
        exit();
    }
}
?>