<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Patient ID missing");
}

$success = false;

if (isset($_POST['save_only']) || isset($_POST['save_and_referral'])) {

    $stmt = $conn->prepare("INSERT INTO patient_visits 
        (patient_id, category, notes, bp, temperature, heart_rate, weight, height, created_by)
        VALUES 
        (:patient_id, :category, :notes, :bp, :temp, :hr, :weight, :height, :created_by)");

    $stmt->execute([
        ':patient_id' => $patient_id,
        ':category' => 'Check-up',
        ':notes' => $_POST['notes'],
        ':bp' => $_POST['bp'],
        ':temp' => $_POST['temperature'],
        ':hr' => $_POST['heart_rate'],
        ':weight' => $_POST['weight'],
        ':height' => $_POST['height'],
        ':created_by' => $_SESSION['user_id']
    ]);

    if (isset($_POST['save_and_referral'])) {
        $ref = $conn->prepare("INSERT INTO referrals 
            (patient_id, consultation_id, reason, status, created_by)
            VALUES 
            (:patient_id, :consultation_id, :reason, :status, :created_by)");

        $ref->execute([
            ':patient_id' => $patient_id,
            ':consultation_id' => null,
            ':reason' => 'Check-up Assessment',
            ':status' => 'pending',
            ':created_by' => $_SESSION['user_id']
        ]);
    }

    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Check-up Form</title>

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

h3,h4{margin-top:10px;}

input,textarea{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border-radius:8px;
    border:1px solid #ccc;
    outline:none;
}

textarea{resize:none;height:80px;}

.buttons{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

button{
    flex:1;
    padding:12px;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
}

.save{
    background:linear-gradient(135deg,#4facfe,#00f2fe);
    color:white;
}

.referral{
    background:linear-gradient(135deg,#ff9966,#ff5e62);
    color:white;
}

.success{
    background:#e8f5e9;
    color:#1b5e20;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
}

.links a{
    display:inline-block;
    margin-top:10px;
    color:#555;
    text-decoration:none;
}

</style>
</head>

<body>

<a href="patient_list.php" class="back">⬅ Back to Patient List</a>

<div class="container">

<h2>🩺 Check-up Form</h2>

<?php if ($success): ?>

    <div class="success">✔ Check-up saved successfully</div>

    <?php if (isset($_POST['save_and_referral'])): ?>
        <div class="success">📩 Referral sent to Nurse</div>
    <?php endif; ?>

    <div class="links">
        <a href="checkup_form.php?patient_id=<?= $patient_id ?>">➕ Add Another Check-up</a><br>
        <a href="view_patient_history.php?patient_id=<?= $patient_id ?>">📋 View History</a>
    </div>

<?php else: ?>

<form method="POST">

    <h3>Vital Signs</h3>

    <input type="text" name="bp" placeholder="Blood Pressure (e.g. 120/80)" required>
    <input type="number" step="0.1" name="temperature" placeholder="Temperature (°C)">
    <input type="number" name="heart_rate" placeholder="Heart Rate (bpm)">
    <input type="number" step="0.1" name="weight" placeholder="Weight (kg)">
    <input type="number" step="0.1" name="height" placeholder="Height (cm)">
    <textarea name="notes" placeholder="Symptoms / Notes"></textarea>

    <div class="buttons">
        <button class="save" type="submit" name="save_only">💾 Save Only</button>
        <button class="referral" type="submit" name="save_and_referral">📩 Save & Referral</button>
    </div>

</form>

<?php endif; ?>

</div>

</body>
</html>
