<?php
$servername = "sql12.freesqldatabase.com"; // Mula sa Screenshot (328).png
$username = "sql12824630";               // Mula sa Screenshot (328).png
$password = "P3zrdTXyaX"; // Ang password mo sa FreeSQL
$dbname = "sql12824630";                 // Mula sa Screenshot (328).png

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; 
?>
