<?php
session_start();
include __DIR__ . '/db.php';

$patient_id = $_GET['patient_id'] ?? null;
$category = $_GET['category'] ?? 'checkup';

if (!$patient_id) {
    die("Invalid patient.");
}

/* GET PATIENT INFO */
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) die("Patient not found.");

$birthdate = new DateTime($patient['birthdate']);
$today     = new DateTime();
$age_years = $today->diff($birthdate)->y;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $temp = $_POST['temperature'];
    $bp = $_POST['bp'];
    $hr = $_POST['heart_rate'];
    $spo2 = $_POST['spo2'];
    $weight = $_POST['weight'];
    $symptoms = $_POST['symptoms'];
    $duration = $_POST['duration'];
    $fever = $_POST['fever'];
    $cough = $_POST['cough'];
    $breathing = $_POST['breathing'];

    // VALIDATION
    if ($temp < 34 || $temp > 42) $errors[] = "Invalid temperature";
    if (!preg_match('/^\d{2,3}\/\d{2,3}$/', $bp)) $errors[] = "Invalid BP format";
    if ($spo2 < 70 || $spo2 > 100) $errors[] = "Invalid oxygen level";

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // 1. INSERT VISIT
            $stmt = $conn->prepare("INSERT INTO visits (patient_id, category, visit_date, attended_by) VALUES (?, ?, NOW(), ?)");
            $stmt->execute([$patient_id, $category, $_SESSION['user_id']]);
            $visit_id = $conn->lastInsertId();

            // 2. INSERT CHECKUP DETAILS
            $stmt2 = $conn->prepare("INSERT INTO checkup_visits (visit_id, temperature, blood_pressure, heart_rate, spo2, weight, symptoms, duration, fever, cough, breathing) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->execute([$visit_id, $temp, $bp, $hr, $spo2, $weight, $symptoms, $duration, $fever, $cough, $breathing]);

            // 🔥 REFERRAL LOGIC
            if (isset($_POST['action']) && $_POST['action'] === 'refer') {
                $stmt3 = $conn->prepare("INSERT INTO referrals (patient_id, visit_id, source_type, source_id, status, ml_result, referred_by, created_at) VALUES (?, ?, 'checkup', ?, 'pending', 'For Review', ?, NOW())");
                $stmt3->execute([$patient_id, $visit_id, $visit_id, $_SESSION['user_id']]);
            }

            $conn->commit();
            header("Location: bhw_patientHistory.php?patient_id=$patient_id&msg=Checkup Saved");
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Checkup Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background:#f4f7fb; padding:30px; }
        .back-btn { display:inline-flex; align-items:center; gap:6px; color:#7b1fa2; font-size:13px; font-weight:600; cursor:pointer; background:none; border:none; margin-bottom:20px; text-decoration:none; }
        
        .patient-banner { background:linear-gradient(135deg,#7b1fa2,#9c27b0); color:white; padding:16px 22px; border-radius:12px; margin-bottom:25px; max-width:700px; }
        .patient-banner h3 { font-size:18px; }

        .form-card { background:white; padding:25px; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.07); max-width:700px; margin-bottom:20px; }
        .form-card h3 { color:#7b1fa2; font-size:15px; margin-bottom:18px; padding-bottom:10px; border-bottom:2px solid #f0f0f0; }
        
        .row { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .form-group { margin-bottom:14px; }
        .form-group label { font-size:12px; font-weight:600; color:#555; display:block; margin-bottom:5px; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; outline:none; }
        .form-group input:focus { border-color: #9c27b0; }

        /* Screening Toggles - Same as FP */
        .screening-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .screening-item { background:#f8f9fa; padding:12px 15px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; font-size:13px; color:#444; }
        .toggle { display:flex; gap:8px; }
        .toggle input[type="radio"] { display:none; }
        .toggle label { padding:5px 14px; border-radius:20px; border:1px solid #ddd; cursor:pointer; font-size:12px; font-weight:600; background:white; }
        
        /* YES/NO Colors */
        .toggle input[type="radio"]:checked + label.yes { background:#f3e5f5; border-color:#9c27b0; color:#4a148c; }
        .toggle input[type="radio"]:checked + label.no { background:#e8f5e9; border-color:#28a745; color:#155724; }
        
        .btn-submit { width:100%; padding:14px; background:linear-gradient(135deg,#7b1fa2,#9c27b0); color:white; border:none; border-radius:10px; cursor:pointer; font-size:15px; font-weight:600; transition: 0.3s; }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-2px); }
        
        .error-box { background:#f8d7da; color:#721c24; padding:12px; border-radius:8px; margin-bottom:15px; font-size:13px; max-width:700px; border-left: 5px solid #dc3545; }
    </style>
</head>
<body>

<a href="bhw_addvisit.php?patient_id=<?= $patient_id ?>" class="back-btn">
    ← Back to Selection
</a>

<div class="patient-banner">
    <h3>🩺 General Check-up & Screening</h3>
    <small>Patient: <b><?= htmlspecialchars($patient['firstname'].' '.$patient['lastname']) ?></b> | Age: <?= $age_years ?></small>
</div>

<?php if (!empty($errors)): ?>
    <div class="error-box">❌ <b>Please fix the following:</b><br><?= implode("<br>", $errors) ?></div>
<?php endif; ?>

<form method="POST">

    <!-- VITAL SIGNS CARD -->
    <div class="form-card">
        <h3>💓 Vital Signs</h3>
        <div class="row">
            <div class="form-group">
                <label>Temperature (°C)</label>
                <input type="number" step="0.1" name="temperature" required placeholder="36.5">
            </div>
            <div class="form-group">
                <label>Blood Pressure (mmHg)</label>
                <input type="text" name="bp" required placeholder="120/80">
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label>Heart Rate (BPM)</label>
                <input type="number" name="heart_rate" required placeholder="80">
            </div>
            <div class="form-group">
                <label>Oxygen Level (SpO2 %)</label>
                <input type="number" name="spo2" required placeholder="98">
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" step="0.1" name="weight" required placeholder="60">
            </div>
            <div class="form-group">
                <label>Duration of Symptoms (days)</label>
                <input type="number" name="duration" required placeholder="0">
            </div>
        </div>
    </div>

    <!-- SYMPTOMS SCREENING CARD -->
    <div class="form-card">
        <h3>📋 Symptoms Screening</h3>
        <div class="screening-grid">
            <div class="screening-item">
                <span>Fever?</span>
                <div class="toggle">
                    <input type="radio" name="fever" id="f1" value="yes"><label for="f1" class="yes">Yes</label>
                    <input type="radio" name="fever" id="f2" value="no" checked><label for="f2" class="no">No</label>
                </div>
            </div>
            <div class="screening-item">
                <span>Cough?</span>
                <div class="toggle">
                    <input type="radio" name="cough" id="c1" value="yes"><label for="c1" class="yes">Yes</label>
                    <input type="radio" name="cough" id="c2" value="no" checked><label for="c2" class="no">No</label>
                </div>
            </div>
            <div class="screening-item" style="grid-column: span 2;">
                <span>Breathing Difficulty?</span>
                <div class="toggle">
                    <input type="radio" name="breathing" id="b1" value="yes"><label for="b1" class="yes">Yes</label>
                    <input type="radio" name="breathing" id="b2" value="no" checked><label for="b2" class="no">No</label>
                </div>
            </div>
        </div>
        
        <div class="form-group" style="margin-top:15px;">
            <label>Other Symptoms / Remarks</label>
            <textarea name="symptoms" rows="3" placeholder="Enter other observations..."></textarea>
        </div>
    </div>

    <div style="max-width:700px;">
        <button type="submit" name="action" value="refer" class="btn-submit">
            💾 Save & Refer to Nurse
        </button>
    </div>

</form>

</body>
</html>