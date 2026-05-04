<?php
session_start();
include __DIR__ . '/db.php';

if (isset($_GET['patient_id'])) {

    $id = $_GET['patient_id'];

    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();

    if (!$patient) {
        echo json_encode(['status' => 'not_found']);
    } else {
        echo json_encode([
            'status' => 'found',
            'name' => $patient['firstname'] . ' ' . $patient['lastname']
        ]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

<style>
body { font-family:Poppins; background:#f4f7fb; padding:30px; }
.card { background:white; padding:20px; border-radius:12px; max-width:500px; }
#reader { width:100%; }

.alert { display:none; padding:10px; margin-top:10px; border-radius:8px; }
.success { background:#d4edda; }
.error { background:#f8d7da; }
</style>
</head>

<body>

<div class="card">
<h2>📷 Scan QR</h2>

<div id="reader"></div>

<div class="alert success" id="success">Patient Found</div>
<div class="alert error" id="error">Not Found</div>

</div>

<script>
let scanner = new Html5Qrcode("reader");

scanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    (decodedText) => {

        scanner.stop();

        let patientId = decodedText;

        // 🔥 FIX: extract ID if URL ang QR
        if (decodedText.includes("id=")) {
            const url = new URL(decodedText);
            patientId = url.searchParams.get("id");
        }

        fetch("scan_qr.php?patient_id=" + patientId)
        .then(res => res.json())
        .then(data => {

            if (data.status === "found") {
                document.getElementById("success").style.display = "block";

                setTimeout(() => {
                    window.location.href = "patientprofile.php?id=" + patientId;
                }, 1000);

            } else {
                document.getElementById("error").style.display = "block";
            }
        });

    }
);
</script>

</body>
</html>
