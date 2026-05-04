<?php
session_start();
include __DIR__ . '/db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: bhw_patientlist.php"); exit(); }

// PATIENT INFORMATION
$stmt = $conn->prepare("
    SELECT p.*, u.fullname as bhw_name
    FROM patients p
    LEFT JOIN users u ON u.id = p.registered_by
    WHERE p.id = ?
");
$stmt->bind_param("i", $id); // Sa MySQLi, kailangan ng bind_param para sa '?'
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();

if (!$p) { die("Patient not found."); }

// VISIT HISTORY
$vStmt = $conn->prepare("SELECT * FROM visits WHERE patient_id = ? ORDER BY visit_date DESC");
$vStmt->bind_param("i", $id);
$vStmt->execute();
$vRes = $vStmt->get_result();
$visitHistory = $vRes->fetch_all(MYSQLI_ASSOC);
$visitCount   = count($visitHistory);
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { padding:30px; background:#f4f7fb; }

    .back-btn {
        display:inline-flex; align-items:center; gap:6px;
        color:#1a7a4a; text-decoration:none;
        font-size:13px; margin-bottom:20px; font-weight:600;
        cursor:pointer; background:none; border:none;
    }
    .back-btn:hover { text-decoration:underline; }

    .profile-grid {
        display:grid; grid-template-columns:1fr 1fr;
        gap:20px; margin-bottom:20px;
    }

    .card {
        background:white; padding:25px;
        border-radius:14px;
        box-shadow:0 4px 16px rgba(0,0,0,0.07);
    }
    .card h3 {
        color:#1a7a4a; font-size:15px;
        margin-bottom:15px; padding-bottom:10px;
        border-bottom:2px solid #f0f0f0;
    }

    .info-row {
        display:flex; justify-content:space-between;
        margin-bottom:10px; font-size:13px;
    }
    .info-row .label { color:#888; }
    .info-row .value { color:#333; font-weight:600; }

    .qr-card { text-align:center; }
    .qr-img {
        width:160px; height:160px;
        border:3px solid #28a745; border-radius:12px;
        padding:8px; cursor:pointer; transition:0.2s;
        margin:10px 0 15px;
    }
    .qr-img:hover { transform:scale(1.05); }

    .badge { padding:5px 14px; border-radius:20px; font-size:12px; font-weight:600; }
    .badge-new       { background:#d4edda; color:#155724; }
    .badge-returning { background:#cce5ff; color:#004085; }

    .actions-card { margin-bottom:20px; }
    .action-btns  { display:flex; gap:12px; flex-wrap:wrap; }
    .action-btn {
        padding:12px 22px; border:none; border-radius:10px;
        cursor:pointer; font-size:13px;
        font-family:'Poppins',sans-serif; font-weight:600;
        transition:0.2s; display:flex; align-items:center; gap:8px;
    }
    .action-btn:hover { transform:translateY(-2px); opacity:0.9; }
    .btn-visit  { background:linear-gradient(135deg,#1a7a4a,#28a745); color:white; }
    .btn-refer  { background:linear-gradient(135deg,#e67e00,#f0a500); color:white; }
    .btn-gen-qr { background:linear-gradient(135deg,#1e3c72,#2a5298); color:white; }

    .visit-table { width:100%; border-collapse:collapse; font-size:13px; }
    .visit-table th { background:#f4f7fb; color:#555; padding:10px 12px; text-align:left; }
    .visit-table td { padding:10px 12px; border-bottom:1px solid #eee; }

    .cat-badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .cat-immunization    { background:#fff3cd; color:#856404; }
    .cat-checkup         { background:#cce5ff; color:#004085; }
    .cat-family_planning { background:#f8d7da; color:#721c24; }

    /* QR MODAL */
    .modal-bg {
        display:none; position:fixed; inset:0;
        background:rgba(0,0,0,0.6);
        justify-content:center; align-items:center; z-index:9999;
    }
    .modal-bg.show { display:flex; }
    .modal {
        background:white; padding:35px; border-radius:16px;
        text-align:center; max-width:340px; width:90%;
        position:relative; animation:popIn 0.3s ease;
    }
    @keyframes popIn {
        from { transform:scale(0.8); opacity:0; }
        to   { transform:scale(1);   opacity:1; }
    }
    .close-btn { position:absolute; top:12px; right:15px; font-size:20px; cursor:pointer; color:#aaa; background:none; border:none; }
    .modal h3  { color:#1a7a4a; margin-bottom:4px; }
    .modal p   { color:#888; font-size:12px; margin-bottom:15px; }
    .qr-big    { width:200px; height:200px; border:3px solid #28a745; border-radius:12px; padding:8px; margin-bottom:15px; }
    .modal-btns { display:flex; gap:8px; justify-content:center; }
    .btn-sm { padding:8px 16px; border:none; border-radius:8px; cursor:pointer; font-size:12px; font-family:'Poppins',sans-serif; font-weight:600; }
    .btn-sm-green { background:linear-gradient(135deg,#1a7a4a,#28a745); color:white; }
    .btn-sm-blue  { background:linear-gradient(135deg,#1e3c72,#2a5298); color:white; text-decoration:none; display:inline-block; }
    .btn-sm-gray  { background:#eee; color:#555; }
</style>
</head>
<body>

<button class="back-btn" onclick="window.location='bhw_patientlist.php'">← Back to Patient List</button>

<div class="profile-grid">
    <div class="card">
        <h3>👤 Patient Information</h3>
        <div class="info-row"><span class="label">Full Name</span><span class="value"><?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?></span></div>
        <div class="info-row"><span class="label">Gender</span><span class="value"><?= $p['gender'] ?></span></div>
        <div class="info-row"><span class="label">Birthdate</span><span class="value"><?= date('M d, Y', strtotime($p['birthdate'])) ?></span></div>
        <div class="info-row"><span class="label">Blood Type</span><span class="value"><?= $p['blood_type'] ?? '—' ?></span></div>
        <div class="info-row"><span class="label">Contact</span><span class="value"><?= $p['contact'] ?? '—' ?></span></div>
        <div class="info-row"><span class="label">Address</span><span class="value"><?= htmlspecialchars($p['address'] ?? '—') ?></span></div>
        <div class="info-row"><span class="label">Registered By</span><span class="value"><?= htmlspecialchars($p['bhw_name'] ?? '—') ?></span></div>
        <div class="info-row">
            <span class="label">Status</span>
            <span class="value">
                <span class="badge <?= $visitCount == 1 ? 'badge-new' : 'badge-returning' ?>">
                    <?= $visitCount == 1 ? '🟢 NEW PATIENT' : '🔵 FORMER PATIENT' ?>
                </span>
            </span>
        </div>
        <div class="info-row"><span class="label">Total Visits</span><span class="value"><?= $visitCount ?></span></div>
    </div>

    <div class="card qr-card">
        <h3>🔲 QR Code</h3>
        <?php if (!empty($p['qr_code'])): ?>
            <?php
session_start();
include __DIR__ . '/db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: bhw_patientlist.php"); exit(); }

// PATIENT INFORMATION
$stmt = $conn->prepare("
    SELECT p.*, u.fullname as bhw_name
    FROM patients p
    LEFT JOIN users u ON u.id = p.registered_by
    WHERE p.id = ?
");
$stmt->bind_param("i", $id); // Sa MySQLi, kailangan ng bind_param para sa '?'
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();

if (!$p) { die("Patient not found."); }

// VISIT HISTORY
$vStmt = $conn->prepare("SELECT * FROM visits WHERE patient_id = ? ORDER BY visit_date DESC");
$vStmt->bind_param("i", $id);
$vStmt->execute();
$vRes = $vStmt->get_result();
$visitHistory = $vRes->fetch_all(MYSQLI_ASSOC);
$visitCount   = count($visitHistory);
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { padding:30px; background:#f4f7fb; }

    .back-btn {
        display:inline-flex; align-items:center; gap:6px;
        color:#1a7a4a; text-decoration:none;
        font-size:13px; margin-bottom:20px; font-weight:600;
        cursor:pointer; background:none; border:none;
    }
    .back-btn:hover { text-decoration:underline; }

    .profile-grid {
        display:grid; grid-template-columns:1fr 1fr;
        gap:20px; margin-bottom:20px;
    }

    .card {
        background:white; padding:25px;
        border-radius:14px;
        box-shadow:0 4px 16px rgba(0,0,0,0.07);
    }
    .card h3 {
        color:#1a7a4a; font-size:15px;
        margin-bottom:15px; padding-bottom:10px;
        border-bottom:2px solid #f0f0f0;
    }

    .info-row {
        display:flex; justify-content:space-between;
        margin-bottom:10px; font-size:13px;
    }
    .info-row .label { color:#888; }
    .info-row .value { color:#333; font-weight:600; }

    .qr-card { text-align:center; }
    .qr-img {
        width:160px; height:160px;
        border:3px solid #28a745; border-radius:12px;
        padding:8px; cursor:pointer; transition:0.2s;
        margin:10px 0 15px;
    }
    .qr-img:hover { transform:scale(1.05); }

    .badge { padding:5px 14px; border-radius:20px; font-size:12px; font-weight:600; }
    .badge-new       { background:#d4edda; color:#155724; }
    .badge-returning { background:#cce5ff; color:#004085; }

    .actions-card { margin-bottom:20px; }
    .action-btns  { display:flex; gap:12px; flex-wrap:wrap; }
    .action-btn {
        padding:12px 22px; border:none; border-radius:10px;
        cursor:pointer; font-size:13px;
        font-family:'Poppins',sans-serif; font-weight:600;
        transition:0.2s; display:flex; align-items:center; gap:8px;
    }
    .action-btn:hover { transform:translateY(-2px); opacity:0.9; }
    .btn-visit  { background:linear-gradient(135deg,#1a7a4a,#28a745); color:white; }
    .btn-refer  { background:linear-gradient(135deg,#e67e00,#f0a500); color:white; }
    .btn-gen-qr { background:linear-gradient(135deg,#1e3c72,#2a5298); color:white; }

    .visit-table { width:100%; border-collapse:collapse; font-size:13px; }
    .visit-table th { background:#f4f7fb; color:#555; padding:10px 12px; text-align:left; }
    .visit-table td { padding:10px 12px; border-bottom:1px solid #eee; }

    .cat-badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .cat-immunization    { background:#fff3cd; color:#856404; }
    .cat-checkup         { background:#cce5ff; color:#004085; }
    .cat-family_planning { background:#f8d7da; color:#721c24; }

    /* QR MODAL */
    .modal-bg {
        display:none; position:fixed; inset:0;
        background:rgba(0,0,0,0.6);
        justify-content:center; align-items:center; z-index:9999;
    }
    .modal-bg.show { display:flex; }
    .modal {
        background:white; padding:35px; border-radius:16px;
        text-align:center; max-width:340px; width:90%;
        position:relative; animation:popIn 0.3s ease;
    }
    @keyframes popIn {
        from { transform:scale(0.8); opacity:0; }
        to   { transform:scale(1);   opacity:1; }
    }
    .close-btn { position:absolute; top:12px; right:15px; font-size:20px; cursor:pointer; color:#aaa; background:none; border:none; }
    .modal h3  { color:#1a7a4a; margin-bottom:4px; }
    .modal p   { color:#888; font-size:12px; margin-bottom:15px; }
    .qr-big    { width:200px; height:200px; border:3px solid #28a745; border-radius:12px; padding:8px; margin-bottom:15px; }
    .modal-btns { display:flex; gap:8px; justify-content:center; }
    .btn-sm { padding:8px 16px; border:none; border-radius:8px; cursor:pointer; font-size:12px; font-family:'Poppins',sans-serif; font-weight:600; }
    .btn-sm-green { background:linear-gradient(135deg,#1a7a4a,#28a745); color:white; }
    .btn-sm-blue  { background:linear-gradient(135deg,#1e3c72,#2a5298); color:white; text-decoration:none; display:inline-block; }
    .btn-sm-gray  { background:#eee; color:#555; }
</style>
</head>
<body>

<button class="back-btn" onclick="window.location='bhw_patientlist.php'">← Back to Patient List</button>

<div class="profile-grid">
    <div class="card">
        <h3>👤 Patient Information</h3>
        <div class="info-row"><span class="label">Full Name</span><span class="value"><?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?></span></div>
        <div class="info-row"><span class="label">Gender</span><span class="value"><?= $p['gender'] ?></span></div>
        <div class="info-row"><span class="label">Birthdate</span><span class="value"><?= date('M d, Y', strtotime($p['birthdate'])) ?></span></div>
        <div class="info-row"><span class="label">Blood Type</span><span class="value"><?= $p['blood_type'] ?? '—' ?></span></div>
        <div class="info-row"><span class="label">Contact</span><span class="value"><?= $p['contact'] ?? '—' ?></span></div>
        <div class="info-row"><span class="label">Address</span><span class="value"><?= htmlspecialchars($p['address'] ?? '—') ?></span></div>
        <div class="info-row"><span class="label">Registered By</span><span class="value"><?= htmlspecialchars($p['bhw_name'] ?? '—') ?></span></div>
        <div class="info-row">
            <span class="label">Status</span>
            <span class="value">
                <span class="badge <?= $visitCount == 1 ? 'badge-new' : 'badge-returning' ?>">
                    <?= $visitCount == 1 ? '🟢 NEW PATIENT' : '🔵 FORMER PATIENT' ?>
                </span>
            </span>
        </div>
        <div class="info-row"><span class="label">Total Visits</span><span class="value"><?= $visitCount ?></span></div>
    </div>

    <div class="card qr-card">
        <h3>🔲 QR Code</h3>
        <?php if (!empty($p['qr_code'])): ?>
            <img src="<?= htmlspecialchars($p['qr_code']) ?>" class="qr-img"
                 title="Click to enlarge" onclick="openQR('<?= htmlspecialchars($p['qr_code']) ?>')">
            <p style="font-size:12px; color:#888;">Click QR to enlarge</p>
        <?php else: ?>
            <div style="padding:20px; color:#aaa; font-size:13px;">
                <p style="font-size:40px;">🔲</p>
                <p style="margin-top:10px;">No QR yet</p>
            </div>
            <button class="action-btn btn-gen-qr" style="margin:10px auto;"
                onclick="window.location='/HMS-2/generate_qr.php?id=<?= $p['id'] ?>'">
                🔲 Generate QR Code
            </button>
        <?php endif; ?>
    </div>
</div>


    <div class="card actions-card">
    <h3>⚡ Actions</h3>
    <div class="action-btns">
        <button class="action-btn btn-visit"
            onclick="window.location='bhw_addvisit.php?patient_id=<?= $p['id'] ?>'">
            📋 Add Visit
        </button>
       
    </div>


<div class="card">
    <h3>📅 Visit History</h3>
    <?php if (empty($visitHistory)): ?>
        <p style="color:#aaa; font-size:13px; text-align:center; padding:20px;">No visits recorded yet.</p>
    <?php else: ?>
    <table class="visit-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Visit Date</th>
                <th>Category</th>
                <th>Visit No.</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($visitHistory as $i => $v): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= date('M d, Y h:i A', strtotime($v['visit_date'])) ?></td>
                <td>
                    <?php if ($v['category']): ?>
                    <span class="cat-badge cat-<?= $v['category'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $v['category'])) ?>
                    </span>
                    <?php else: ?>
                        <span style="color:#aaa;">—</span>
                    <?php endif; ?>
                </td>
                <td>Visit #<?= $v['visit_number'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- QR MODAL -->
<div class="modal-bg" id="qrModal">
    <div class="modal">
        <button class="close-btn" onclick="closeModal()">✕</button>
        <h3><?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?></h3>
        <p>Patient ID: #<?= $p['id'] ?></p>
        <img id="modalQR" src="<?= htmlspecialchars($p['qr_code'] ?? '') ?>" class="qr-big">
        <div class="modal-btns">
            <button class="btn-sm btn-sm-green" onclick="printQR()">🖨️ Print</button>
            <a class="btn-sm btn-sm-blue" href="<?= htmlspecialchars($p['qr_code'] ?? '') ?>" download>⬇️ Download</a>
            <button class="btn-sm btn-sm-gray" onclick="closeModal()">✕ Close</button>
        </div>
    </div>
</div>

<script>
function openQR(src) {
    document.getElementById('modalQR').src = src;
    document.getElementById('qrModal').classList.add('show');
}
function closeModal() {
    document.getElementById('qrModal').classList.remove('show');
}
function printQR() {
    const src  = document.getElementById('modalQR').src;
    const name = "<?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?>";
    const win  = window.open('', '_blank');
    win.document.write(`
        <html><head><title>Print QR</title></head>
        <body style="text-align:center;font-family:Poppins,sans-serif;padding:40px;">
            <h2 style="color:#1a7a4a;">${name}</h2>
            <img src="${src}" style="width:250px;height:250px;border:3px solid #28a745;border-radius:12px;padding:8px;">
        </body></html>
    `);
    win.document.close();
    win.print();
}
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>
            <p style="font-size:12px; color:#888;">Click QR to enlarge</p>
        <?php else: ?>
            <div style="padding:20px; color:#aaa; font-size:13px;">
                <p style="font-size:40px;">🔲</p>
                <p style="margin-top:10px;">No QR yet</p>
            </div>
            <button class="action-btn btn-gen-qr" style="margin:10px auto;"
                onclick="window.location='/HMS-2/generate_qr.php?id=<?= $p['id'] ?>'">
                🔲 Generate QR Code
            </button>
        <?php endif; ?>
    </div>
</div>


    <div class="card actions-card">
    <h3>⚡ Actions</h3>
    <div class="action-btns">
        <button class="action-btn btn-visit"
            onclick="window.location='bhw_addvisit.php?patient_id=<?= $p['id'] ?>'">
            📋 Add Visit
        </button>
       
    </div>


<div class="card">
    <h3>📅 Visit History</h3>
    <?php if (empty($visitHistory)): ?>
        <p style="color:#aaa; font-size:13px; text-align:center; padding:20px;">No visits recorded yet.</p>
    <?php else: ?>
    <table class="visit-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Visit Date</th>
                <th>Category</th>
                <th>Visit No.</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($visitHistory as $i => $v): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= date('M d, Y h:i A', strtotime($v['visit_date'])) ?></td>
                <td>
                    <?php if ($v['category']): ?>
                    <span class="cat-badge cat-<?= $v['category'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $v['category'])) ?>
                    </span>
                    <?php else: ?>
                        <span style="color:#aaa;">—</span>
                    <?php endif; ?>
                </td>
                <td>Visit #<?= $v['visit_number'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- QR MODAL -->
<div class="modal-bg" id="qrModal">
    <div class="modal">
        <button class="close-btn" onclick="closeModal()">✕</button>
        <h3><?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?></h3>
        <p>Patient ID: #<?= $p['id'] ?></p>
        <img id="modalQR" src="<?= htmlspecialchars($p['qr_code'] ?? '') ?>" class="qr-big">
        <div class="modal-btns">
            <button class="btn-sm btn-sm-green" onclick="printQR()">🖨️ Print</button>
            <a class="btn-sm btn-sm-blue" href="<?= htmlspecialchars($p['qr_code'] ?? '') ?>" download>⬇️ Download</a>
            <button class="btn-sm btn-sm-gray" onclick="closeModal()">✕ Close</button>
        </div>
    </div>
</div>

<script>
function openQR(src) {
    document.getElementById('modalQR').src = src;
    document.getElementById('qrModal').classList.add('show');
}
function closeModal() {
    document.getElementById('qrModal').classList.remove('show');
}
function printQR() {
    const src  = document.getElementById('modalQR').src;
    const name = "<?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?>";
    const win  = window.open('', '_blank');
    win.document.write(`
        <html><head><title>Print QR</title></head>
        <body style="text-align:center;font-family:Poppins,sans-serif;padding:40px;">
            <h2 style="color:#1a7a4a;">${name}</h2>
            <img src="${src}" style="width:250px;height:250px;border:3px solid #28a745;border-radius:12px;padding:8px;">
        </body></html>
    `);
    win.document.close();
    win.print();
}
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>
