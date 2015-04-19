<?php
session_start();
unset($_SESSION["user"]);
echo "<p style='color:blue;'>User logged out successfully.</p>";
require_once 'login.php';
exit();
