<?php
include 'db.php';

if (!isset($_GET['id'])) {
    die("Referral ID is missing.");
}

$id = $_GET['id'];

try {
    /* 1. FETCH MAIN REFERRAL DATA & PATIENT INFO */
    $stmt = $conn->prepare("
        SELECT r.*, p.firstname, p.lastname, p.birthdate, p.gender 
        FROM referrals r 
        JOIN patients p ON p.id = r.patient_id 
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $referral = $result->fetch_assoc();

    if (!$referral) {
        die("Referral not found.");
    }

    $visit_id = $referral['visit_id'];
    $category = $referral['source_type'];

    /* 2. AUTOMATIC STATUS TRANSITION */
    if ($referral['status'] == 'pending') {
        $upd = $conn->prepare("UPDATE referrals SET status='viewed' WHERE id=?");
        $upd->bind_param("i", $id);
        $upd->execute();
        $referral['status'] = 'viewed';
    }

    /* 3. FETCH SPECIFIC FORM DATA BASED ON CATEGORY */
    $form = [];
    $table_map = [
        'checkup' => 'checkup_visits',
        'immunization' => 'immunization_visits',
        'family_planning' => 'family_planning_visits'
    ];

    if (array_key_exists($category, $table_map)) {
        $table = $table_map[$category];
        $stmt = $conn->prepare("SELECT * FROM $table WHERE visit_id=?");
        $stmt->bind_param("i", $visit_id);
        $stmt->execute();
        $form = $stmt->get_result()->fetch_assoc();
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .completed { background: #d4edda; color: #155724; }
        .section-title { font-size: 16px; font-weight: 600; color: #2a5298; margin: 25px 0 10px 0; display: flex; align-items: center; }
        .section-title::before { content: ''; display: inline-block; width: 4px; height: 18px; background: #2a5298; margin-right: 10px; border-radius: 2px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .info-card { background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #edf2f7; }
        .label { font-size: 11px; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }
        .value { font-size: 15px; font-weight: 600; color: #2d3748; }
        .ml-box { background: #fff; border: 2px dashed #cbd5e0; padding: 20px; border-radius: 12px; margin-top: 20px; text-align: center; }
        .btn-run { background: #2a5298; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <span class="label">Patient Name</span>
            <div class="patient-name"><?= htmlspecialchars($referral['firstname'] . ' ' . $referral['lastname']) ?></div>
        </div>
        <span class="status-badge <?= strtolower($referral['status']) ?>"><?= $referral['status'] ?></span>
    </div>

    <div class="section-title">General Information</div>
    <div class="info-grid">
        <div class="info-card"><span class="label">Gender</span><div class="value"><?= ucfirst($referral['gender']) ?></div></div>
        <div class="info-card"><span class="label">Category</span><div class="value"><?= strtoupper($category) ?></div></div>
        <div class="info-card"><span class="label">Referral Date</span><div class="value"><?= date('M d, Y | h:i A', strtotime($referral['created_at'])) ?></div></div>
    </div>

    <div class="section-title"><?= strtoupper($category) ?> Clinical Data</div>
    
    <?php if ($form): ?>
        <div class="info-grid">
            <div class="info-card"><span class="label">Blood Pressure</span><div class="value"><?= htmlspecialchars($form['blood_pressure'] ?? 'N/A') ?></div></div>
            <div class="info-card"><span class="label">Heart Rate</span><div class="value"><?= htmlspecialchars($form['heart_rate'] ?? 'N/A') ?> bpm</div></div>
            <div class="info-card"><span class="label">Weight</span><div class="value"><?= htmlspecialchars($form['weight'] ?? '0') ?> kg</div></div>

            <?php if ($category == 'checkup'): ?>
                <div class="info-card"><span class="label">Temperature</span><div class="value"><?= htmlspecialchars($form['temperature'] ?? '0') ?> °C</div></div>
                <div class="info-card"><span class="label">SpO2</span><div class="value"><?= htmlspecialchars($form['spo2'] ?? '0') ?>%</div></div>
                <div class="info-card" style="grid-column: span 2;"><span class="label">Symptoms</span><div class="value"><?= nl2br(htmlspecialchars($form['symptoms'] ?? 'None')) ?></div></div>

            <?php elseif ($category == 'immunization'): ?>
                <div class="info-card"><span class="label">Vaccine</span><div class="value"><?= htmlspecialchars($form['vaccine_name'] ?? 'N/A') ?></div></div>
                <div class="info-card" style="grid-column: span 2;"><span class="label">Allergy Notes</span><div class="value"><?= htmlspecialchars($form['allergy_notes'] ?? 'None') ?></div></div>

            <?php elseif ($category == 'family_planning' || $category == 'FAMILY_PLANNING'): ?>
                <div class="info-card"><span class="label">Method</span><div class="value"><?= htmlspecialchars($form['method'] ?? 'N/A') ?></div></div>
                <div class="info-card" style="grid-column: span 2;">
                    <span class="label">Clinical Notes</span>
                    <div class="value">
                        <?php 
                            // Sinubukan nating i-check ang iba pang posibleng pangalan ng column para sa notes
                            echo htmlspecialchars($form['fp_notes'] ?? $form['notes'] ?? $form['remarks'] ?? 'None'); 
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p class="error-msg">⚠️ No clinical data found for this referral.</p>
    <?php endif; ?>

    <div class="section-title">Machine Learning Recommendation</div>
    <div class="ml-box">
        <form action="checkup_ml.php" method="GET">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <button type="submit" class="btn-run">🚀 RUN ML ANALYSIS</button>
        </form>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <a href="nurse_dashboard.php" style="text-decoration: none; color: #718096; font-size: 14px;">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
