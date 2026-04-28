<?php
include $_SERVER['DOCUMENT_ROOT'] . '/application/config/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/application/includes/session_check.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid patient ID");

$stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
$stmt->execute([':id' => $id]);
$patient = $stmt->fetch();

if (!$patient) die("Patient not found");

$birthDate = new DateTime($patient['birthdate']);
$today = new DateTime();
$age = $birthDate->diff($today)->y;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background:#f4f7fb; padding:25px; }
.back {
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#555;
    background:#fff;
    padding:8px 12px;
    border-radius:8px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}
.container {
    max-width:600px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}
.header {
    background:linear-gradient(135deg,#43e97b,#38f9d7);
    color:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    text-align:center;
}
.header h2 { font-size:22px; }
.header p { font-size:13px; opacity:0.9; }
.info-card {
    background:#f9f9f9;
    border-radius:10px;
    padding:15px 20px;
    margin-bottom:12px;
}
.info-card label {
    font-size:11px;
    color:#999;
    text-transform:uppercase;
    letter-spacing:1px;
}
.info-card p {
    font-size:15px;
    font-weight:600;
    color:#333;
    margin-top:3px;
}
.qr-box {
    text-align:center;
    margin-top:15px;
}
.qr-box img { width:150px; border-radius:10px; }
.actions {
    display:flex;
    gap:10px;
    margin-top:20px;
}
.actions a {
    flex:1;
    text-align:center;
    padding:10px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
    font-size:13px;
}
.visit { background:#e8f5e9; color:#1b5e20; }
.history { background:#fff3e0; color:#e65100; }
</style>
</head>
<body>

<a href="/presentation/bhw/patient_list.php" class="back">⬅ Back to Patient List</a>

<div class="container">
    <div class="header">
        <h2>👤 <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
        <p>Patient ID: #<?= $patient['id'] ?></p>
    </div>

    <div class="info-card">
        <label>Age</label>
        <p><?= $age ?> years old</p>
    </div>

    <div class="info-card">
        <label>Birthdate</label>
        <p><?= date('F d, Y', strtotime($patient['birthdate'])) ?></p>
    </div>

    <div class="info-card">
        <label>Address</label>
        <p><?= htmlspecialchars($patient['address']) ?></p>
    </div>

    <div class="info-card">
        <label>Contact Number</label>
        <p><?= htmlspecialchars($patient['contact_number']) ?></p>
    </div>

    <?php if (!empty($patient['qr_code'])): ?>
    <div class="qr-box">
        <label style="font-size:11px;color:#999;">QR CODE</label><br>
        <img src="<?= $patient['qr_code'] ?>">
    </div>
    <?php endif; ?>

    <div class="actions">
        <a class="visit" href="select_category.php?patient_id=<?= $patient['id'] ?>">🩺 Visit</a>
        <a class="history" href="view_patient_history.php?patient_id=<?= $patient['id'] ?>">📋 History</a>
    </div>

</div>
</body>
</html>
