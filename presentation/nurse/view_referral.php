<?php

include $_SERVER['DOCUMENT_ROOT'] . '/application/config/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/application/includes/session_check.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid referral ID");

/* GET REFERRAL */
$stmt = $conn->prepare("
    SELECT r.*, p.first_name, p.last_name
    FROM referrals r
    JOIN patients p ON r.patient_id = p.id
    WHERE r.id = :id
");
$stmt->execute([':id' => $id]);
$ref = $stmt->fetch();

if (!$ref) die("Referral not found");

/* AUTO SET IN PROGRESS */
if ($ref['status'] == 'Pending') {
    $conn->prepare("
        UPDATE referrals 
        SET status = 'In Progress' 
        WHERE id = :id
    ")->execute([':id' => $id]);
    $ref['status'] = 'In Progress';
}

/* GET LATEST VISIT */
$v = $conn->prepare("
    SELECT * FROM patient_visits
    WHERE patient_id = :pid
    ORDER BY created_at DESC
    LIMIT 1
");
$v->execute([':pid' => $ref['patient_id']]);
$checkup = $v->fetch();

/* ML PROCESS */
if (isset($_POST['submit_nurse'])) {

    $assessment     = $_POST['assessment'] ?? '';
    $assessment_low = strtolower($assessment);

    // STEP 1: Determine symptom keyword
    if (strpos($assessment_low, 'fever') !== false || strpos($assessment_low, 'lagnat') !== false || strpos($assessment_low, 'nilalagnat') !== false) {
        $symptom = 'fever';
    } elseif (strpos($assessment_low, 'cough') !== false || strpos($assessment_low, 'ubo') !== false) {
        $symptom = 'cough';
    } elseif (strpos($assessment_low, 'headache') !== false || strpos($assessment_low, 'masakit ang ulo') !== false) {
        $symptom = 'headache';
    } elseif (strpos($assessment_low, 'cold') !== false || strpos($assessment_low, 'sipon') !== false) {
        $symptom = 'cold';
    } elseif (strpos($assessment_low, 'chest pain') !== false) {
        $symptom = 'chest pain';
    } elseif (strpos($assessment_low, 'vaccine') !== false || strpos($assessment_low, 'immunization') !== false || strpos($assessment_low, 'bakuna') !== false || strpos($assessment_low, 'turok') !== false) {
        $symptom = 'BCG vaccine';
    } elseif (strpos($assessment_low, 'prenatal') !== false || strpos($assessment_low, 'pregnant') !== false) {
        $symptom = 'Prenatal checkup';
    } elseif (strpos($assessment_low, 'pills') !== false || strpos($assessment_low, 'birth control') !== false) {
        $symptom = 'Pills';
    } elseif (strpos($assessment_low, 'injectable') !== false || strpos($assessment_low, 'dmpa') !== false) {
        $symptom = 'Injectable DMPA';
    } elseif (strpos($assessment_low, 'implant') !== false) {
        $symptom = 'Implant';
    } elseif (strpos($assessment_low, 'family planning') !== false || strpos($assessment_low, 'condom') !== false || strpos($assessment_low, 'iud') !== false) {
        $symptom = 'Family Planning';
    } elseif (strpos($assessment_low, 'diarrhea') !== false || strpos($assessment_low, 'nagtatae') !== false) {
        $symptom = 'Nagtatae';
    } elseif (strpos($assessment_low, 'asthma') !== false) {
        $symptom = 'Asthma attack';
    } elseif (strpos($assessment_low, 'dengue') !== false) {
        $symptom = 'Dengue suspect';
    } elseif (strpos($assessment_low, 'hypertension') !== false || strpos($assessment_low, 'high blood') !== false || strpos($assessment_low, 'hi-blood') !== false) {
        $symptom = 'Hi-blood pressure';
    } elseif (strpos($assessment_low, 'uti') !== false) {
        $symptom = 'UTI symptoms';
    } elseif (strpos($assessment_low, 'arthritis') !== false) {
        $symptom = 'Arthritis';
    } elseif (strpos($assessment_low, 'muscle') !== false) {
        $symptom = 'Muscle pain';
    } elseif (strpos($assessment_low, 'stomach') !== false || strpos($assessment_low, 'tiyan') !== false) {
        $symptom = 'Stomach cramps';
    } else {
        $symptom = 'headache';
    }

    // ✅ STEP 2: Call Render ML API
    $ml_url  = "https://hms-ml-api.onrender.com/predict";
    $payload = json_encode(["symptom" => $symptom]);

    $ch = curl_init($ml_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    curl_close($ch);

    $label     = "Unknown";
    $score     = 0;
    $recommend = "No recommendation available.";

    if ($response) {
        $res       = json_decode($response, true);
        $label     = $res['label']     ?? "Unknown";
        $score     = $res['score']     ?? 0;
        $recommend = $res['recommend'] ?? "No recommendation available.";
    }

    // STEP 3: Save to DB
    $conn->prepare("
        UPDATE referrals
        SET nurse_assessment    = :a,
            ai_validation_label = :l,
            ai_validation_score = :s,
            ai_recommendation   = :r,
            status              = 'Completed'
        WHERE id = :id
    ")->execute([
        ':a'  => $assessment,
        ':l'  => $label,
        ':s'  => $score,
        ':r'  => $recommend,
        ':id' => $id
    ]);

    header("Location: view_referral.php?id=" . $id);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Referral Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    font-family: Poppins, sans-serif;
    background: #f4f7fb;
    padding: 20px;
}
.container { max-width: 900px; margin: auto; }
.header {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 15px;
}
.card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}
.badge-status {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    color: white;
    margin-top: 6px;
}
.status-pending     { background: #f39c12; }
.status-in-progress { background: #3498db; }
.status-completed   { background: #2ecc71; }
textarea {
    width: 100%;
    height: 110px;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-family: Poppins, sans-serif;
    resize: vertical;
}
.btn-ml {
    background: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    margin-top: 10px;
}
.btn-ml:hover { background: #0056b3; }
.ai-box {
    background: #e8f5e9;
    border-left: 5px solid #2ecc71;
}
.ai-box h3 { color: #27ae60; }
.recommend-box {
    background: #f0f8ff;
    border-left: 4px solid #4facfe;
    padding: 12px 15px;
    border-radius: 8px;
    margin-top: 12px;
    font-size: 14px;
    line-height: 1.7;
}
.info-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 8px;
}
.info-chip {
    background: #f0f4ff;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 13px;
    color: #333;
}
</style>
</head>

<body>
<a href="../nurse/dashboard.php" style="
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
    background-color: #fff;
    margin-bottom: 15px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
">← Back to Dashboard</a>
<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2 class="mb-1">👤 <?= htmlspecialchars($ref['first_name'] . ' ' . $ref['last_name']) ?></h2>
        <?php
            $statusClass = match(strtolower($ref['status'])) {
                'pending'     => 'status-pending',
                'in progress' => 'status-in-progress',
                'completed'   => 'status-completed',
                default       => 'status-pending'
            };
        ?>
        <span class="badge-status <?= $statusClass ?>">
            <?= htmlspecialchars($ref['status']) ?>
        </span>
    </div>

    <!-- REFERRAL INFO -->
    <div class="card">
        <h5>📋 Referral Info</h5>
        <div class="info-row">
            <div class="info-chip">📌 Purpose: <?= htmlspecialchars($ref['purpose'] ?? 'N/A') ?></div>
            <div class="info-chip">📅 Date: <?= $ref['created_at'] ?? 'N/A' ?></div>
        </div>
    </div>

    <!-- BHW LATEST VISIT -->
    <div class="card">
        <h5>🏥 BHW Latest Visit</h5>
        <div class="info-row">
            <div class="info-chip">🩸 BP: <?= htmlspecialchars($checkup['bp'] ?? 'N/A') ?></div>
            <div class="info-chip">🌡️ Temp: <?= htmlspecialchars($checkup['temperature'] ?? 'N/A') ?>°C</div>
            <div class="info-chip">💓 HR: <?= htmlspecialchars($checkup['heart_rate'] ?? 'N/A') ?> bpm</div>
        </div>
        <div class="mt-3">
            <b>Notes:</b> <?= htmlspecialchars($checkup['notes'] ?? 'N/A') ?>
        </div>
    </div>

    <!-- NURSE ASSESSMENT FORM -->
    <?php if ($ref['status'] != 'Completed'): ?>
    <div class="card">
        <h5>🩺 Nurse Assessment</h5>
        <form method="POST">
            <textarea name="assessment" placeholder="Enter clinical assessment here (e.g. patient has fever and cough...)" required></textarea>
            <button type="submit" name="submit_nurse" class="btn-ml">🤖 Trigger ML & Save</button>
        </form>
    </div>

    <?php else: ?>
    <!-- AI RESULT -->
    <div class="card ai-box">
        <h3>🤖 AI Result</h3>
        <div class="info-row">
            <div class="info-chip">🏷️ Classification: <b><?= htmlspecialchars($ref['ai_validation_label'] ?? 'N/A') ?></b></div>
            <div class="info-chip">📊 Confidence: <b><?= number_format($ref['ai_validation_score'] ?? 0, 2) ?>%</b></div>
        </div>
        <div class="recommend-box">
            <b>📋 Nurse Recommendation:</b><br>
            <?= htmlspecialchars($ref['ai_recommendation'] ?? 'No recommendation available.') ?>
        </div>
        <?php if (!empty($ref['nurse_assessment'])): ?>
        <div class="mt-3">
            <b>📝 Nurse Notes:</b><br>
            <small class="text-muted"><?= htmlspecialchars($ref['nurse_assessment']) ?></small>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
