<?php
include '../../application/config/db.php';
include '../../application/includes/session_check.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid referral ID");

$stmt = $conn->prepare("
    SELECT r.*, p.first_name, p.last_name 
    FROM referrals r 
    JOIN patients p ON r.patient_id = p.id 
    WHERE r.id = :id
");
$stmt->execute([':id' => $id]);
$ref = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ref) die("Referral not found");

// UPDATE STATUS
if ($ref['status'] == 'Pending') {
    $conn->prepare("UPDATE referrals SET status = 'In Progress' WHERE id = :id")
         ->execute([':id' => $id]);
    $ref['status'] = 'In Progress';
}

// VISITS
$visitStmt = $conn->prepare("
    SELECT * FROM patient_visits 
    WHERE patient_id = :pid 
    ORDER BY created_at DESC
");
$visitStmt->execute([':pid' => $ref['patient_id']]);
$visits = $visitStmt->fetchAll(PDO::FETCH_ASSOC);

// AI DATA
$ai_label = $ref['ai_validation_label'] ?? '';
$raw_score = (float)($ref['ai_validation_score'] ?? 0);

$ai_score = ($raw_score > 100) ? $raw_score / 100 : $raw_score;
if ($ai_score < 1) $ai_score *= 100;

$recommendation = "";
$box_color = "#f8f9fa";
$text_color = "#333";

if (!empty($ai_label)) {
    switch($ai_label) {

        case 'Immunization':
            $recommendation = "Verify vaccine age suitability. Monitor side effects.";
            $box_color = "#d4edda"; $text_color = "#155724";
            break;

        case 'Family Planning':
            $recommendation = "Provide counseling and assess contraindications.";
            $box_color = "#cce5ff"; $text_color = "#004085";
            break;

        case 'Urgent':
            $recommendation = "Immediate referral to physician. Monitor vitals closely.";
            $box_color = "#f8d7da"; $text_color = "#721c24";
            break;

        default:
            $recommendation = "Continue standard monitoring and assessment.";
            $box_color = "#fff3cd"; $text_color = "#856404";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Referral Details</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:#f4f7fb;
    padding:25px;
}

.container{
    max-width:950px;
    margin:auto;
}

/* HEADER CARD */
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 10px 20px rgba(0,0,0,0.08);
    margin-bottom:20px;
}

/* ML BOX */
.ml-box{
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
}

/* TEXTAREA */
textarea{
    width:100%;
    height:90px;
    padding:12px;
    border-radius:8px;
    border:1px solid #ddd;
    outline:none;
    margin-top:5px;
}

/* BUTTON */
.btn{
    background:#007bff;
    color:white;
    padding:12px 20px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
}

.btn:hover{
    opacity:0.9;
}

/* HISTORY */
.history{
    background:white;
    padding:15px;
    margin:10px 0;
    border-left:5px solid #007bff;
    border-radius:8px;
    box-shadow:0 5px 10px rgba(0,0,0,0.05);
}

/* BACK BUTTON */
.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#555;
    font-weight:600;
}
</style>
</head>

<body>

<div class="container">

<a href="/hms2/presentation/nurse/dashboard.php" class="back">
    ⬅ Back to Dashboard
</a>

<h2>📋 Referral Details</h2>

<div class="card">
    <p><b>Patient:</b> <?= htmlspecialchars($ref['first_name']." ".$ref['last_name']) ?></p>
    <p><b>Reason:</b> <?= htmlspecialchars($ref['reason']) ?></p>
    <p><b>Status:</b> <b style="color:#007bff;"><?= strtoupper($ref['status']) ?></b></p>
</div>

<?php if (!empty($ai_label)): ?>
<div class="ml-box" style="background:<?= $box_color ?>; color:<?= $text_color ?>;">
    <h3>🤖 AI Validation</h3>
    <p><b>Classification:</b> <?= htmlspecialchars($ai_label) ?></p>
    <p><b>Confidence:</b> <?= number_format($ai_score, 2) ?>%</p>

    <div style="margin-top:10px;">
        <b>Recommendations:</b><br>
        <?= $recommendation ?>
    </div>
</div>
<?php endif; ?>

<div class="card">

<h3>🩺 Nurse Assessment</h3>

<form action="../../application/controllers/nursecontroller.php" method="POST">

<input type="hidden" name="referral_id" value="<?= $ref['id'] ?>">
<input type="hidden" name="patient_id" value="<?= $ref['patient_id'] ?>">

<label>Assessment</label>
<textarea name="assessment" required>
<?= htmlspecialchars($ref['nurse_assessment'] ?? '') ?>
</textarea>

<br><br>

<label>Diagnosis</label>
<textarea name="diagnosis">
<?= htmlspecialchars($ref['nurse_diagnosis'] ?? '') ?>
</textarea>

<br><br>

<button type="submit" name="submit_nurse" class="btn">
    💾 Save & Trigger ML
</button>

</form>

</div>

<h3>📋 Visit History</h3>

<?php if (!empty($visits)): ?>
    <?php foreach ($visits as $v): ?>
        <div class="history">
            <b><?= htmlspecialchars($v['category']) ?></b>
            <small>(<?= date('M d, Y', strtotime($v['created_at'])) ?>)</small>
            <br><br>
            BP: <?= $v['bp'] ?> |
            Temp: <?= $v['temperature'] ?>°C |
            HR: <?= $v['heart_rate'] ?> bpm
            <br>
            Notes: <?= htmlspecialchars($v['notes']) ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No visit history found.</p>
<?php endif; ?>

</div>

</body>
</html>