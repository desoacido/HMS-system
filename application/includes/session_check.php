<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /hms2/presentation/login.php");
    exit();
}
?>