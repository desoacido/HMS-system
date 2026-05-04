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
    $stmt->execute([$id]);
    $referral = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$referral) {
        die("Referral not found.");
    }

    $visit_id = $referral['visit_id'];
    $category = $referral['source_type'];

    /* 2. AUTOMATIC STATUS TRANSITION (Pending to Viewed) */
    if ($referral['status'] == 'pending') {
        $conn->prepare("UPDATE referrals SET status='viewed' WHERE id=?")->execute([$id]);
        $referral['status'] = 'viewed';
    }

    /* 3. FETCH SPECIFIC FORM DATA BASED ON CATEGORY */
    $form = [];
    if ($category == 'checkup') {
        $stmt = $conn->prepare("SELECT * FROM checkup_visits WHERE visit_id=?");
        $stmt->execute([$visit_id]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($category == 'immunization') {
        $stmt = $conn->prepare("SELECT * FROM immunization_visits WHERE visit_id=?");
        $stmt->execute([$visit_id]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($category == 'family_planning') {
        $stmt = $conn->prepare("SELECT * FROM family_planning_visits WHERE visit_id=?");
        $stmt->execute([$visit_id]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
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
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        
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
        .ml-content { text-align: left; background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 5px solid #2a5298; margin-bottom: 15px; }
        
        .btn-run { background: #2a5298; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-run:hover { background: #1e3c72; transform: translateY(-2px); }
        
        .error-msg { background: #fff5f5; color: #c53030; padding: 15px; border-radius: 10px; border: 1px solid #feb2b2; }
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

    <!-- 1. BASIC INFORMATION -->
    <div class="section-title">General Information</div>
    <div class="info-grid">
        <div class="info-card"><span class="label">Gender</span><div class="value"><?= ucfirst($referral['gender']) ?></div></div>
        <div class="info-card"><span class="label">Category</span><div class="value"><?= strtoupper($category) ?></div></div>
        <div class="info-card"><span class="label">Referral Date</span><div class="value"><?= date('M d, Y | h:i A', strtotime($referral['created_at'])) ?></div></div>
    </div>

    <!-- 2. DYNAMIC VISIT DETAILS -->
    <div class="section-title"><?= strtoupper($category) ?> Clinical Data</div>
    
    <?php if ($form): ?>
        <div class="info-grid">
            
           
            <div class="info-card"><span class="label">Blood Pressure</span><div class="value"><?= htmlspecialchars($form['blood_pressure']) ?></div></div>
            <div class="info-card"><span class="label">Weight</span><div class="value"><?= htmlspecialchars($form['weight']) ?> kg</div></div>
            <div class="info-card"><span class="label">Heart Rate</span><div class="value"><?= htmlspecialchars($form['heart_rate'] ?? 'N/A') ?> bpm</div></div>

            <!-- Immunization Specific (Based on Screenshot 313) -->
            <?php if ($category == 'immunization'): ?>
                <div class="info-card"><span class="label">Has Allergy?</span><div class="value"><?= htmlspecialchars($form['has_allergy']) ?></div></div>
                <div class="info-card"><span class="label">Has Fever?</span><div class="value"><?= htmlspecialchars($form['has_fever']) ?></div></div>
                <div class="info-card" style="grid-column: span 2;"><span class="label">Allergy Notes</span><div class="value"><?= htmlspecialchars($form['allergy_notes'] ?: 'None') ?></div></div>
            <?php endif; ?>

            <!-- Add other specific fields for family_planning or checkup here if needed -->
        </div>
    <?php else: ?>
        <div class="error-msg">
            <strong>⚠️ Data Not Found:</strong> Could not retrieve detailed records from <u><?= $category ?>_visits</u> for Visit ID: <?= $visit_id ?>.
        </div>
    <?php endif; ?>

    <!-- 3. ML ANALYSIS SECTION -->
    <!-- 3. ML ANALYSIS SECTION -->
<div class="section-title">Machine Learning Recommendation</div>
<div class="ml-box">
    <?php if ($referral['status'] == 'completed' && !empty($referral['ml_result'])): ?>
        <div class="ml-content">
            <strong>ML Result:</strong><br>
            <?= nl2br(htmlspecialchars($referral['ml_result'])) ?>
        </div>
        <p style="color: green;">✔ Transaction Completed</p>
    <?php else: ?>
        <p>No prediction generated yet.</p>

        <!-- ✅ FIXED: Use $id and $form instead of $details -->
        <form action="checkup_ml.php" method="GET">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <button type="submit" class="btn-run" style="background-color: #2a5298; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                🚀 RUN ML ANALYSIS
            </button>
        </form>
    <?php endif; ?>
</div>
    <div style="margin-top: 30px; text-align: center;">
        <a href="nurse_dashboard.php" style="text-decoration: none; color: #718096; font-size: 14px;">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>