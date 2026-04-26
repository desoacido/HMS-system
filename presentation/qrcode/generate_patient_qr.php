<?php
include '../../application/config/db.php';
include '../../phpqrcode/qrlib.php';

$patient_id = $_GET['id'];

// get patient info (optional but useful)
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
$stmt->execute([':id' => $patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// QR content (link to patient profile)
$data = "http://localhost/hms2/presentation/patient/view.php?id=" . $patient_id;

// folder path
$path = "../../qrcodes/";

// create folder if not exists
if (!file_exists($path)) {
    mkdir($path);
}

// file name
$file = $path . "patient_" . $patient_id . ".png";

// generate QR
QRcode::png($data, $file, 'L', 6, 2);

// show QR
echo "<h3>QR Code for " . $patient['first_name'] . "</h3>";
echo "<img src='../../qrcodes/patient_" . $patient_id . ".png'>";
?>

<br><br>

<a href="/hms2/presentation/bhw/patient_list.php">
    ⬅ Back to Patient List
</a>