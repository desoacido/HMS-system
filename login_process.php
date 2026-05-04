<?php
session_start();
include __DIR__ . '/db.php';

// I-check kung may input
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: login.php");
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

// 1. GET USER gamit ang MySQLi (Standard sa Render Docker)
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 2. USER NOT FOUND
if (!$user) {
    $_SESSION['error'] = "User not found!";
    header("Location: login.php");
    exit();
}

// 3. STATUS CHECK
if (isset($user['status']) && $user['status'] == 'inactive') {
    $_SESSION['error'] = "Account is deactivated!";
    header("Location: login.php");
    exit();
}

// 4. PASSWORD CHECK
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Wrong password!";
    header("Location: login.php");
    exit();
}

// 5. SESSION SAVE (Safe version para iwas "Undefined index" error)
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

// Dito natin sinisiguro na hindi mag-e-error kung mali ang column names mo
$fname = isset($user['firstname']) ? $user['firstname'] : (isset($user['first_name']) ? $user['first_name'] : 'User');
$lname = isset($user['lastname']) ? $user['lastname'] : (isset($user['last_name']) ? $user['last_name'] : '');
$_SESSION['fullname'] = trim($fname . ' ' . $lname);

// 6. FIRST LOGIN CHECK
if (isset($user['must_change_password']) && $user['must_change_password'] == 1) {
    header("Location: change_password.php");
    exit();
}

// 7. ROLE REDIRECT (Direct paths para sa Render)
if ($user['role'] == 'admin') {
    header("Location: admin_dashboard.php");
} elseif ($user['role'] == 'bhw') {
    header("Location: bhw_dashboard.php");
} else {
    header("Location: nurse_dashboard.php");
}

exit();
?>
