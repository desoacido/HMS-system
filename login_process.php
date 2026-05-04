<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    // Inalis natin ang /HMS-2/ dahil sa Render, nasa main folder na ang files mo
    header("Location: login.php");
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

// GET USER gamit ang MySQLi syntax
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// USER NOT FOUND
if (!$user) {
    $_SESSION['error'] = "User not found!";
    header("Location: login.php");
    exit();
}

// STATUS CHECK
if ($user['status'] == 'inactive') {
    $_SESSION['error'] = "Account is deactivated!";
    header("Location: login.php");
    exit();
}

// PASSWORD CHECK
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Wrong password!";
    header("Location: login.php");
    exit();
}

// SESSION SAVE
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['fullname'] = $user['firstname'] . ' ' . $user['lastname'];

// FIRST LOGIN CHECK
if ($user['must_change_password'] == 1) {
    header("Location: change_password.php");
    exit();
}

// ROLE REDIRECT (Inalis ang /HMS-2/ para gumana sa Render link)
if ($user['role'] == 'admin') {
    header("Location: admin_dashboard.php");
} elseif ($user['role'] == 'bhw') {
    header("Location: bhw_dashboard.php");
} else {
    header("Location: nurse_dashboard.php");
}

exit();
?>
