<?php
session_start();
if (isset($_SESSION['account_id']) && isset($_SESSION['role'])){
    if ($_SESSION['role'] == "instructor") {
        header("Location: #");
        exit();
        } 
    elseif ($_SESSION['role'] == "student"){
        header("Location: #");
        exit();
        }
    else{
        header("location: #");{
        }
    }
    } else {
        header("Location: ./account/index.php");
        exit();
        }
?>
