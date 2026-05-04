<?php
session_start();
include __DIR__ . '/db.php';

$patient_id = $_GET['patient_id'] ?? null;
if (!$patient_id) die("Invalid patient.");

/* GET PATIENT INFO */
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) die("Patient not found.");

/* AGE & GENDER VALIDATION */
$birthdate = new DateTime($patient['birthdate']);
$today     = new DateTime();
$age_years = $today->diff($birthdate)->y;
$gender    = $patient['gender'];

if ($gender !== 'Female' || $age_years < 12) {
    die("Family Planning is only available for females aged 13 and above.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // STEP 1 — INSERT INTO visits
        $stmt1 = $conn->prepare("
            INSERT INTO visits (patient_id, category, visit_date, attended_by)
            VALUES (?, 'family_planning', NOW(), ?)
        ");
        $stmt1->execute([$patient_id, $_SESSION['user_id']]);
        $visit_id = $conn->lastInsertId();

        // STEP 2 — INSERT INTO family_planning_visits
        $stmt2 = $conn->prepare("
            INSERT INTO family_planning_visits (
                visit_id, patient_id,
                blood_pressure, weight,
                method, lmp, num_children, side_effects,
                age_youngest_child, is_breastfeeding, is_pregnant,
                is_smoker, has_hypertension, has_diabetes,
                has_blood_clots, has_migraines,
                planning_pregnancy, months_on_method, menstrual_regularity
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt2->execute([
            $visit_id,
            $patient_id,
            $_POST['blood_pressure']       ?? null,
            $_POST['weight']               ?? null,
            $_POST['method']               ?? null,
            $_POST['lmp']                  ?? null,
            $_POST['num_children']         ?? 0,
            $_POST['side_effects']         ?? null,
            $_POST['age_youngest_child']   ?? null,
            $_POST['is_breastfeeding']     ?? 'No',
            $_POST['is_pregnant']          ?? 'No',
            $_POST['is_smoker']            ?? 'No',
            $_POST['has_hypertension']     ?? 'No',
            $_POST['has_diabetes']         ?? 'No',
            $_POST['has_blood_clots']      ?? 'No',
            $_POST['has_migraines']        ?? 'No',
            $_POST['planning_pregnancy']   ?? 'No',
            $_POST['months_on_method']     ?? null,
            $_POST['menstrual_regularity'] ?? 'Regular'
        ]);

        $fp_id = $conn->lastInsertId();

        // STEP 3 — INSERT INTO referrals
        $stmt3 = $conn->prepare("
            INSERT INTO referrals (
                patient_id,
                visit_id,
                source_type,
                source_id,
                referred_by,
                status,
                created_at
            ) VALUES (?, ?, 'family_planning', ?, ?, 'pending', NOW())
        ");

        $stmt3->execute([
            $patient_id,
            $visit_id,
            $fp_id,
            $_SESSION['user_id']
        ]);

        $conn->commit();
        header("Location: bhw_patientHistory.php?patient_id=$patient_id&msg=Referral Sent to Nurse");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $errors[] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Family Planning Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background:#f4f7fb; padding:30px; }
        .back-btn { display:inline-flex; align-items:center; gap:6px; color:#7b1fa2; font-size:13px; font-weight:600; cursor:pointer; background:none; border:none; margin-bottom:20px; }
        .patient-banner { background:linear-gradient(135deg,#7b1fa2,#9c27b0); color:white; padding:16px 22px; border-radius:12px; margin-bottom:25px; max-width:700px; }
        .form-card { background:white; padding:25px; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.07); max-width:700px; margin-bottom:20px; }
        .form-card h3 { color:#7b1fa2; font-size:15px; margin-bottom:18px; padding-bottom:10px; border-bottom:2px solid #f0f0f0; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .form-group { margin-bottom:14px; }
        .form-group label { font-size:12px; font-weight:600; color:#555; display:block; margin-bottom:5px; }
        .form-group input,
        .form-group select,
        .form-group textarea { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; }
        .form-group textarea { resize:vertical; }

        /* Toggles */
        .screening-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .screening-item { background:#f8f9fa; padding:12px 15px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; font-size:13px; color:#444; }
        .toggle { display:flex; gap:8px; }
        .toggle input[type="radio"] { display:none; }
        .toggle label { padding:5px 14px; border-radius:20px; border:1px solid #ddd; cursor:pointer; font-size:12px; font-weight:600; background:white; }
        .toggle input[type="radio"]:checked + label.yes { background:#f3e5f5; border-color:#9c27b0; color:#4a148c; }
        .toggle input[type="radio"]:checked + label.no  { background:#e8f5e9; border-color:#28a745; color:#155724; }

        .btn-submit { width:100%; padding:14px; background:linear-gradient(135deg,#7b1fa2,#9c27b0); color:white; border:none; border-radius:10px; cursor:pointer; font-size:15px; font-weight:600; }
        .error-box { background:#f8d7da; color:#721c24; padding:12px; border-radius:8px; margin-bottom:15px; font-size:13px; max-width:700px; }
    </style>
</head>
<body>

<button class="back-btn" onclick="window.location='bhw_addvisit.php?patient_id=<?= $patient_id ?>'">
    ← Back to Selection
</button>

<div class="patient-banner">
    <h3>🟣 Family Planning & Referral</h3>
    <small>Patient: <b><?= htmlspecialchars($patient['firstname'].' '.$patient['lastname']) ?></b> | Age: <?= $age_years ?></small>
</div>

<?php if (!empty($errors)): ?>
    <div class="error-box">❌ <?= implode("<br>", $errors) ?></div>
<?php endif; ?>

<form method="POST">

    <!-- ✅ VITAL SIGNS -->
    <div class="form-card">
        <h3>💓 Vital Signs</h3>
        <div class="row">
            <div class="form-group">
                <label>Blood Pressure (mmHg)</label>
                <input type="text" name="blood_pressure" placeholder="120/80" required>
            </div>
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" step="0.1" name="weight" required>
            </div>
        </div>
    </div>

    <!-- ✅ METHOD & HISTORY -->
    <div class="form-card">
        <h3>📋 Method & History</h3>
        <div class="row">
            <div class="form-group">
                <label>Method</label>
                <select name="method" required onchange="handleMethodChange(this.value)">
                    <option value="None">None</option>
                    <option value="Pills">Pills</option>
                    <option value="Injectable">Injectable</option>
                    <option value="Implant">Implant</option>
                    <option value="Condom">Condom</option>
                    <option value="IUD">IUD</option>
                </select>
            </div>
            <div class="form-group">
                <label>Last Menstrual Period (LMP)</label>
                <input type="date" name="lmp" required>
            </div>
        </div>

        <div class="form-group" id="months_method_group" style="display:none;">
            <label>Months on this Method</label>
            <input type="number" name="months_on_method" min="0">
        </div>

        <div class="row">
            <div class="form-group">
                <label>Number of Children</label>
                <input type="number" name="num_children" id="num_children" value="0" min="0" required>
            </div>
            <div class="form-group" id="youngest_child_group" style="display:none;">
                <label>Age of Youngest (months)</label>
                <input type="number" name="age_youngest_child" min="0">
            </div>
        </div>

        <!-- ✅ ADDED: Menstrual Regularity -->
        <div class="form-group">
            <label>Menstrual Regularity</label>
            <select name="menstrual_regularity">
                <option value="Regular">Regular</option>
                <option value="Irregular">Irregular</option>
                <option value="Absent">Absent</option>
            </select>
        </div>

        <!-- ✅ ADDED: Side Effects -->
        <div class="form-group">
            <label>Side Effects (if any)</label>
            <textarea name="side_effects" rows="2" placeholder="Describe any side effects or leave blank if none..."></textarea>
        </div>
    </div>

    <!-- ✅ MEDICAL SCREENING — all 8 toggles now complete -->
    <div class="form-card">
        <h3>🏥 Medical Screening</h3>
        <div class="screening-grid">

            <div class="screening-item">
                <span>Pregnant?</span>
                <div class="toggle">
                    <input type="radio" name="is_pregnant" id="p1" value="Yes"><label for="p1" class="yes">Yes</label>
                    <input type="radio" name="is_pregnant" id="p2" value="No" checked><label for="p2" class="no">No</label>
                </div>
            </div>

            <!-- ✅ ADDED: Breastfeeding -->
            <div class="screening-item">
                <span>Breastfeeding?</span>
                <div class="toggle">
                    <input type="radio" name="is_breastfeeding" id="bf1" value="Yes"><label for="bf1" class="yes">Yes</label>
                    <input type="radio" name="is_breastfeeding" id="bf2" value="No" checked><label for="bf2" class="no">No</label>
                </div>
            </div>

            <div class="screening-item">
                <span>Smoker?</span>
                <div class="toggle">
                    <input type="radio" name="is_smoker" id="s1" value="Yes"><label for="s1" class="yes">Yes</label>
                    <input type="radio" name="is_smoker" id="s2" value="No" checked><label for="s2" class="no">No</label>
                </div>
            </div>

            <div class="screening-item">
                <span>Hypertension?</span>
                <div class="toggle">
                    <input type="radio" name="has_hypertension" id="h1" value="Yes"><label for="h1" class="yes">Yes</label>
                    <input type="radio" name="has_hypertension" id="h2" value="No" checked><label for="h2" class="no">No</label>
                </div>
            </div>

            <div class="screening-item">
                <span>Diabetes?</span>
                <div class="toggle">
                    <input type="radio" name="has_diabetes" id="d1" value="Yes"><label for="d1" class="yes">Yes</label>
                    <input type="radio" name="has_diabetes" id="d2" value="No" checked><label for="d2" class="no">No</label>
                </div>
            </div>

            <!-- ✅ ADDED: Blood Clots -->
            <div class="screening-item">
                <span>Blood Clots?</span>
                <div class="toggle">
                    <input type="radio" name="has_blood_clots" id="bc1" value="Yes"><label for="bc1" class="yes">Yes</label>
                    <input type="radio" name="has_blood_clots" id="bc2" value="No" checked><label for="bc2" class="no">No</label>
                </div>
            </div>

            <!-- ✅ ADDED: Migraines -->
            <div class="screening-item">
                <span>Migraines?</span>
                <div class="toggle">
                    <input type="radio" name="has_migraines" id="mg1" value="Yes"><label for="mg1" class="yes">Yes</label>
                    <input type="radio" name="has_migraines" id="mg2" value="No" checked><label for="mg2" class="no">No</label>
                </div>
            </div>

            <!-- ✅ ADDED: Planning Pregnancy -->
            <div class="screening-item">
                <span>Planning Pregnancy?</span>
                <div class="toggle">
                    <input type="radio" name="planning_pregnancy" id="pp1" value="Yes"><label for="pp1" class="yes">Yes</label>
                    <input type="radio" name="planning_pregnancy" id="pp2" value="No" checked><label for="pp2" class="no">No</label>
                </div>
            </div>

        </div>
    </div>

    <div style="max-width:700px;">
        <button type="submit" class="btn-submit">💾 Save & Refer to Nurse</button>
    </div>

</form>

<script>
function handleMethodChange(val) {
    document.getElementById('months_method_group').style.display = (val !== 'None') ? 'block' : 'none';
}
document.getElementById('num_children').addEventListener('input', function() {
    document.getElementById('youngest_child_group').style.display = (this.value > 0) ? 'block' : 'none';
});
</script>

</body>
</html>