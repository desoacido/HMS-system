<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Patient ID missing");
}

$message = "";

/* =========================
   SUBMIT HANDLER
========================= */
if (isset($_POST['save_only']) || isset($_POST['save_and_referral'])) {

    try {
        $conn->beginTransaction();

        $patient_id = $_POST['patient_id'];

        $bp = $_POST['bp'] ?? '0/0';
        $temp = $_POST['temperature'] ?? 0;
        $hr = $_POST['heart_rate'] ?? 0;
        $weight = $_POST['weight'] ?? 0;
        $height = $_POST['height'] ?? 0;
        $notes = $_POST['notes'] ?? '';

        $category = "Immunization";

        $stmt = $conn->prepare("
            INSERT INTO patient_visits 
            (patient_id, category, notes, bp, temperature, heart_rate, weight, height, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $patient_id,
            $category,
            $notes,
            $bp,
            $temp,
            $hr,
            $weight,
            $height,
            $_SESSION['user_id']
        ]);

        if (isset($_POST['save_and_referral'])) {

            $ref = $conn->prepare("
                INSERT INTO referrals 
                (patient_id, reason, status, created_at)
                VALUES (?, ?, 'Pending', NOW())
            ");

            $ref->execute([
                $patient_id,
                'Immunization Assessment'
            ]);

            $message = "referral";
        } else {
            $message = "saved";
        }

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Immunization Form</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body {
    background:#f4f7fb;
    padding:20px;
}

/* BACK BUTTON */
.back {
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#555;
    background:white;
    padding:8px 12px;
    border-radius:8px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

/* CONTAINER SAFE */
.container {
    max-width:650px;
    margin:auto;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

/* TITLE */
h2 {
    text-align:center;
    color:#2c7be5;
    margin-bottom:15px;
}

/* SIMPLE SAFE INPUT STYLE */
.field {
    margin-bottom:12px;
}

label {
    display:block;
    font-weight:600;
    font-size:13px;
    margin-bottom:5px;
    color:#333;
}

input, textarea {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:8px;
    outline:none;
}

textarea {
    resize:none;
    height:90px;
}

/* BUTTONS */
.buttons {
    display:flex;
    gap:10px;
    margin-top:15px;
}

button {
    flex:1;
    padding:12px;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
}

.save {
    background:#4facfe;
    color:white;
}

.referral {
    background:#ff6b6b;
    color:white;
}

button:hover {
    opacity:0.9;
}

/* MOBILE FIX */
@media(max-width:600px){
    .buttons {
        flex-direction:column;
    }
}
</style>
</head>

<body>

<a href="/hms2/presentation/bhw/patient_list.php" class="back">⬅ Back</a>

<div class="container">

<h2>💉 Immunization Form</h2>

<form method="POST">

<input type="hidden" name="patient_id" value="<?= $patient_id ?>">

<div class="field">
    <label>Blood Pressure</label>
    <input type="text" name="bp" required>
</div>

<div class="field">
    <label>Temperature</label>
    <input type="number" step="0.1" name="temperature">
</div>

<div class="field">
    <label>Heart Rate</label>
    <input type="number" name="heart_rate">
</div>

<div class="field">
    <label>Weight (kg)</label>
    <input type="number" step="0.1" name="weight">
</div>

<div class="field">
    <label>Height (cm)</label>
    <input type="number" step="0.1" name="height">
</div>

<div class="field">
    <label>Notes / Vaccine</label>
    <textarea name="notes"></textarea>
</div>

<div class="buttons">

    <button type="submit" name="save_only" class="save">
        💾 Save Only
    </button>

    <button type="submit" name="save_and_referral" class="referral">
        📩 Save & Send Referral
    </button>

</div>

</form>

</div>

</body>
</html>