<?php
session_start();
include __DIR__ . '/db.php';

/**
 * SECURITY CHECK:
 * Sinisigurado nito na walang 'Undefined array key "user_id"' error.
 * Kung hindi naka-login ang BHW, ibabalik sila sa login page.
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];
$patient_id = $_GET['patient_id'] ?? null;
$category = 'immunization';

if (!$patient_id) {
    die("Error: No patient selected.");
}

/* 1. KUNIN ANG PATIENT INFO */
$stmt_p = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt_p->bind_param("i", $patient_id);
$stmt_p->execute();
$result_p = $stmt_p->get_result();
$patient = $result_p->fetch_assoc();

if (!$patient) {
    die("Error: Patient record not found.");
}

$errors = [];

/* 2. FORM SUBMISSION LOGIC */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vital Signs
    $temp    = $_POST['temperature'] ?? 0;
    $bp      = $_POST['blood_pressure'] ?? '';
    $hr      = $_POST['heart_rate'] ?? 0;
    $weight  = $_POST['weight'] ?? 0;
    
    // Vaccine Details
    $vaccine = $_POST['vaccine_name'] ?? '';
    $dose    = $_POST['dose_number'] ?? '';

    // Screening Questions (Default to 'No' if not set)
    $has_allergy          = $_POST['has_allergy'] ?? 'No';
    $allergy_notes        = $_POST['allergy_notes'] ?? '';
    $has_fever            = $_POST['has_fever'] ?? 'No';
    $has_acute_illness    = $_POST['has_acute_illness'] ?? 'No';
    $recent_vaccine       = $_POST['recent_vaccine'] ?? 'No';
    $is_pregnant          = $_POST['is_pregnant'] ?? 'No';
    $has_autoimmune       = $_POST['has_autoimmune'] ?? 'No';
    $on_blood_thinners     = $_POST['on_blood_thinners'] ?? 'No';
    $is_immunocompromised = $_POST['is_immunocompromised'] ?? 'No';

    if (empty($vaccine)) {
        $errors[] = "Please select a vaccine name.";
    }

    if (empty($errors)) {
        // Simulan ang Database Transaction para sigurado ang data integrity
        $conn->begin_transaction();

        try {
            // A. INSERT INTO visits table
            $stmt = $conn->prepare("INSERT INTO visits (patient_id, category, visit_date, attended_by) VALUES (?, ?, NOW(), ?)");
            $stmt->bind_param("isi", $patient_id, $category, $user_id);
            $stmt->execute();
            $visit_id = $conn->insert_id;

            // B. INSERT INTO immunization_visits table
            $stmt2 = $conn->prepare("
                INSERT INTO immunization_visits (
                    visit_id, patient_id, temperature, blood_pressure, heart_rate, weight,
                    has_allergy, allergy_notes, has_fever, has_acute_illness,
                    recent_vaccine, is_pregnant, has_autoimmune, on_blood_thinners, 
                    is_immunocompromised, vaccine_name, dose_number
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt2->bind_param("iissddsssssssssss", 
                $visit_id, $patient_id, $temp, $bp, $hr, $weight,
                $has_allergy, $allergy_notes, $has_fever, $has_acute_illness,
                $recent_vaccine, $is_pregnant, $has_autoimmune, $on_blood_thinners,
                $is_immunocompromised, $vaccine, $dose
            );
            $stmt2->execute();
            $immu_id = $conn->insert_id; 

            // C. INSERT INTO referrals (Para lumabas sa Nurse dashboard for review)
            $stmt3 = $conn->prepare("
                INSERT INTO referrals (source_type, source_id, patient_id, referred_by, status, created_at, visit_id, ml_result)
                VALUES ('immunization', ?, ?, ?, 'pending', NOW(), ?, 'For Review')
            ");
            $stmt3->bind_param("iiii", $immu_id, $patient_id, $user_id, $visit_id);
            $stmt3->execute();

            $conn->commit();
            
            // Redirect pagkatapos ng tagumpay na save
            header("Location: bhw_patientHistory.php?patient_id=$patient_id&msg=Immunization Saved");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "System Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Immunization Form | Barangay Health Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary-purple: #8e44ad; --bg-color: #f4f7fb; --text-color: #2c3e50; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); padding: 20px; color: var(--text-color); }
        .container { max-width: 800px; margin: auto; }
        
        .header-card { background: linear-gradient(135deg, #8e44ad, #9b59b6); color: white; padding: 25px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(142, 68, 173, 0.2); }
        
        .form-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card-title { color: var(--primary-purple); font-weight: 600; border-bottom: 1px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        
        .grid-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        label { font-size: 13px; font-weight: 500; color: #7f8c8d; display: block; margin-bottom: 8px; }
        
        input, select, textarea { 
            width: 100%; padding: 12px; border: 1px solid #dfe6e9; border-radius: 8px; 
            font-size: 14px; outline: none; transition: 0.3s;
        }
        input:focus, select:focus { border-color: var(--primary-purple); }

        .btn-submit { 
            background: var(--primary-purple); color: white; border: none; padding: 16px; 
            width: 100%; border-radius: 12px; font-weight: 600; cursor: pointer; 
            font-size: 16px; transition: 0.3s; margin-top: 10px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(142, 68, 173, 0.4); opacity: 0.9; }
        
        .error-box { background: #fab1a0; color: #c0392b; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 5px solid #e17055; }
        .back-link { text-decoration: none; color: var(--primary-purple); font-size: 14px; font-weight: 600; margin-bottom: 15px; display: inline-block; }
    </style>
</head>
<body>

<div class="container">
    <a href="bhw_addvisit.php?patient_id=<?= $patient_id ?>" class="back-link">← Back to Selection</a>

    <div class="header-card">
        <div style="font-weight:600; font-size:20px;">💉 Immunization Intake Form</div>
        <div style="font-size:14px; opacity:0.9; margin-top: 5px;">
            Patient: <b><?= htmlspecialchars($patient['firstname'] . ' ' . $patient['lastname']) ?></b> | 
            ID: #<?= htmlspecialchars($patient_id) ?>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <b>Please correct the following:</b><br>
            <?= implode("<br>", $errors) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <!-- VITAL SIGNS -->
        <div class="form-card">
            <div class="card-title">❤️ Vital Signs</div>
            <div class="grid-row">
                <div>
                    <label>Blood Pressure (mmHg)</label>
                    <input type="text" name="blood_pressure" placeholder="120/80" required>
                </div>
                <div>
                    <label>Weight (kg)</label>
                    <input type="number" step="0.1" name="weight" placeholder="0.0" required>
                </div>
            </div>
            <div class="grid-row">
                <div>
                    <label>Temperature (°C)</label>
                    <input type="number" step="0.1" name="temperature" placeholder="36.5" required>
                </div>
                <div>
                    <label>Heart Rate (bpm)</label>
                    <input type="number" name="heart_rate" placeholder="80" required>
                </div>
            </div>
        </div>

        <!-- VACCINATION INFO -->
        <div class="form-card">
            <div class="card-title">💉 Vaccination Information</div>
            <div class="grid-row">
                <div>
                    <label>Vaccine Name</label>
                    <select name="vaccine_name" required>
                        <option value="" disabled selected>-- Select Vaccine --</option>
                        <optgroup label="Routine Infant Immunization">
                            <option value="BCG">BCG (Tuberculosis)</option>
                            <option value="Hepatitis B">Hepatitis B</option>
                            <option value="Pentavalent">Pentavalent (DPT-HepB-Hib)</option>
                            <option value="OPV">Oral Polio (OPV)</option>
                            <option value="IPV">Inactivated Polio (IPV)</option>
                            <option value="PCV">Pneumonia (PCV)</option>
                            <option value="MMR">MMR (Measles, Mumps, Rubella)</option>
                        </optgroup>
                        <optgroup label="Adult / Others">
                            <option value="Tetanus Toxoid">Tetanus Toxoid</option>
                            <option value="Influenza">Influenza (Flu)</option>
                            <option value="COVID-19">COVID-19</option>
                            <option value="HPV">HPV</option>
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

        <!-- SCREENING -->
        <div class="form-card">
            <div class="card-title">📝 Pre-Vaccination Screening</div>
            <div class="grid-row">
                <div>
                    <label>Has Fever today?</label>
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
                    <label>Is Patient Pregnant?</label>
                    <select name="is_pregnant">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
                <div>
                    <label>Has severe Allergy?</label>
                    <select name="has_allergy">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 10px;">
                <label>Allergy Notes / Remarks</label>
                <textarea name="allergy_notes" rows="2" placeholder="List allergies if 'Yes'..."></textarea>
            </div>
        </div>

        <button type="submit" class="btn-submit">💾 Save Visit & Refer to Nurse</button>
    </form>
</div>

</body>
</html>
