<?php
session_start();
include __DIR__ . '/db.php';

$patient_id = $_GET['patient_id'] ?? null;
$category = 'immunization';

if (!$patient_id) {
    die("Invalid patient.");
}

// Kunin ang info ng pasyente para sa header
$stmt_p = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt_p->execute([$patient_id]);
$patient = $stmt_p->fetch(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kunin ang lahat ng data mula sa form
    $temp    = $_POST['temperature'];
    $bp      = $_POST['blood_pressure'];
    $hr      = $_POST['heart_rate'];
    $weight  = $_POST['weight'];
    $vaccine = $_POST['vaccine_name'];
    $dose    = $_POST['dose_number'];

    // Screening Questions (may default na 'No' para safe)
    $has_allergy         = $_POST['has_allergy'] ?? 'No';
    $allergy_notes       = $_POST['allergy_notes'] ?? '';
    $has_fever           = $_POST['has_fever'] ?? 'No';
    $has_acute_illness   = $_POST['has_acute_illness'] ?? 'No';
    $recent_vaccine      = $_POST['recent_vaccine'] ?? 'No';
    $is_pregnant         = $_POST['is_pregnant'] ?? 'No';
    $has_autoimmune      = $_POST['has_autoimmune'] ?? 'No';
    $on_blood_thinners   = $_POST['on_blood_thinners'] ?? 'No';
    $is_immunocompromised = $_POST['is_immunocompromised'] ?? 'No';

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // 1. INSERT INTO VISITS (Main record ng pagbisita)
            $stmt = $conn->prepare("INSERT INTO visits (patient_id, category, visit_date, attended_by) VALUES (?, ?, NOW(), ?)");
            $stmt->execute([$patient_id, $category, $_SESSION['user_id']]);
            $visit_id = $conn->lastInsertId();

            // 2. INSERT INTO IMMUNIZATION_VISITS (Tugma sa database columns mo)
            $stmt2 = $conn->prepare("
                INSERT INTO immunization_visits (
                    visit_id, patient_id, temperature, blood_pressure, heart_rate, weight,
                    has_allergy, allergy_notes, has_fever, has_acute_illness,
                    recent_vaccine, is_pregnant, has_autoimmune, on_blood_thinners, 
                    is_immunocompromised, vaccine_name, dose_number
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt2->execute([
                $visit_id, $patient_id, $temp, $bp, $hr, $weight,
                $has_allergy, $allergy_notes, $has_fever, $has_acute_illness,
                $recent_vaccine, $is_pregnant, $has_autoimmune, $on_blood_thinners,
                $is_immunocompromised, $vaccine, $dose
            ]);

            $immu_id = $conn->lastInsertId(); 

            // 3. INSERT INTO REFERRALS (Para makita ni Nurse)
            $stmt3 = $conn->prepare("
                INSERT INTO referrals (source_type, source_id, patient_id, referred_by, status, created_at, visit_id, ml_result)
                VALUES ('immunization', ?, ?, ?, 'pending', NOW(), ?, 'For Review')
            ");
            $stmt3->execute([$immu_id, $patient_id, $_SESSION['user_id'], $visit_id]);

            $conn->commit();
            header("Location: bhw_patientHistory.php?patient_id=$patient_id&msg=success");
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Immunization Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary-purple: #8e44ad; --bg-color: #f4f7fb; --text-color: #2c3e50; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); padding: 20px; color: var(--text-color); }
        .container { max-width: 800px; margin: auto; }
        .header-card { background: var(--primary-purple); color: white; padding: 25px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(142, 68, 173, 0.2); }
        .form-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card-title { color: var(--primary-purple); font-weight: 600; border-bottom: 1px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 20px; }
        .grid-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        label { font-size: 13px; font-weight: 500; color: #7f8c8d; display: block; margin-bottom: 8px; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #dfe6e9; border-radius: 8px; box-sizing: border-box; }
        .btn-submit { background: var(--primary-purple); color: white; border: none; padding: 16px; width: 100%; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(142, 68, 173, 0.4); }
        .error-box { background: #fab1a0; color: #c0392b; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
   

    <div class="header-card">
        <div style="font-weight:600; font-size:19px;">💉 Immunization Form</div>
        <div style="font-size:13px; opacity:0.9;">
            Patient: <b><?= htmlspecialchars(($patient['firstname'] ?? '') . ' ' . ($patient['lastname'] ?? '')) ?></b>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">⚠️ <?= implode(", ", $errors) ?></div>
    <?php endif; ?>

    <form method="POST">
        <!-- Vitals -->
        <div class="form-card">
            <div class="card-title">❤️ Vital Signs</div>
            <div class="grid-row">
                <div>
                    <label>Blood Pressure</label>
                    <input type="text" name="blood_pressure" placeholder="120/80" required>
                </div>
                <div>
                    <label>Weight (kg)</label>
                    <input type="number" step="0.1" name="weight" required>
                </div>
            </div>
            <div class="grid-row">
                <div>
                    <label>Temperature (°C)</label>
                    <input type="number" step="0.1" name="temperature" required>
                </div>
                <div>
                    <label>Heart Rate (bpm)</label>
                    <input type="number" name="heart_rate" required>
                </div>
            </div>
        </div>

        <!-- Vaccine Details -->
        <div class="form-card">
    <div class="card-title">💉 Vaccination Information</div>
    <div class="grid-row">
        <div>
            <label>Vaccine Name</label>
            <select name="vaccine_name" required>
                <option value="" disabled selected>-- Select Vaccine --</option>
                <optgroup label="Infant / Childhood">
                    <option value="BCG">BCG (Tuberculosis)</option>
                    <option value="Hepatitis B">Hepatitis B</option>
                    <option value="Pentavalent">Pentavalent (DPT-HepB-Hib)</option>
                    <option value="OPV">Oral Polio (OPV)</option>
                    <option value="IPV">Inactivated Polio (IPV)</option>
                    <option value="PCV">PCV (Pneumonia)</option>
                    <option value="MMR">MMR (Measles, Mumps, Rubella)</option>
                </optgroup>
                <optgroup label="School Age / Teenager">
                    <option value="HPV">HPV (Cervical Cancer)</option>
                    <option value="MR">Measles-Rubella (MR)</option>
                    <option value="Td">Tetanus-diphtheria (Td)</option>
                </optgroup>
                <optgroup label="Adult / Senior">
                    <option value="Flu Vaccine">Flu Vaccine</option>
                    <option value="Pneumococcal">Pneumococcal (Adult)</option>
                    <option value="COVID-19">COVID-19</option>
                    <option value="Pfizer">Pfizer (COVID-19)</option>
                    <option value="Moderna">Moderna (COVID-19)</option>
                    <option value="Sinovac">Sinovac (COVID-19)</option>
                </optgroup>
                <option value="Others">Others</option>
            </select>
        </div>
        <div>
            <label>Dose Number</label>
            <select name="dose_number" required>
                <option value="1st Dose">1st Dose</option>
                <option value="2nd Dose">2nd Dose</option>
                <option value="3rd Dose">3rd Dose</option>
                <option value="Booster 1">Booster 1</option>
                <option value="Booster 2">Booster 2</option>
                <option value="Annual">Annual</option>
            </select>
        </div>
    </div>
</div>
        <!-- Full Screening Questions -->
        <div class="form-card">
            <div class="card-title">📝 Screening Questions</div>
            <div class="grid-row">
                <div>
                    <label>Has Fever?</label>
                    <select name="has_fever">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
                <div>
                    <label>Acute Illness?</label>
                    <select name="has_acute_illness">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>
            <div class="grid-row">
                <div>
                    <label>Autoimmune Disease?</label>
                    <select name="has_autoimmune">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
                <div>
                    <label>Immunocompromised?</label>
                    <select name="is_immunocompromised">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>
            <div class="grid-row">
                <div>
                    <label>On Blood Thinners?</label>
                    <select name="on_blood_thinners">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
                <div>
                    <label>Recent Vaccine (Last 4 weeks)?</label>
                    <select name="recent_vaccine">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>
            <div class="grid-row">
                <div>
                    <label>Is Pregnant?</label>
                    <select name="is_pregnant">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
                <div>
                    <label>Has Allergy?</label>
                    <select name="has_allergy">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>
            <label>Allergy Notes</label>
            <textarea name="allergy_notes" rows="2"></textarea>
        </div>

        <button type="submit" class="btn-submit">💾 Save & Refer to Nurse</button>
    </form>
</div>

</body>
</html>