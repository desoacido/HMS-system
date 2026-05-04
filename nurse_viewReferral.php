<?php
session_start();
// Database connection - siguraduhin na tama ang iyong db details
include 'db.php'; 

$referral_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$referral_id) {
    die("Error: No Referral ID provided.");
}

/* 1. FETCH MAIN DATA (Referral + Patient + BHW Info) */
$query = "SELECT r.*, p.*, u.fullname as bhw_name, r.status as referral_status
          FROM referrals r
          JOIN patients p ON r.patient_id = p.id
          JOIN users u ON r.referred_by = u.id
          WHERE r.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $referral_id);
$stmt->execute();
$referral = $stmt->get_result()->fetch_assoc();

if (!$referral) {
    die("Error: Record not found.");
}

// Tukuyin kung anong table ang kukunin base sa source_type
$category = $referral['source_type'];
$source_id = $referral['source_id'];
$form_details = [];

/* 2. FETCH SPECIFIC FORM DATA */
if ($category === 'checkup') {
    $sql_form = "SELECT * FROM checkup_visits WHERE id = ?";
} elseif ($category === 'immunization') {
    $sql_form = "SELECT * FROM immunization_visits WHERE id = ?";
} elseif ($category === 'family_planning') {
    $sql_form = "SELECT * FROM family_planning_visits WHERE id = ?";
}

$stmt_form = $conn->prepare($sql_form);
$stmt_form->bind_param("i", $source_id);
$stmt_form->execute();
$form_details = $stmt_form->get_result()->fetch_assoc();

/* 3. PREVIEW LOGIC PARA SA ML (Supervised Learning Simulation) */
// Dito papasok ang logic ng iyong prediction model base sa categories
$prediction_label = "Low Risk";
$prediction_color = "#2ecc71"; // Green

if ($category === 'checkup') {
    // Halimbawa: Predict High Risk kung mataas ang lagnat at BP
    if ($form_details['temperature'] >= 38.5 || $form_details['blood_pressure'] === '140/90') {
        $prediction_label = "High Risk / Urgent Attention";
        $prediction_color = "#e74c3c"; // Red
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Details - Nurse Panel</title>
    <style>
        :root { --primary: #2c3e50; --secondary: #34495e; --accent: #3498db; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .card { background: #fff; max-width: 900px; margin: 0 auto; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: var(--primary); color: #fff; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 30px; }
        .section-title { font-size: 1.2rem; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: var(--primary); }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
        .info-box { background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid var(--accent); }
        .info-label { display: block; font-size: 0.8rem; color: #7f8c8d; text-transform: uppercase; font-weight: bold; }
        .info-value { font-size: 1rem; color: #2c3e50; font-weight: 500; }
        .prediction-alert { padding: 15px; border-radius: 5px; color: #fff; font-weight: bold; text-align: center; margin-top: 20px; }
        .btn-print { background: #fff; color: var(--primary); border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <div>
            <h2 style="margin:0;">Referral Case #<?php echo $referral['id']; ?></h2>
            <small>Category: <?php echo strtoupper($category); ?></small>
        </div>
        <button class="btn-print" onclick="window.print()">Print Report</button>
    </div>

    <div class="card-body">
        <!-- Patient Profile -->
        <div class="section-title">Patient Profile</div>
        <div class="grid">
            <div class="info-box">
                <span class="info-label">Full Name</span>
                <span class="info-value"><?php echo $referral['firstname'] . ' ' . $referral['lastname']; ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Age / Gender</span>
                <span class="info-value"><?php echo $referral['age']; ?> yrs old / <?php echo $referral['gender']; ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Address</span>
                <span class="info-value"><?php echo $referral['address']; ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Referred By (BHW)</span>
                <span class="info-value"><?php echo $referral['bhw_name']; ?></span>
            </div>
        </div>

        <!-- Vital Signs -->
        <div class="section-title">Clinical Findings (Vital Signs)</div>
        <div class="grid">
            <div class="info-box">
                <span class="info-label">Blood Pressure</span>
                <span class="info-value"><?php echo $form_details['blood_pressure']; ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Temperature</span>
                <span class="info-value"><?php echo $form_details['temperature']; ?> °C</span>
            </div>
            <div class="info-box">
                <span class="info-label">Weight</span>
                <span class="info-value"><?php echo $form_details['weight']; ?> kg</span>
            </div>
        </div>

        <!-- Category Specific Details -->
        <div class="section-title">Specific Information: <?php echo ucfirst($category); ?></div>
        <div class="grid">
            <?php if ($category === 'checkup'): ?>
                <div class="info-box">
                    <span class="info-label">Symptoms Duration</span>
                    <span class="info-value"><?php echo $form_details['duration']; ?> days</span>
                </div>
                <div class="info-box" style="grid-column: span 2;">
                    <span class="info-label">Remarks</span>
                    <span class="info-value"><?php echo nl2br($form_details['symptoms']); ?></span>
                </div>

            <?php elseif ($category === 'immunization'): ?>
                <div class="info-box">
                    <span class="info-label">Vaccine / Dose</span>
                    <span class="info-value"><?php echo $form_details['vaccine_name'] . ' / Dose #' . $form_details['dose_number']; ?></span>
                </div>
                <div class="info-box">
                    <span class="info-label">Allergies</span>
                    <span class="info-value"><?php echo $form_details['has_allergy']; ?></span>
                </div>

            <?php elseif ($category === 'family_planning'): ?>
                <div class="info-box">
                    <span class="info-label">Method</span>
                    <span class="info-value"><?php echo $form_details['method']; ?></span>
                </div>
                <div class="info-box">
                    <span class="info-label">Last Menstrual Period</span>
                    <span class="info-value"><?php echo $form_details['lmp']; ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- ML Prediction Result -->
        <div class="section-title">System Analysis (ML Predicted)</div>
        <div class="prediction-alert" style="background-color: <?php echo $prediction_color; ?>;">
            Result: <?php echo $prediction_label; ?>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="nurse_dashboard.php" style="color: #7f8c8d; text-decoration: none;">← Back to Referrals List</a>
        </div>
    </div>
</div>

</body>
</html>
