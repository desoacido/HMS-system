<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_GET['id'])) {
    die("Referral ID is missing.");
}

$id = $_GET['id'];

try {
    /* 1. FETCH MAIN REFERRAL & PATIENT INFO */
    $stmt = $conn->prepare("
        SELECT r.*, p.firstname, p.lastname, p.birthdate, p.gender 
        FROM referrals r 
        JOIN patients p ON p.id = r.patient_id 
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $referral = $stmt->get_result()->fetch_assoc();

    if (!$referral) {
        die("Referral not found.");
    }

    $visit_id = $referral['visit_id'];
    $category = strtolower($referral['source_type']); // Ginawang lowercase para sa table mapping
    $source_id = $referral['source_id']; // Importante para sa FP 19 fields

    /* 2. AUTOMATIC STATUS TRANSITION (Pending to Viewed) */
    if ($referral['status'] == 'pending') {
        $upd = $conn->prepare("UPDATE referrals SET status='viewed' WHERE id=?");
        $upd->bind_param("i", $id);
        $upd->execute();
        $referral['status'] = 'viewed';
    }

    /* 3. DYNAMIC FETCHING BASED ON 3 CATEGORIES */
    $form = [];
    if ($category === 'checkup') {
        $stmt = $conn->prepare("SELECT * FROM checkup_visits WHERE visit_id = ?");
        $stmt->bind_param("i", $visit_id);
    } elseif ($category === 'immunization') {
        $stmt = $conn->prepare("SELECT * FROM immunization_visits WHERE visit_id = ?");
        $stmt->bind_param("i", $visit_id);
    } elseif ($category === 'family_planning' || $category === 'family planning') {
        // Ginagamit ang source_id dahil ito ang primary key sa family_planning_visits table
        $stmt = $conn->prepare("SELECT * FROM family_planning_visits WHERE id = ?");
        $stmt->bind_param("i", $source_id);
    }

    if (isset($stmt)) {
        $stmt->execute();
        $form = $stmt->get_result()->fetch_assoc();
    }

    // Age Calculation
    $birthdate = new DateTime($referral['birthdate']);
    $age = (new DateTime())->diff($birthdate)->y;

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nurse Portal - View Referral</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fb; padding: 20px; color: #333; }
        .container { max-width: 950px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 25px; }
        .patient-name { font-size: 24px; font-weight: 600; color: #2a5298; }
        .status-badge { padding: 6px 15px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .pending { background: #fff3cd; color: #856404; }
        .viewed { background: #d1ecf1; color: #0c5460; }
        
        .section-title { font-size: 16px; font-weight: 600; color: #2a5298; margin: 25px 0 10px 0; border-left: 5px solid #2a5298; padding-left: 10px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .info-card { background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #edf2f7; }
        .label { font-size: 11px; color: #718096; text-transform: uppercase; display: block; margin-bottom: 4px; font-weight: 600; }
        .value { font-size: 15px; font-weight: 600; color: #2d3748; }
        .risk-yes { color: #d32f2f; } /* Pula para sa alert */
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <div class="patient-name"><?= htmlspecialchars($referral['firstname'] . ' ' . $referral['lastname']) ?></div>
            <small>Age: <?= $age ?> | Gender: <?= $referral['gender'] ?></small>
        </div>
        <span class="status-badge <?= $referral['status'] ?>"><?= $referral['status'] ?></span>
    </div>

    <!-- CATEGORY INDICATOR -->
    <div class="info-grid" style="margin-bottom: 20px;">
        <div class="info-card"><span class="label">Referral Type</span><div class="value"><?= strtoupper($category) ?></div></div>
        <div class="info-card"><span class="label">Referral Date</span><div class="value"><?= date('M d, Y | h:i A', strtotime($referral['created_at'])) ?></div></div>
    </div>

    <?php if ($form): ?>
        
        <!-- VITALS (COMMON TO MOST) -->
        <div class="section-title">Clinical Vitals</div>
        <div class="info-grid">
            <div class="info-card"><span class="label">Blood Pressure</span><div class="value"><?= $form['blood_pressure'] ?? 'N/A' ?></div></div>
            <div class="info-card"><span class="label">Weight</span><div class="value"><?= $form['weight'] ?? '0' ?> kg</div></div>
            <?php if(isset($form['heart_rate'])): ?><div class="info-card"><span class="label">Heart Rate</span><div class="value"><?= $form['heart_rate'] ?> bpm</div></div><?php endif; ?>
        </div>

        <!-- CATEGORY SPECIFIC DATA -->

        <?php if ($category === 'checkup'): ?>
            <div class="section-title">Checkup Findings</div>
            <div class="info-grid">
                <div class="info-card"><span class="label">Temperature</span><div class="value"><?= $form['temperature'] ?> °C</div></div>
                <div class="info-card"><span class="label">SpO2</span><div class="value"><?= $form['spo2'] ?>%</div></div>
                <div class="info-card"><span class="label">Symptoms Duration</span><div class="value"><?= $form['duration'] ?> Days</div></div>
                <div class="info-card" style="grid-column: span 2;"><span class="label">Remarks</span><div class="value"><?= nl2br(htmlspecialchars($form['symptoms'])) ?></div></div>
            </div>

        <?php elseif ($category === 'immunization'): ?>
            <div class="section-title">Immunization Details</div>
            <div class="info-grid">
                <div class="info-card"><span class="label">Vaccine Name</span><div class="value"><?= $form['vaccine_name'] ?></div></div>
                <div class="info-card"><span class="label">Has Allergy?</span><div class="value"><?= $form['has_allergy'] ?></div></div>
                <div class="info-card" style="grid-column: span 2;"><span class="label">Allergy Notes</span><div class="value"><?= htmlspecialchars($form['allergy_notes'] ?: 'None') ?></div></div>
            </div>

        <?php elseif ($category === 'family_planning' || $category === 'family planning'): ?>
            <div class="section-title">FP Method & History</div>
            <div class="info-grid">
                <div class="info-card"><span class="label">Method</span><div class="value"><?= $form['method'] ?></div></div>
                <div class="info-card"><span class="label">LMP</span><div class="value"><?= $form['lmp'] ?></div></div>
                <div class="info-card"><span class="label">No. of Children</span><div class="value"><?= $form['num_children'] ?></div></div>
                <div class="info-card"><span class="label">Months on Method</span><div class="value"><?= $form['months_on_method'] ?></div></div>
            </div>

            <div class="section-title">Medical Screening (Risk Factors)</div>
            <div class="info-grid">
                <?php 
                $risks = [
                    'is_pregnant' => 'Pregnant?', 'is_breastfeeding' => 'Breastfeeding?', 
                    'is_smoker' => 'Smoker?', 'has_hypertension' => 'Hypertension?', 
                    'has_diabetes' => 'Diabetes?', 'has_blood_clots' => 'Blood Clots?', 
                    'has_migraines' => 'Migraines?'
                ];
                foreach($risks as $key => $label): 
                    $val = $form[$key] ?? 'No';
                ?>
                <div class="info-card">
                    <span class="label"><?= $label ?></span>
                    <div class="value <?= ($val == 'Yes') ? 'risk-yes' : '' ?>"><?= $val ?></div>
                </div>
                <?php endforeach; ?>
                <div class="info-card" style="grid-column: span 2;">
                    <span class="label">Side Effects / Notes</span>
                    <div class="value"><?= nl2br(htmlspecialchars($form['side_effects'] ?? 'None')) ?></div>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div style="background: #fff5f5; color: #c53030; padding: 20px; border-radius: 10px; margin-top: 20px; border: 1px solid #feb2b2;">
            ⚠️ <strong>Data Fetching Error:</strong> Hindi mahanap ang detalye ng form sa database. Mangyaring i-check ang <u>visit_id</u> o <u>source_id</u>.
        </div>
    <?php endif; ?>

    <!-- ML BUTTON -->
    <div class="section-title">Analysis</div>
    <div class="ml-box">
        <form action="checkup_ml.php" method="GET">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit" class="btn-run">🚀 RUN ML ANALYSIS</button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="nurse_dashboard.php" style="color: #718096; text-decoration: none;">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
