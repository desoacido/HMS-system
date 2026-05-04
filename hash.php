<?php
$newHash = password_hash('ADMIN123', PASSWORD_BCRYPT);
echo $newHash;
echo "<br>Length: " . strlen($newHash);

// Auto-update the database
include __DIR__ . '/db.php';
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$newHash]);
echo "<br>Database updated! ✅";
?>