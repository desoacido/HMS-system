<?php
include $_SERVER['DOCUMENT_ROOT'] . '/application/config/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/application/includes/session_check.php';

$stmt = $conn->query("SELECT * FROM patients ORDER BY id DESC");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BHW Patient List</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }

body {
    background:#f4f7fb;
    padding:25px;
}

.back {
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#555;
    background:#fff;
    padding:8px 12px;
    border-radius:8px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    transition:0.3s;
}
.back:hover { transform:translateY(-2px); }

.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

h2 { color:#333; }

.search-box input {
    padding:10px;
    width:300px;
    border-radius:8px;
    border:1px solid #ccc;
    outline:none;
}

.patient-list {
    display:flex;
    flex-direction:column;
    gap:10px;
}

.patient-card {
    background:white;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.07);
    overflow:hidden;
}

.patient-name {
    padding:15px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    cursor:pointer;
    transition:0.2s;
}

.patient-name:hover {
    background:#f0fdf4;
}

.patient-name h3 {
    font-size:15px;
    color:#333;
    font-weight:600;
}

.patient-name .meta {
    font-size:12px;
    color:#999;
    margin-top:3px;
    font-weight:400;
}

.arrow {
    color:#43e97b;
    font-size:18px;
    transition:transform 0.3s;
}

.arrow.open {
    transform:rotate(180deg);
}

.patient-details {
    display:none;
    padding:15px 20px;
    border-top:1px solid #f0f0f0;
    background:#fafafa;
}

.patient-details.show {
    display:block;
}

.detail-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
    margin-bottom:15px;
}

.detail-item {
    font-size:13px;
}

.detail-item span {
    color:#999;
    display:block;
    font-size:11px;
}

.detail-item strong {
    color:#333;
}

.qr-section {
    display:flex;
    align-items:center;
    gap:15px;
    margin-bottom:15px;
}

.qr-section img {
    border-radius:8px;
    width:70px;
    height:70px;
}

.no-qr {
    font-size:12px;
    color:#999;
}

.actions {
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.actions a {
    padding:7px 14px;
    border-radius:8px;
    font-size:12px;
    text-decoration:none;
    font-weight:600;
    transition:0.2s;
}

.actions a:hover { opacity:0.85; }

.qr      { background:#e3f2fd; color:#0d47a1; }
.visit   { background:#e8f5e9; color:#1b5e20; }
.history { background:#fff3e0; color:#e65100; }
.record  { background:#f3e5f5; color:#6a1b9a; }
</style>
</head>

<body>

<a href="/presentation/bhw/dashboard.php" class="back">⬅ Back to Dashboard</a>

<div class="header">
    <h2>Patient List (BHW)</h2>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search patient name...">
    </div>
</div>

<div class="patient-list" id="patientList">
    <?php foreach ($patients as $p): ?>
    <?php
        $age = 'N/A';
        if (!empty($p['birthdate'])) {
            $birth = new DateTime($p['birthdate']);
            $today = new DateTime();
            $age = $birth->diff($today)->y;
        }
        $fullName = $p['first_name'] . " " . $p['last_name'];
    ?>
    <div class="patient-card" data-name="<?= strtolower($fullName) ?>">

        <!-- CLICKABLE NAME -->
        <div class="patient-name" onclick="toggleDetails(this)">
            <div>
                <h3><?= $fullName ?></h3>
                <div class="meta">ID #<?= $p['id'] ?> &nbsp;|&nbsp; Age: <?= $age ?></div>
                <div class="meta">📅 <?= $p['birthdate'] ?? 'N/A' ?> &nbsp;|&nbsp; 📍 <?= $p['address'] ?? 'N/A' ?></div>
            </div>
            <span class="arrow">▼</span>
        </div>

        <!-- HIDDEN DETAILS -->
        <div class="patient-details">
            <div class="detail-grid">
                <div class="detail-item">
                    <span>Contact</span>
                    <strong><?= $p['contact_number'] ?? 'N/A' ?></strong>
                </div>
                <div class="detail-item">
                    <span>Birthdate</span>
                    <strong><?= $p['birthdate'] ?? 'N/A' ?></strong>
                </div>
                <div class="detail-item">
                    <span>Address</span>
                    <strong><?= $p['address'] ?? 'N/A' ?></strong>
                </div>
                <div class="detail-item">
                    <span>Age</span>
                    <strong><?= $age ?></strong>
                </div>
            </div>

            <!-- QR CODE -->
            <div class="qr-section">
                <?php if (!empty($p['qr_code'])): ?>
                    <img src="<?= $p['qr_code'] ?>">
                <?php else: ?>
                    <span class="no-qr">No QR Code yet</span>
                <?php endif; ?>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="actions">
                <a class="qr"      href="/presentation/qrcode/generate_patient_qr.php?id=<?= $p['id'] ?>">🔲 QR</a>
                <a class="visit"   href="select_category.php?patient_id=<?= $p['id'] ?>">🩺 Visit</a>
                <a class="history" href="view_patient_history.php?patient_id=<?= $p['id'] ?>">📋 History</a>
                <a class="record"  href="select_category.php?patient_id=<?= $p['id'] ?>">➕ New</a>
            </div>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<script>
function toggleDetails(nameEl) {
    const details = nameEl.nextElementSibling;
    const arrow = nameEl.querySelector('.arrow');
    details.classList.toggle('show');
    arrow.classList.toggle('open');
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('.patient-card').forEach(card => {
        const name = card.getAttribute('data-name');
        card.style.display = name.includes(filter) ? '' : 'none';
    });
});
</script>

</body>
</html>
