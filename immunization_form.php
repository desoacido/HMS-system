<?php
session_start();
include __DIR__ . '/db.php';

// Kunin ang IDs mula sa URL (halimbawa: galing sa profile or visit list)
$visit_id   = $_GET['visit_id'] ?? '';
$patient_id = $_GET['patient_id'] ?? '';

$patient_name = "";

// 1. I-verify ang patient kung may ID na binigay
if (!empty($patient_id)) {
    $stmt = $conn->prepare("SELECT firstname, lastname FROM patients WHERE id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc(); // TAMA: MySQLi syntax (walang argument sa loob ng fetch)
    
    if ($patient) {
        $patient_name = $patient['firstname'] . " " . $patient['lastname'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Immunization Screening</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fb; padding: 30px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #1a7a4a; margin-bottom: 20px; font-size: 22px; }
        .patient-banner { background: #e8f5e9; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; color: #2e7d32; border-left: 5px solid #2e7d32; }
        label { font-weight: 600; display: block; margin-top: 15px; font-size: 13px; color: #555; }
        input, select, textarea { width: 100%; padding: 12px; margin-top: 5px; border-radius: 8px; border: 1px solid #ddd; font-family: inherit; font-size: 14px; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        button { margin-top: 30px; padding: 15px; width: 100%; border: none; background: linear-gradient(135deg, #28a745, #218838); color: white; font-weight: 600; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3); }
    </style>
</head>
<body>

<div class="container">
    <h2>💉 Immunization / Screening Form</h2>

    <?php if ($patient_name): ?>
        <div class="patient-banner">
            <strong>Screening for:</strong> <?= htmlspecialchars($patient_name) ?> (ID: #<?= htmlspecialchars($patient_id) ?>)
        </div>
    <?php endif; ?>

    <form method="POST" action="save_immunization.php">

        <!-- BASIC INFO -->
        <div class="row">
            <div>
                <label>Visit ID</label>
                <input type="number" name="visit_id" value="<?= htmlspecialchars($visit_id) ?>" required readonly style="background:#f9f9f9;">
            </div>

            <div>
                <label>Patient ID</label>
                <input type="number" name="patient_id" value="<?= htmlspecialchars($patient_id) ?>" required readonly style="background:#f9f9f9;">
            </div>
        </div>

        <!-- VITALS -->
        <div class="row">
            <div>
                <label>Temperature (°C)</label>
                <input type="text" name="temperature" placeholder="e.g. 36.5">
            </div>
            <div>
                <label>Blood Pressure</label>
                <input type="text" name="blood_pressure" placeholder="e.g. 120/80">
            </div>
            <div>
                <label>Heart Rate (BPM)</label>
                <input type="text" name="heart_rate" placeholder="e.g. 72">
            </div>
            <div>
                <label>Weight (kg)</label>
                <input type="text" name="weight" placeholder="e.g. 60">
            </div>
        </div>

        <!-- CONDITIONS -->
        <div class="row">
            <div>
                <label>Has Allergy?</label>
                <select name="has_allergy">
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>
            <div>
                <label>Has Fever?</label>
                <select name="has_fever">
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>
        </div>

        <label>Allergy Notes</label>
        <textarea name="allergy_notes" rows="2" placeholder="List allergies if any..."></textarea>

        <div class="row">
            <div>
                <label>Has Acute Illness?</label>
                <select name="has_acute_illness">
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>
            <div>
                <label>Recent Vaccine (Last 4 weeks)</label>
                <select name="recent_vaccine">
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div>
                <label>Is Pregnant?</label>
                <select name="is_pregnant">
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>
            <div>
                <label>Has Autoimmune Disease?</label>
                <select name="has_autoimmune">
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>
        </div>

        <!-- VACCINE DETAILS -->
        <div class="row">
            <div>
                <label>Vaccine Name</label>
                <input type="text" name="vaccine_name" placeholder="e.g. Pentavalent, BCG">
            </div>
            <div>
                <label>Dose Number</label>
                <select name="dose_number">
                    <option value="1st Dose">1st Dose</option>
                    <option value="2nd Dose">2nd Dose</option>
                    <option value="3rd Dose">3rd Dose</option>
                    <option value="Booster">Booster</option>
                </select>
            </div>
        </div>

        <button type="submit">💉 Save Immunization Record</button>

    </form>
</div>

</body>
</html>
