<?php
$patient_id = $_GET['patient_id'] ?? null;
if (!$patient_id) die("Invalid patient");
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Category</title>
<style>
body { font-family:Poppins; background:#f4f7fb; padding:40px; text-align:center; }
h2 { color:#1a7a4a; margin-bottom:30px; }

.container {
    display:flex;
    justify-content:center;
    gap:20px;
    flex-wrap:wrap;
}

.card {
    width:200px;
    padding:25px;
    background:white;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    cursor:pointer;
    transition:0.2s;
}

.card:hover {
    transform:translateY(-5px);
}

.card h3 {
    margin-bottom:10px;
}

.immunization { border-top:5px solid #28a745; }
.checkup { border-top:5px solid #007bff; }
.family { border-top:5px solid #9c27b0; }
</style>
</head>
<body>

<h2>📋 Select Visit Category</h2>

<div class="container">

    <div class="card immunization"
        onclick="go('immunization')">
        <h3>🟢 Immunization</h3>
        <p>Vaccines & doses</p>
    </div>

    <div class="card checkup"
        onclick="go('checkup')">
        <h3>🔵 Checkup</h3>
        <p>Vitals & symptoms</p>
    </div>

    <div class="card family"
        onclick="go('family_planning')">
        <h3>🟣 Family Planning</h3>
        <p>Reproductive care</p>
    </div>

</div>

<script>
function go(category) {
    const patient_id = <?= json_encode($patient_id) ?>;

    if (category === 'checkup') {
        window.location = "bhw_checkupform.php?patient_id=" + patient_id;
    }
    else if (category === 'immunization') {
        window.location = "bhw_immunizationform.php?patient_id=" + patient_id;
    }
    else {
        window.location = "bhw_famPlanform.php?patient_id=" + patient_id;
    }
}
</script>

</body>
</html>