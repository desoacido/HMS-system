<?php
session_start();
include __DIR__ . '/db.php';

$visit_id = $_GET['visit_id'] ?? null;

if (!$visit_id) {
    die("Invalid visit ID");
}

/* ================= GET VISIT + PATIENT + BHW ================= */
$stmt = $conn->prepare("
    SELECT v.*, 
           p.firstname, p.lastname,
           u.fullname AS bhw_name
    FROM visits v
    JOIN patients p ON p.id = v.patient_id
    LEFT JOIN users u ON u.id = v.attended_by
    WHERE v.id = ?
");
$stmt->execute([$visit_id]);
$visit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visit) {
    die("Visit not found");
}

$category = $visit['category'];

/* ================= CHECK IF ALREADY REFERRED ================= */
$check = $conn->prepare("
    SELECT id FROM referrals 
    WHERE visit_id = ?
");
$check->execute([$visit_id]);
$already_referred = $check->rowCount() > 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Visit Details</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body {
    font-family:Poppins;
    background:#f4f7fb;
    padding:30px;
}

.card {
    max-width:700px;
    margin:auto;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    margin-bottom:15px;
}

h2 {
    color:#1a7a4a;
}

.label {
    font-weight:600;
    margin-top:10px;
}

.value {
    margin-bottom:8px;
    font-size:14px;
}

.success {
    max-width:700px;
    margin:auto;
    background:#28a745;
    color:white;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
}

.btn-group {
    max-width:700px;
    margin:auto;
    display:flex;
    justify-content:space-between;
}

.btn {
    padding:8px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.back { background:#6c757d; color:white; }
.refer { background:#ff9800; color:white; }
.disabled { background:gray; color:white; cursor:not-allowed; }
</style>
</head>

<body>

<!-- SUCCESS MESSAGE -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="success">
        <?= $_SESSION['success']; ?>
    </div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- PATIENT INFO -->
<div class="card">
<h2>👤 Patient Info</h2>

<div class="value"><b>Name:</b> <?= htmlspecialchars($visit['firstname'].' '.$visit['lastname']) ?></div>
<div class="value"><b>Category:</b> <?= strtoupper($category) ?></div>
<div class="value"><b>Date:</b> <?= $visit['visit_date'] ?></div>
<div class="value"><b>Attended By:</b> <?= htmlspecialchars($visit['bhw_name'] ?? '—') ?></div>
</div>

<!-- CHECKUP DETAILS -->
<?php if ($category == 'checkup'): ?>

<?php
$stmt = $conn->prepare("SELECT * FROM checkup_visits WHERE visit_id = ?");
$stmt->execute([$visit_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card">
<h2>🩺 Checkup Details</h2>

<div class="label">Temperature</div>
<div class="value"><?= $data['temperature'] ?? '—' ?></div>

<div class="label">Blood Pressure</div>
<div class="value"><?= $data['blood_pressure'] ?? '—' ?></div>

<div class="label">Heart Rate</div>
<div class="value"><?= $data['heart_rate'] ?? '—' ?></div>

<div class="label">SpO2</div>
<div class="value"><?= $data['spo2'] ?? '—' ?></div>

<div class="label">Weight</div>
<div class="value"><?= $data['weight'] ?? '—' ?></div>

<div class="label">Symptoms</div>
<div class="value"><?= $data['symptoms'] ?? '—' ?></div>

<div class="label">Duration</div>
<div class="value"><?= $data['duration'] ?? '—' ?></div>

</div>

<?php endif; ?>

<!-- BUTTONS -->
<div class="btn-group">

    <!-- BACK -->
    <button class="btn back"
        onclick="window.location='bhw_patientHistory.php?patient_id=<?= $visit['patient_id'] ?>'">
        ⬅ Back
    </button>

    <!-- REFER -->
    <?php if ($already_referred): ?>

        <button class="btn disabled" disabled>
            ✅ Sent to Nurse
        </button>

    <?php else: ?>

        <button class="btn refer"
        onclick="window.location='bhw_referral.php?visit_id=<?= $visit_id ?>&patient_id=<?= $visit['patient_id'] ?>'">
            🔄 Refer
        </button>

    <?php endif; ?>

</div>

</body>
</html>