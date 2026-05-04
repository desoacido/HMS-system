<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: /HMS-2/login.php");
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
    header("Location: /HMS-2/login.php");
    exit();
}

// STATUS CHECK
if ($user['status'] == 'inactive') {
    $_SESSION['error'] = "Account is deactivated!";
    header("Location: /HMS-2/login.php");
    exit();
}

// PASSWORD CHECK
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Wrong password!";
    header("Location: /HMS-2/login.php");
    exit();
}

// SESSION SAVE
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['fullname'] = $user['firstname'] . ' ' . $user['lastname'];

// FIRST LOGIN CHECK
if ($user['must_change_password'] == 1) {
    header("Location: /HMS-2/change_password.php");
    exit();
}

if ($user['role'] == 'admin') {
    header("Location: /HMS-2/admin_dashboard.php");
} elseif ($user['role'] == 'bhw') {
    header("Location: /HMS-2/bhw_dashboard.php");
} else {
    header("Location: /HMS-2/nurse_dashboard.php");
}

// ROLE REDIRECT


exit();
?>
