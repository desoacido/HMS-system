<?php
session_start();
include __DIR__ . '/db.php';

// FETCH ALL PATIENTS
// Gamit ang MySQLi connection ($conn)
$query = "
    SELECT p.*, u.fullname AS registered_by_name
    FROM patients p
    LEFT JOIN users u ON p.registered_by = u.id
    ORDER BY p.id DESC
";

$result = $conn->query($query);

// Dito natin papalitan ang fetchAll() ng MySQLi version
$patients = $result->fetch_all(MYSQLI_ASSOC);
$total = count($patients);

// Para sa Success Modal logic
$saved = isset($_GET['saved']) ? true : false;
$patient_name = isset($_GET['name']) ? $_GET['name'] : '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Patient Records</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { padding:30px; background:#f4f7fb; }

    .page-header {
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
        flex-wrap:wrap;
        gap:10px;
    }
    h2 { color:#1a7a4a; }

    .top-controls {
        display:flex;
        gap:10px;
        align-items:center;
        flex-wrap:wrap;
    }

    .search-box {
        display:flex;
        align-items:center;
        background:white;
        border:1px solid #ddd;
        border-radius:8px;
        overflow:hidden;
        box-shadow:0 2px 6px rgba(0,0,0,0.05);
    }
    .search-box input {
        border:none;
        outline:none;
        padding:9px 14px;
        font-family:'Poppins',sans-serif;
        font-size:13px;
        width:220px;
    }
    .search-box button {
        padding:9px 14px;
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white;
        border:none;
        cursor:pointer;
        font-size:13px;
    }

    .btn-add {
        padding:10px 20px;
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white;
        border:none;
        border-radius:8px;
        cursor:pointer;
        font-size:13px;
        font-family:'Poppins',sans-serif;
        font-weight:600;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        gap:5px;
    }
    .btn-add:hover { opacity:0.9; }

    .stats-bar {
        background:white;
        border-radius:10px;
        padding:12px 20px;
        margin-bottom:18px;
        font-size:13px;
        color:#555;
        box-shadow:0 2px 8px rgba(0,0,0,0.05);
        display:flex;
        align-items:center;
        gap:8px;
    }
    .stats-bar strong { color:#1a7a4a; font-size:18px; }

    .table-card {
        background:white;
        border-radius:14px;
        box-shadow:0 4px 16px rgba(0,0,0,0.07);
        overflow:hidden;
    }

    table {
        width:100%;
        border-collapse:collapse;
        font-size:13px;
    }
    thead {
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white;
    }
    thead th {
        padding:13px 15px;
        text-align:left;
        font-weight:600;
        letter-spacing:0.3px;
    }
    tbody tr {
        border-bottom:1px solid #f0f0f0;
        transition:background 0.15s;
    }
    tbody tr:hover { background:#f6fff9; }
    tbody td { padding:11px 15px; color:#444; vertical-align:middle; }

    .badge {
        padding:3px 10px;
        border-radius:20px;
        font-size:11px;
        font-weight:600;
    }
    .badge-male   { background:#e3f0ff; color:#1565c0; }
    .badge-female { background:#fce4ec; color:#c2185b; }

    .blood {
        padding:3px 10px;
        border-radius:20px;
        background:#fff3e0;
        color:#e65100;
        font-size:11px;
        font-weight:600;
    }

    .action-btns { display:flex; gap:6px; }

    .btn-view-rec {
        padding:6px 12px;
        background:#e3f0ff;
        color:#1565c0;
        border:none;
        border-radius:6px;
        cursor:pointer;
        font-size:12px;
        font-family:'Poppins',sans-serif;
        font-weight:600;
        text-decoration:none;
    }
    .btn-edit-rec {
        padding:6px 12px;
        background:#fff8e1;
        color:#f57f17;
        border:none;
        border-radius:6px;
        cursor:pointer;
        font-size:12px;
        font-family:'Poppins',sans-serif;
        font-weight:600;
        text-decoration:none;
    }
    .btn-view-rec:hover { background:#bbdefb; }
    .btn-edit-rec:hover { background:#fff176; }

    /* ADD PATIENT MODAL */
    .add-modal {
        background:white;
        padding:35px;
        border-radius:16px;
        max-width:580px;
        width:95%;
        box-shadow:0 10px 40px rgba(0,0,0,0.2);
        animation: popIn 0.25s ease;
        position:relative;
        max-height:90vh;
        overflow-y:auto;
    }
    .add-modal h3 { color:#1a7a4a; margin-bottom:18px; font-size:17px; border-bottom:2px solid #e8f5e9; padding-bottom:10px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .form-group { margin-bottom:14px; }
    .form-group label { font-size:12px; color:#555; display:block; margin-bottom:5px; font-weight:600; }
    .form-group input, .form-group select {
        width:100%; padding:10px 12px;
        border:1px solid #ddd; border-radius:8px;
        font-family:'Poppins',sans-serif; font-size:13px;
    }
    .form-group input:focus, .form-group select:focus {
        border-color:#28a745; outline:none;
    }
    .btn-save {
        padding:11px 28px;
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white; border:none; border-radius:8px;
        cursor:pointer; font-size:13px;
        font-family:'Poppins',sans-serif; font-weight:600;
        width:100%; margin-top:5px;
    }
    .btn-save:hover { opacity:0.9; }

    /* SUCCESS MODAL */
    .success-modal {
        background:white;
        padding:40px 35px;
        border-radius:16px;
        text-align:center;
        max-width:360px;
        width:90%;
        box-shadow:0 10px 40px rgba(0,0,0,0.2);
        animation: popIn 0.3s ease;
    }
    .success-modal .check-icon { font-size:55px; margin-bottom:12px; }
    .success-modal h3 { color:#1a7a4a; font-size:19px; margin-bottom:8px; }
    .success-modal p { color:#888; font-size:13px; margin-bottom:20px; }
    .success-modal .pname { color:#333; font-weight:600; font-size:14px; }
    .btn-ok {
        padding:10px 30px;
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white; border:none; border-radius:8px;
        cursor:pointer; font-size:13px;
        font-family:'Poppins',sans-serif; font-weight:600;
    }

    .no-records {
        text-align:center;
        padding:50px;
        color:#aaa;
        font-size:14px;
    }
    .no-records span { font-size:40px; display:block; margin-bottom:10px; }

    /* VIEW MODAL */
    .modal-bg {
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.45);
        justify-content:center;
        align-items:center;
        z-index:999;
    }
    .modal-bg.show { display:flex; }
    .modal {
        background:white;
        padding:35px;
        border-radius:16px;
        max-width:480px;
        width:90%;
        box-shadow:0 10px 40px rgba(0,0,0,0.2);
        animation: popIn 0.25s ease;
        position:relative;
    }
    @keyframes popIn {
        from { transform:scale(0.85); opacity:0; }
        to   { transform:scale(1);   opacity:1; }
    }
    .modal h3 { color:#1a7a4a; margin-bottom:18px; font-size:17px; border-bottom:2px solid #e8f5e9; padding-bottom:10px; }
    .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .detail-item label { font-size:11px; color:#aaa; font-weight:600; text-transform:uppercase; display:block; margin-bottom:3px; }
    .detail-item span { font-size:13px; color:#333; font-weight:500; }
    .modal-close {
        position:absolute;
        top:15px; right:18px;
        background:none; border:none;
        font-size:20px; cursor:pointer; color:#aaa;
    }
    .modal-close:hover { color:#333; }


</style>
</head>
<body>

<!-- PAGE HEADER -->
<div class="page-header">
    <h2>📋 Patient Records</h2>
    <div class="top-controls">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search name, contact, blood type..." oninput="filterTable()">
            <span style="padding:9px 14px; background:linear-gradient(135deg,#1a7a4a,#28a745); color:white; font-size:13px;">🔍</span>
        </div>
    </div>
</div>

<!-- STATS -->
<div class="stats-bar">
    <strong id="countDisplay"><?= $total ?></strong>
    <span id="countLabel">total patient(s) registered</span>
</div>

<!-- TABLE -->
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Birthdate</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Contact</th>
                <th>Blood Type</th>
                <th>Registered By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($total === 0): ?>
            <tr>
                <td colspan="9">
                    <div class="no-records">
                        <span>🔍</span>
                        No patient records found.
                    </div>
                </td>
            </tr>
        <?php else: ?>
            <?php $i = 1; foreach ($patients as $p): ?>
            <?php
                // Compute Age
                $dob = new DateTime($p['birthdate']);
                $now = new DateTime();
                $age = $dob->diff($now)->y;
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><strong><?= htmlspecialchars($p['firstname'] . ' ' . $p['lastname']) ?></strong></td>
                <td><?= date('M d, Y', strtotime($p['birthdate'])) ?></td>
                <td><?= $age ?> yrs</td>
                <td>
                    <span class="badge <?= $p['gender'] === 'Male' ? 'badge-male' : 'badge-female' ?>">
                        <?= $p['gender'] ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($p['contact'] ?: '—') ?></td>
                <td><span class="blood"><?= htmlspecialchars($p['blood_type']) ?></span></td>
                <td><?= htmlspecialchars($p['registered_by_name'] ?? '—') ?></td>
                <td>
                    <div class="action-btns">
                        <button class="btn-view-rec" onclick='openView(<?= json_encode($p) ?>, <?= $age ?>)'>👁 View</button>
                        

                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ADD PATIENT MODAL -->
<div class="modal-bg" id="addModal">
    <div class="add-modal">
        <button class="modal-close" onclick="closeModal('addModal')">✖</button>
        <h3>➕ Add New Patient</h3>
        <form method="POST" id="addForm">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstname" required placeholder="e.g. Juan">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastname" required placeholder="e.g. Dela Cruz">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Birthdate</label>
                    <input type="date" name="birthdate" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact" placeholder="e.g. 09xxxxxxxxx">
                </div>
                <div class="form-group">
                    <label>Blood Type</label>
                    <select name="blood_type">
                        <option>A+</option><option>A-</option>
                        <option>B+</option><option>B-</option>
                        <option>AB+</option><option>AB-</option>
                        <option>O+</option><option>O-</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" placeholder="e.g. Brgy. San Pedro, Davao City">
            </div>
            <button class="btn-save" name="submit" type="submit">💾 Save Patient</button>
        </form>
    </div>
</div>

<!-- SUCCESS MODAL -->
<div class="modal-bg <?= $saved ? 'show' : '' ?>" id="successModal">
    <div class="success-modal">
        <div class="check-icon">✅</div>
        <h3>Patient Saved!</h3>
        <p>
            <span class="pname"><?= htmlspecialchars($patient_name) ?></span><br>
            has been successfully registered.
        </p>
        <button class="btn-ok" onclick="closeModal('successModal')">Done</button>
    </div>
</div>

<!-- VIEW MODAL -->

<div class="modal-bg" id="viewModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('viewModal')">✖</button>
        <h3>👤 Patient Details</h3>
        <div class="detail-grid" id="viewContent"></div>
    </div>
</div>



<script>
function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    let count = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(input)) {
            row.style.display = '';
            count++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('countDisplay').textContent = count;
    document.getElementById('countLabel').textContent = input
        ? `result(s) found for "${input}"`
        : 'total patient(s) registered';
}

function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function openView(p, age) {
    const fields = [
        { label: 'Patient ID',    value: p.id },
        { label: 'First Name',    value: p.firstname },
        { label: 'Last Name',     value: p.lastname },
        { label: 'Birthdate',     value: p.birthdate },
        { label: 'Age',           value: age + ' years old' },
        { label: 'Gender',        value: p.gender },
        { label: 'Blood Type',    value: p.blood_type },
        { label: 'Contact',       value: p.contact || '—' },
        { label: 'Address',       value: p.address || '—' },
        { label: 'Registered By', value: p.registered_by_name || '—' },
    ];
    let html = '';
    fields.forEach(f => {
        html += `<div class="detail-item"><label>${f.label}</label><span>${f.value}</span></div>`;
    });
    document.getElementById('viewContent').innerHTML = html;
    document.getElementById('viewModal').classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

// Close modal on bg click
document.querySelectorAll('.modal-bg').forEach(bg => {
    bg.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });
});
</script>

</body>
</html>
