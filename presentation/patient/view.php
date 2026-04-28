<?php
include $_SERVER['DOCUMENT_ROOT'] . '/application/config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Patient ID missing");
}

// GET PATIENT DATA
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
$stmt->execute([':id' => $id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Profile</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body{
            font-family:'Poppins',sans-serif;
            background:#f4f7fb;
            padding:30px;
        }

        .card{
            max-width:500px;
            margin:auto;
            background:white;
            padding:25px;
            border-radius:12px;
            box-shadow:0 10px 25px rgba(0,0,0,0.1);
        }

        h2{
            margin-bottom:10px;
        }

        .info{
            margin:10px 0;
            color:#555;
        }

        .label{
            font-weight:600;
        }

        .back{
            display:inline-block;
            margin-top:15px;
            text-decoration:none;
            color:white;
            background:linear-gradient(135deg,#43e97b,#38f9d7);
            padding:10px 15px;
            border-radius:8px;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Patient Profile</h2>

    <div class="info"><span class="label">Name:</span> <?= $patient['first_name'] . " " . $patient['last_name'] ?></div>

    <div class="info"><span class="label">Birthday:</span><?php
echo $patient['birthdate'] ?? 'N/A';
?></div>

    <div class="info"><span class="label">Contact:</span> <?= $patient['contact_number'] ?></div>

    <div class="info"><span class="label">Address:</span> <?= $patient['address'] ?></div>

    <a class="back" href="/presentation/bhw/patient_list.php">
        ⬅ Back
    </a>

</div>

</body>
</html>
