<?php
include '../../application/config/db.php';
include '../../phpqrcode/qrlib.php';

$patient_id = $_GET['id'];

// GET PATIENT
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
$stmt->execute([':id' => $patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// QR DATA
$data = "https://your-domain.com/presentation/patient/view.php?id=" . $patient_id;

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($data);

$stmt = $conn->prepare("UPDATE patients SET qr_code = :qr WHERE id = :id");
$stmt->execute([
    ':qr' => $qr_url,
    ':id' => $patient_id
]);

// FOLDER
$path = "../../qrcodes/";
if (!file_exists($path)) {
    mkdir($path, 0777, true);
}

// FILE
$file = $path . "patient_" . $patient_id . ".png";

// GENERATE QR
QRcode::png($data, $file, 'L', 6, 2);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient QR Code</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body{
            font-family:'Poppins',sans-serif;
            background:#f4f7fb;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .card{
            background:white;
            padding:30px;
            border-radius:15px;
            box-shadow:0 10px 25px rgba(0,0,0,0.1);
            text-align:center;
            width:320px;
        }

        .card h2{
            margin-bottom:10px;
            color:#333;
        }

        .name{
            font-size:16px;
            color:#555;
            margin-bottom:20px;
        }

        img{
            width:200px;
            height:200px;
            margin-bottom:20px;
        }

        .btn{
            display:inline-block;
            padding:10px 15px;
            background:linear-gradient(135deg,#43e97b,#38f9d7);
            color:white;
            text-decoration:none;
            border-radius:8px;
            font-weight:600;
        }

        .btn:hover{
            opacity:0.9;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Patient QR Code</h2>

    <div class="name">
        <?= $patient['first_name'] . " " . $patient['last_name'] ?>
    </div>

    <img src="../../qrcodes/patient_<?= $patient_id ?>.png">

    <br>

    <a class="btn" href="/hms2/presentation/bhw/patient_list.php">
        ⬅ Back to Patient List
    </a>

</div>

</body>
</html>
