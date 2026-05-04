<?php
// ✅ CORRECT — goes up 2 folders from bhw/ to reach hms2/
include __DIR__ . '/../../application/includes/session_check.php';
include __DIR__ . '/../../application/config/db.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Patient ID missing");
}

$success = false;

if (isset($_POST['save_only']) || isset($_POST['save_and_referral'])) {

    try {

        // 1. INSERT PATIENT VISIT
        $stmt = $conn->prepare("INSERT INTO patient_visits 
            (patient_id, category, bp, temperature, heart_rate, weight, height,
             spo2, respiratory_rate, blood_sugar, chief_complaint, medical_history, created_by)
            VALUES 
            (:patient_id, :category, :bp, :temp, :hr, :weight, :height,
             :spo2, :respiratory_rate, :blood_sugar, :chief_complaint, :medical_history, :created_by)");

        $stmt->execute([
            ':patient_id'        => $patient_id,
            ':category'          => 'Check-up',
            ':bp'                => $_POST['bp'] ?? '',
            ':temp'              => $_POST['temperature'] ?? null,
            ':hr'                => $_POST['heart_rate'] ?? null,
            ':weight'            => $_POST['weight'] ?? null,
            ':height'            => $_POST['height'] ?? null,
            ':spo2'              => $_POST['spo2'] ?? null,
            ':respiratory_rate'  => $_POST['respiratory_rate'] ?? null,
            ':blood_sugar'       => $_POST['blood_sugar'] ?? null,
            ':chief_complaint'   => $_POST['chief_complaint'] ?? null,
            ':medical_history'   => $_POST['medical_history'] ?? null,
            ':created_by'        => $_SESSION['user_id']
        ]);

        // 2. SAVE REFERRAL ONLY IF REQUESTED
        if (isset($_POST['save_and_referral'])) {

            $ref = $conn->prepare("INSERT INTO referrals 
                (patient_id, consultation_id, purpose, status, created_by)
                VALUES 
                (:patient_id, :consultation_id, :purpose, :status, :created_by)");

            $ref->execute([
                ':patient_id'      => $patient_id,
                ':consultation_id' => null,
                ':purpose'         => 'Check-up Assessment',
                ':status'          => 'pending',
                ':created_by'      => $_SESSION['user_id']
            ]);
        }

        $success = true;

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
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
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

body {
    background: #f4f7fb;
    padding: 25px;
}

.back {
    display: inline-block;
    margin-bottom: 15px;
    text-decoration: none;
    color: #555;
    background: white;
    padding: 8px 12px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.container {
    max-width: 650px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

h2 { margin-bottom: 15px; color: #333; }

h3 {
    margin: 18px 0 10px;
    color: #555;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 1px solid #eee;
    padding-bottom: 6px;
}

input, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: #333;
}

input:focus, textarea:focus {
    outline: none;
    border-color: #4facfe;
}

textarea { resize: none; height: 80px; }

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
}

.field-wrap { margin-bottom: 4px; }

.hint {
    font-size: 11px;
    color: #aaa;
    margin-top: -8px;
    margin-bottom: 10px;
    padding-left: 4px;
}

.buttons {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

button {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: opacity 0.2s;
}

button:hover { opacity: 0.85; }

.save     { background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; }
.referral { background: linear-gradient(135deg, #ff9966, #ff5e62); color: white; }

.success {
    background: #e8f5e9;
    color: #1b5e20;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.nurse-note {
    background: #e3f2fd;
    color: #0d47a1;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 15px;
}

.links a {
    display: inline-block;
    margin-top: 10px;
    color: #555;
    text-decoration: none;
}

.links a:hover { color: #333; }
</style>
</head>

<body>

<a href="patient_list.php" class="back">⬅ Back to Patient List</a>

<div class="container">

    <h2>🩺 Check-up Form</h2>

    <div class="nurse-note">
        📋 Fill in all available vital signs. The nurse will review and generate the AI recommendation from the dashboard.
    </div>

    <?php if ($success): ?>

        <div class="success">✔ Check-up saved successfully</div>

        <?php if (isset($_POST['save_and_referral'])): ?>
            <div class="success">📩 Referral sent successfully</div>
        <?php endif; ?>

        <div class="links">
            <a href="checkup_form.php?patient_id=<?= $patient_id ?>">➕ Add Another Check-up</a><br>
            <a href="view_patient_history.php?patient_id=<?= $patient_id ?>">📋 View History</a>
        </div>

    <?php else: ?>

    <form method="POST">

        <!-- VITAL SIGNS -->
        <h3>🫀 Vital Signs</h3>

        <input type="text" name="bp" placeholder="Blood Pressure (e.g. 120/80)" required>

        <div class="row">
            <input type="number" step="0.1" name="temperature" placeholder="Temperature (°C)">
            <input type="number" name="heart_rate" placeholder="Heart Rate (bpm)">
        </div>

        <div class="row">
            <input type="number" step="0.1" name="weight" placeholder="Weight (kg)">
            <input type="number" step="0.1" name="height" placeholder="Height (cm)">
        </div>

        <div class="row-3">
            <div class="field-wrap">
                <input type="number" step="0.1" min="0" max="100" name="spo2" placeholder="SpO2 (%)">
                <p class="hint">Oxygen Saturation</p>
            </div>
            <div class="field-wrap">
                <input type="number" min="0" max="100" name="respiratory_rate" placeholder="Resp. Rate">
                <p class="hint">Breaths per minute</p>
            </div>
            <div class="field-wrap">
                <input type="number" step="0.1" name="blood_sugar" placeholder="Blood Sugar">
                <p class="hint">mg/dL</p>
            </div>
        </div>

        <!-- CHIEF COMPLAINT -->
        <h3>🗣️ Chief Complaint</h3>
        <textarea name="chief_complaint" placeholder="Describe the patient's main complaint (e.g. headache, fever, cough, chest pain...)"></textarea>

        <!-- MEDICAL HISTORY -->
        <h3>📁 Medical History / Existing Conditions</h3>
        <textarea name="medical_history" placeholder="List any known conditions or medications (e.g. hypertension, diabetes, asthma, maintenance meds...)"></textarea>

        <div class="buttons">
            <button class="save" type="submit" name="save_only">💾 Save Only</button>
            <button class="referral" type="submit" name="save_and_referral">📩 Save & Referral</button>
        </div>

    </form>

    <?php endif; ?>

</div>

</body>
</html>