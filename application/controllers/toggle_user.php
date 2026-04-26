<?php
include '../config/db.php';

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Invalid request");
}

$id = $_GET['id'];
$status = $_GET['status'];

$stmt = $conn->prepare("
    UPDATE users 
    SET status = :status 
    WHERE id = :id
");

$stmt->execute([
    ':status' => $status,
    ':id' => $id
]);

header("Location: ../../presentation/admin/users.php");
exit();
?>