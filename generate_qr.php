<?php
session_start();
include __DIR__ . '/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("No patient selected.");
}

// GET PATIENT INFO
// 1. Ihanda ang statement
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");

// 2. I-bind ang ID (i = integer)
$stmt->bind_param("i", $id);

// 3. I-execute
$stmt->execute();

// 4. Kunin ang result at i-fetch ang data
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    die("Patient not found.");
}
?>

// GENERATE QR USING GOOGLE CHART API (no library needed!)
$qr_data    = $patient['id'];  // QR stores the patient ID
$qr_url     = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $qr_data;
$qr_folder  = __DIR__ . '/qrcodes/';
$qr_file    = $qr_folder . 'patient_' . $id . '.png';
$qr_path    = 'qrcodes/patient_' . $id . '.png';

// CREATE FOLDER IF NOT EXISTS
if (!file_exists($qr_folder)) {
    mkdir($qr_folder, 0777, true);
}

// DOWNLOAD AND SAVE QR IMAGE
file_put_contents($qr_file, file_get_contents($qr_url));

// SAVE QR PATH TO DATABASE
$stmt2 = $conn->prepare("UPDATE patients SET qr_code = ? WHERE id = ?");
$stmt2->execute([$qr_path, $id]);

?>
<!DOCTYPE html>
<html>
<head>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { padding:30px; background:#f4f7fb; }
    h2 { color:#1a7a4a; margin-bottom:20px; }
    .card {
        background:white;
        padding:30px;
        border-radius:14px;
        box-shadow:0 4px 16px rgba(0,0,0,0.07);
        max-width:400px;
        text-align:center;
    }
    .card h3 { color:#333; margin-bottom:5px; }
    .card p { color:#888; font-size:13px; margin-bottom:20px; }
    .qr-img {
        width:200px;
        height:200px;
        border:3px solid #28a745;
        border-radius:12px;
        padding:10px;
        margin-bottom:20px;
    }
    .info { font-size:13px; color:#555; margin-bottom:20px; text-align:left; }
    .info span { font-weight:600; color:#1a7a4a; }
    .btn {
        padding:10px 24px;
        border:none;
        border-radius:8px;
        cursor:pointer;
        font-size:14px;
        font-family:'Poppins',sans-serif;
        font-weight:600;
        margin:5px;
    }
    .btn-print   { background:linear-gradient(135deg,#1a7a4a,#28a745); color:white; }
    .btn-back    { background:#eee; color:#555; }
    .btn-download { background:linear-gradient(135deg,#1e3c72,#2a5298); color:white; }
    .success-msg {
        background:#d4edda;
        color:#155724;
        padding:10px;
        border-radius:8px;
        margin-bottom:20px;
        font-size:13px;
    }
</style>
</head>
<body>

<h2>🔲 QR Code Generated</h2>

<div class="card">

    <div class="success-msg">✅ QR Code successfully generated!</div>

    <h3><?= htmlspecialchars($patient['firstname'] . ' ' . $patient['lastname']) ?></h3>
    <p>Patient ID: #<?= $patient['id'] ?></p>

    <img src="<?= $qr_path ?>" class="qr-img" alt="QR Code">

    <div class="info">
        <p>Gender: <span><?= $patient['gender'] ?></span></p>
        <p>Birthdate: <span><?= date('M d, Y', strtotime($patient['birthdate'])) ?></span></p>
        <p>Contact: <span><?= $patient['contact'] ?? '—' ?></span></p>
        <p>Blood Type: <span><?= $patient['blood_type'] ?? '—' ?></span></p>
    </div>

    <button class="btn btn-print" onclick="window.print()">🖨️ Print QR</button>
    <a href="<?= $qr_path ?>" download="patient_<?= $id ?>_qr.png">
        <button class="btn btn-download">⬇️ Download QR</button>
    </a>
    <button class="btn btn-back" onclick="window.top.location='bhw_patientlist.php'">← Back to List</button>

</div>

</body>
</html>
