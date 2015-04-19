<?php
session_start();
if( !isset($_SESSION["user"]) ){
    echo "<p style='color:red;'>Session expired, Please login to continue.</p>";
    require_once 'login.php';
    exit();
}