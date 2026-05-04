<?php
session_start();
include __DIR__ . '/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Error: No patient selected.");
}

/* GET PATIENT */
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    die("Error: Patient not found.");
}

/* YOUR RENDER DOMAIN */
$base_url = "https://your-app.onrender.com";

/* QR DATA (IMPORTANT FIX) */
$qr_data = $base_url . "/patientprofile.php?id=" . $patient['id'];

/* QR IMAGE (API ONLY, NO SAVE) */
$qr_size = "200x200";
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=$qr_size&data=" . urlencode($qr_data);

?>

<!DOCTYPE html>
<html>
<head>
<title>QR Generated</title>
<style>
body { font-family:Poppins; background:#f4f7fb; text-align:center; padding:50px; }
.card { background:white; padding:30px; display:inline-block; border-radius:15px; }
img { width:200px; height:200px; }
</style>
</head>
<body>

<div class="card">
    <h2>✅ QR Generated</h2>

    <img src="<?= $qr_url ?>" alt="QR Code">

    <p><b>Name:</b> <?= $patient['firstname'] . ' ' . $patient['lastname'] ?></p>
    <p><b>ID:</b> #<?= $patient['id'] ?></p>

    <a href="patientprofile.php?id=<?= $patient['id'] ?>">View Profile</a>
</div>

</body>
</html>
