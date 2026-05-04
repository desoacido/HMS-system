<?php
session_start();
session_destroy();
header("Location: /HMS-2/login.php");
exit();
?>