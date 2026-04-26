<?php

$host = "localhost";
$port = "5432";
$dbname = "hms";
$user = "postgres";
$password = "sweet";

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    // set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Connected successfully"; // optional test

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>