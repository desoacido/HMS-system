<?php
include '../../application/includes/session_check.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Invalid patient ID");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Category</title>

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
    max-width:600px;
    margin:auto;
    text-align:center;
}

h2{
    margin-bottom:20px;
    color:#333;
}

.card{
    background:white;
    padding:15px;
    margin:12px 0;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
    transition:0.2s;
}

.card:hover{
    transform:translateY(-3px);
}

.card a{
    text-decoration:none;
    font-weight:600;
    color:#333;
    display:block;
    padding:10px;
}

.checkup{border-left:5px solid #4facfe;}
.family{border-left:5px solid #ff9966;}
.immunization{border-left:5px solid #43e97b;}

</style>
</head>

<body>

<a href="patient_list.php" class="back">⬅ Back</a>

<div class="container">

<h2>Select Category</h2>

<div class="card checkup">
    <a href="checkup_form.php?patient_id=<?= $patient_id ?>">🩺 Check-up (ML)</a>
</div>

<div class="card family">
    <a href="family_planning_form.php?patient_id=<?= $patient_id ?>">💊 Family Planning</a>
</div>

<div class="card immunization">
    <a href="immunization_form.php?patient_id=<?= $patient_id ?>">💉 Immunization</a>
</div>

</div>

</body>
</html>