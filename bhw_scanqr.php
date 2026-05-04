<?php
session_start();
include __DIR__ . '/db.php';

if (isset($_GET['patient_id'])) {
    $id = intval($_GET['patient_id']);
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    if (!$patient) {
        echo json_encode(['status' => 'not_found']);
    } else {
        echo json_encode([
            'status' => 'found',
            'name'   => $patient['firstname'] . ' ' . $patient['lastname'],
            'id'     => $patient['id']
        ]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { background:#f4f7fb; padding:30px; }

    .card {
        background:white; padding:25px; border-radius:16px;
        max-width:480px; margin:auto;
        box-shadow:0 4px 16px rgba(0,0,0,0.08);
        text-align:center;
    }
    .card h2 { color:#1a7a4a; margin-bottom:5px; }
    .card p  { color:#888; font-size:13px; margin-bottom:20px; }

    #reader { width:100%; border-radius:12px; overflow:hidden; }

    /* Start button */
    #startBtn {
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white; border:none; padding:14px 30px;
        border-radius:10px; font-size:15px; font-weight:600;
        cursor:pointer; font-family:'Poppins',sans-serif;
        margin-bottom:15px; transition:0.2s;
    }
    #startBtn:hover { opacity:0.9; transform:translateY(-2px); }

    /* Status messages */
    .alert {
        display:none; padding:14px; margin-top:15px;
        border-radius:10px; font-size:14px; font-weight:600;
    }
    .alert-success { background:#d4edda; color:#155724; }
    .alert-error   { background:#f8d7da; color:#721c24; }
    .alert-invalid { background:#fff3cd; color:#856404; }

    /* Scanning indicator */
    #scanStatus {
        display:none; color:#1a7a4a;
        font-size:13px; margin-top:10px; font-weight:600;
    }
</style>
</head>
<body>

<div class="card">
    <h2>📷 Scan QR Code</h2>
    <p>I-scan ang QR code ng pasyente</p>

    <button id="startBtn" onclick="startScanner()">📷 Start Camera</button>

    <div id="reader"></div>
    <div id="scanStatus">🔍 Scanning...</div>

    <div class="alert alert-success" id="alertSuccess"></div>
    <div class="alert alert-error"   id="alertError">❌ Patient not found in the system.</div>
    <div class="alert alert-invalid" id="alertInvalid">⚠️ Invalid QR Code. Hindi ito QR ng pasyente.</div>
</div>

<script>
let scanner = null;
let scanned = false;

function startScanner() {
    document.getElementById('startBtn').style.display = 'none';
    document.getElementById('scanStatus').style.display = 'block';
    hideAlerts();

    scanner = new Html5Qrcode("reader");
    scanner.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess,
        onScanError
    ).catch(err => {
        document.getElementById('scanStatus').style.display = 'none';
        document.getElementById('startBtn').style.display = 'block';
        showAlert('alertInvalid', '⚠️ Hindi ma-access ang camera. Pahintulutan ang camera permission.');
        console.error(err);
    });
}

function onScanSuccess(decodedText) {
    if (scanned) return;
    scanned = true;

    scanner.stop();
    document.getElementById('scanStatus').style.display = 'none';
    hideAlerts();

    // ✅ Check if valid patient QR (must contain patientprofile.php?id=)
    const validDomain = "hms-system-1-l6jn.onrender.com";
    let patientId = null;

    if (decodedText.includes("patientprofile.php") && decodedText.includes("id=")) {
        try {
            const url = new URL(decodedText);
            if (url.hostname === validDomain) {
                patientId = url.searchParams.get("id");
            }
        } catch(e) {}
    }

    if (!patientId) {
        // Not a valid patient QR
        showAlert('alertInvalid', '⚠️ Invalid QR Code. Not in the system.');
        setTimeout(() => resetScanner(), 3000);
        return;
    }

    // Valid format — check if patient exists in DB
    fetch("bhw_scanqr.php?patient_id=" + patientId)
    .then(res => res.json())
    .then(data => {
        if (data.status === "found") {
            showAlert('alertSuccess', '✅ Patient Found: ' + data.name + '<br><small>Redirecting...</small>');
            setTimeout(() => {
                window.parent.document.querySelector('iframe[name="content"]').src = "patientprofile.php?id=" + patientId;
            }, 500);
        } else {
            showAlert('alertError', '❌ Patient not found in the system.');
            setTimeout(() => resetScanner(), 500);
        }
    })
    .catch(() => {
        showAlert('alertInvalid', '⚠️ Network error. Try again.');
        setTimeout(() => resetScanner(), 500);
    });
}

function onScanError(err) {
    // Silent — normal lang habang nagha-hunt ng QR
}

function showAlert(id, msg) {
    hideAlerts();
    const el = document.getElementById(id);
    el.innerHTML = msg;
    el.style.display = 'block';
}

function hideAlerts() {
    ['alertSuccess','alertError','alertInvalid'].forEach(id => {
        document.getElementById(id).style.display = 'none';
    });
}

function resetScanner() {
    scanned = false;
    hideAlerts();
    document.getElementById('startBtn').style.display = 'block';
    document.getElementById('reader').innerHTML = '';
    scanner = null;
}
</script>

</body>
</html>
