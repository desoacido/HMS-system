<?php
include '../../application/config/db.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Patient ID missing");
}

$stmt = $conn->prepare("SELECT * FROM patient_visits WHERE patient_id = :id ORDER BY created_at DESC");
$stmt->execute([':id' => $patient_id]);
$visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient History</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

body{
    background:#f4f7fb;
    padding:25px;
}

.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#555;
    background:white;
    padding:8px 12px;
    border-radius:8px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.container{
    max-width:800px;
    margin:auto;
}

h2{
    margin-bottom:20px;
    color:#333;
}

.card{
    background:white;
    padding:15px;
    border-radius:12px;
    margin-bottom:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
    transition:0.2s;
}

.card:hover{
    transform:translateY(-2px);
}

.row{
    margin:5px 0;
    font-size:14px;
    color:#333;
}

.label{
    font-weight:600;
    color:#555;
}

.date{
    font-size:12px;
    color:#888;
    margin-top:8px;
}

.empty{
    background:white;
    padding:15px;
    border-radius:10px;
    color:#777;
}

</style>
</head>

<body>

<a href="/presentation/bhw/dashboard.php" class="back">⬅ Back to Dashboard</a>

<div class="container">

<h2>Patient History</h2>

<?php if (empty($visits)): ?>
    <div class="empty">No records found for this patient.</div>
<?php endif; ?>

<?php foreach ($visits as $v): ?>
<div class="card">

    <div class="row"><span class="label">Category:</span> <?= $v['category'] ?></div>
    <div class="row"><span class="label">BP:</span> <?= $v['bp'] ?></div>
    <div class="row"><span class="label">Temperature:</span> <?= $v['temperature'] ?> °C</div>
    <div class="row"><span class="label">Heart Rate:</span> <?= $v['heart_rate'] ?> bpm</div>
    <div class="row"><span class="label">Weight:</span> <?= $v['weight'] ?> kg</div>
    <div class="row"><span class="label">Height:</span> <?= $v['height'] ?> cm</div>
    <div class="row"><span class="label">Notes:</span> <?= $v['notes'] ?></div>
    
    <div class="date">📅 <?= $v['created_at'] ?></div>

</div>
<?php endforeach; ?>

</div>

</body>
</html>
