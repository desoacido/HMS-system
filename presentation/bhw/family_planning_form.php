<?php
include $_SERVER['DOCUMENT_ROOT'] . '/application/config/db.php';
session_start();

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Invalid patient ID");
}

$message = "";

/* =========================
   FORM SUBMISSION
========================= */
if (isset($_POST['save_only']) || isset($_POST['save_and_referral'])) {

    try {
        $conn->beginTransaction();

        /* =========================
           GET INPUTS FIRST (IMPORTANT)
        ========================= */
        $patient_id = $_POST['patient_id'];

        $age = $_POST['age'] ?? 0;
        $children = $_POST['children'] ?? 0;
        $temp = $_POST['temperature'] ?? 0;
        $hr = $_POST['heart_rate'] ?? 0;
        $bp = $_POST['bp'] ?? '0/0';
        $weight = $_POST['weight'] ?? 0;
        $height = $_POST['height'] ?? 0;
        $notes = $_POST['notes'] ?? '';

        $smoking = isset($_POST['smoking']) ? 'true' : 'false';
        $breastfeeding = isset($_POST['breastfeeding']) ? 'true' : 'false';

        $bmi = ($height > 0)
            ? ($weight / (($height/100) * ($height/100)))
            : 0;

        /* =========================
           CATEGORY (FIXED)
        ========================= */
        $category = "Family Planning";

        /* =========================
           SAVE TO PATIENT VISITS
        ========================= */
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

        /* =========================
           REFERRAL IF CLICKED
        ========================= */
        if (isset($_POST['save_and_referral'])) {

            $ref = $conn->prepare("
                INSERT INTO referrals 
                (patient_id, purpose, status, created_at)
                VALUES (?, ?, 'Pending', NOW())
            ");

            $ref->execute([
                $patient_id,
                'Family Planning Assessment'
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
<title>Family Planning Form</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    padding:25px;
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

/* CONTAINER */
.container {
    max-width:750px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

h2 {
    text-align:center;
    color:#007bff;
    margin-bottom:15px;
}

/* MESSAGE */
.msg {
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
    font-weight:600;
}

.success {
    background:#e8f5e9;
    color:#1b5e20;
}

.ref {
    background:#e3f2fd;
    color:#0d47a1;
}

/* GRID */
.grid {
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:15px;
}

/* FIELD FIX */
.field {
    display:flex;
    flex-direction:column;
}

label {
    font-size:13px;
    font-weight:600;
    margin-bottom:5px;
    color:#333;
}

input, textarea {
    padding:10px;
    border:1px solid #ddd;
    border-radius:8px;
    width:100%;
}

textarea {
    resize:none;
}

/* CHECKBOX */
.checkbox-group {
    display:flex;
    gap:20px;
    background:#f9f9f9;
    padding:10px;
    border-radius:8px;
}

/* BUTTONS */
.buttons {
    display:flex;
    gap:10px;
    margin-top:15px;
}

.save {
    flex:1;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#4facfe;
    color:white;
    font-weight:600;
    cursor:pointer;
}

.referral {
    flex:1;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#ff6b6b;
    color:white;
    font-weight:600;
    cursor:pointer;
}

.save:hover, .referral:hover {
    opacity:0.9;
}

/* MOBILE */
@media(max-width:600px) {
    .grid {
        grid-template-columns:1fr;
    }

    .buttons {
        flex-direction:column;
    }
}
</style>
</head>

<body>

<!-- ✅ FIXED: removed hardcoded /hms2/ -->
<a href="/presentation/bhw/dashboard.php" class="back">⬅ Back</a>

<div class="container">

<h2>📋 Family Planning Intake</h2>

<?php if ($message == "saved"): ?>
    <div class="msg success">✔ Saved Successfully</div>
<?php elseif ($message == "referral"): ?>
    <div class="msg ref">✔ Saved & Sent to Nurse</div>
<?php endif; ?>

<form method="POST">

<input type="hidden" name="patient_id" value="<?= $patient_id ?>">

<div class="grid">

    <div class="field">
        <label>Age</label>
        <input type="number" name="age" required>
    </div>

    <div class="field">
        <label>Children</label>
        <input type="number" name="children" required>
    </div>

    <div class="field">
        <label>Weight (kg)</label>
        <input type="number" step="0.1" name="weight" required>
    </div>

    <div class="field">
        <label>Height (cm)</label>
        <input type="number" step="0.1" name="height" required>
    </div>

    <div class="field">
        <label>Temperature</label>
        <input type="number" step="0.1" name="temperature" required>
    </div>

    <div class="field">
        <label>Blood Pressure</label>
        <input type="text" name="bp" required>
    </div>

    <div class="field">
        <label>Heart Rate</label>
        <input type="number" name="heart_rate" required>
    </div>

</div>

<br>

<label>Notes</label>
<textarea name="notes" rows="3"></textarea>

<br><br>

<label>Lifestyle</label>
<div class="checkbox-group">
    <label><input type="checkbox" name="smoking"> Smoking</label>
    <label><input type="checkbox" name="breastfeeding"> Breastfeeding</label>
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
