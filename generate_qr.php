<?php
session_start();
include __DIR__ . '/db.php';

// 1. Kunin ang ID mula sa URL
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Error: No patient selected.");
}

// 2. Kunin ang impormasyon ng pasyente gamit ang MySQLi Prepared Statement
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    die("Error: Patient not found in the database.");
}

// 3. QR Generation Logic gamit ang QRServer API
$qr_data   = $patient['id']; // Ang ID ng pasyente ang nilalaman ng QR
$qr_size   = "200x200";
$qr_url    = "https://api.qrserver.com/v1/create-qr-code/?size=$qr_size&data=" . $qr_data;

$qr_folder = __DIR__ . '/qrcodes/';
$qr_filename = 'patient_' . $id . '.png';
$qr_file_path = $qr_folder . $qr_filename; // Physical path para sa file_put_contents
$db_save_path = 'qrcodes/' . $qr_filename; // Relative path para sa database

// 4. Siguraduhin na exist ang folder
if (!file_exists($qr_folder)) {
    mkdir($qr_folder, 0777, true);
}

// 5. I-download ang QR image mula sa API at i-save sa folder
$image_content = file_get_contents($qr_url);
if ($image_content === false) {
    die("Error: Could not generate QR image from API.");
}
if (file_put_contents($qr_file_path, $image_content) === false) {
    die("Error: Failed to save QR image. Check folder permissions.");
}

// 6. I-update ang database gamit ang MySQLi Prepared Statement
$update_stmt = $conn->prepare("UPDATE patients SET qr_code = ? WHERE id = ?");
$update_stmt->bind_param("si", $db_save_path, $id);

if (!$update_stmt->execute()) {
    die("Database Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code Generated</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fb; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        .success-icon { color: #28a745; font-size: 50px; margin-bottom: 10px; }
        h2 { color: #333; margin-bottom: 5px; }
        p { color: #666; font-size: 14px; margin-bottom: 20px; }
        .qr-img { width: 200px; height: 200px; border: 5px solid #f8f9fa; border-radius: 15px; margin-bottom: 20px; }
        .patient-info { background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: left; margin-bottom: 20px; font-size: 13px; }
        .btn-group { display: flex; gap: 10px; justify-content: center; }
        .btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; }
        .btn-primary { background: #1a7a4a; color: white; }
        .btn-secondary { background: #e9ecef; color: #333; }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="card">
    <div class="success-icon">✅</div>
    <h2>QR Generated!</h2>
    <p>The QR code for the patient has been successfully created and saved.</p>

    <img src="<?= $db_save_path ?>" class="qr-img" alt="Patient QR Code">

    <div class="patient-info">
        <strong>Name:</strong> <?= htmlspecialchars($patient['firstname'] . ' ' . $patient['lastname']) ?><br>
        <strong>Patient ID:</strong> #<?= $id ?><br>
        <strong>Status:</strong> QR Path Saved to Database
    </div>

    <div class="btn-group">
        <a href="patientprofile.php?id=<?= $id ?>" class="btn btn-primary">View Profile</a>
        <a href="bhw_patientlist.php" class="btn btn-secondary">Back to List</a>
    </div>
</div>

</body>
</html>
