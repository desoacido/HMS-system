<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Invalid patient ID");
}

$stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
$stmt->execute([':id' => $patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Referral</title>

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
    max-width:650px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:15px;
    color:#333;
}

.patient{
    background:#f9fbff;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
}

textarea{
    width:100%;
    height:120px;
    padding:10px;
    border-radius:8px;
    border:1px solid #ddd;
    outline:none;
    resize:none;
}

button{
    width:100%;
    padding:12px;
    margin-top:10px;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
    background:linear-gradient(135deg,#ff9966,#ff5e62);
    color:white;
}

button:hover{
    opacity:0.9;
}

hr{
    margin:15px 0;
    border:0;
    border-top:1px solid #eee;
}

.label{
    font-weight:600;
    color:#555;
}

</style>
</head>

<body>

<a href="patient_list.php" class="back">⬅ Back to Patient List</a>

<div class="container">

<h2>🏥 Create Referral</h2>

<div class="patient">
    <span class="label">Patient:</span>
    <?= $patient['first_name'] . " " . $patient['last_name'] ?>
</div>

<hr>

<form action="../../application/controllers/referral_controllers.php" method="POST">

<input type="hidden" name="patient_id" value="<?= $patient_id ?>">

<textarea name="reason" required>
BHW Referral: Patient requires further clinical assessment based on visit records.
</textarea>

<button type="submit" name="create_referral">🚀 Send Referral</button>

</form>

</div>

</body>
</html>