<?php
include __DIR__ . '/db.php';

$stmt = $conn->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Hash in DB: " . $user['password'] . "<br>";
echo "Password length: " . strlen($user['password']) . "<br>";
echo "Verify result: " . (password_verify('ADMIN123', $user['password']) ? 'TRUE ✅' : 'FALSE ❌');
?>