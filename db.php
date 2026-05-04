<?php
$host     = 'localhost';
$dbname   = 'hms2';          // ← your database name in phpMyAdmin
$username = 'root';
$password = '';              // ← blank by default in XAMPP

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>