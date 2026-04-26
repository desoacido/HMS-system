<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: /hms2/presentation/login.php");
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

// GET USER
$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// USER NOT FOUND
if (!$user) {
    $_SESSION['error'] = "User not found!";
    header("Location: /hms2/presentation/login.php");
    exit();
}

// STATUS CHECK
if ($user['status'] == 'inactive') {
    $_SESSION['error'] = "Account is deactivated!";
    header("Location: /hms2/presentation/login.php");
    exit();
}

// PASSWORD CHECK
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Wrong password!";
    header("Location: /hms2/presentation/login.php");
    exit();
}

// SESSION SAVE
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['fullname'] = $user['fullname'];

// FIRST LOGIN CHECK
if ($user['must_change_password'] == 1) {
    header("Location: /hms2/presentation/change_password.php");
    exit();
}

if ($user['role'] == 'admin') {
    header("Location: /hms2/presentation/admin/dashboard.php");
} elseif ($user['role'] == 'bhw') {
    header("Location: /hms2/presentation/bhw/dashboard.php");
} else {
    header("Location: /hms2/presentation/nurse/dashboard.php");
}

// ROLE REDIRECT


exit();
?>