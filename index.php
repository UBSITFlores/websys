<?php
session_start();
if (isset($_SESSION['account_id']) && isset($_SESSION['role'])){
    if ($_SESSION['role'] == "instructor") {
        header("Location: ./instructor/dashboard.php");
        exit();
        } 
    elseif ($_SESSION['role'] == "student"){
        header("Location: ./portal/index.php");
        exit();
        }
    else{
        header("Location: ./account/index.php");
        exit();
    }
} else {
    header("Location: ./account/login.php");
    exit();
}
?>
