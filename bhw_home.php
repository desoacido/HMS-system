<?php
session_start();
include __DIR__ . '/db.php';

/* TOTAL PATIENTS */
$res1 = $conn->query("SELECT COUNT(*) as count FROM patients");
$totalPatients = $res1->fetch_assoc()['count'] ?? 0;

/* NEW PATIENTS (1 visit only per patient) */
$res2 = $conn->query("
    SELECT COUNT(*) as count FROM (
        SELECT patient_id
        FROM visits
        GROUP BY patient_id
        HAVING COUNT(*) = 1
    ) as t
");
$newPatients = $res2->fetch_assoc()['count'] ?? 0;

/* RETURNING PATIENTS (more than 1 visit per patient) */
$res3 = $conn->query("
    SELECT COUNT(*) as count FROM (
        SELECT patient_id
        FROM visits
        GROUP BY patient_id
        HAVING COUNT(*) > 1
    ) as t
");
$returningPatients = $res3->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    padding: 30px;
    background: #f4f7fb;
}

h2 {
    color: #1a7a4a;
    margin-bottom: 20px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: 0.2s;
}

.card:hover {
    transform: translateY(-3px);
}

.num {
    font-size: 38px;
    font-weight: 600;
    color: #28a745;
}

.lbl {
    font-size: 13px;
    color: #777;
    margin-top: 6px;
}

/* colors per card */
.total { color: #2a5298; }
.new { color: #28a745; }
.returning { color: #ff9800; }

</style>
</head>

<body>

<h2>📊 Dashboard Overview</h2>

<div class="cards">

    <div class="card">
        <div class="num total"><?= $totalPatients ?></div>
        <div class="lbl">Total Patients</div>
    </div>

    <div class="card">
        <div class="num new"><?= $newPatients ?></div>
        <div class="lbl">New Patients</div>
    </div>

    <div class="card">
        <div class="num returning"><?= $returningPatients ?></div>
        <div class="lbl">Returning Patients</div>
    </div>

</div>

</body>
</html>
