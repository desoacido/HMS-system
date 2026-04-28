<?php
$host     = "sql12.freesqldatabase.com"; // ← palitan ng actual host nila
$dbname   = "sql12824630";              // ← palitan ng actual db name
$username = "sql12824630";              // ← palitan ng actual username
$password = "P3zrdTXyaX";            // ← palitan ng actual password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
