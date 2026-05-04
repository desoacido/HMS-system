<?php
session_start();
include __DIR__ . '/db.php';

// CHECK IF QR EXISTS IN DATABASE
if (isset($_GET['patient_id'])) {
    $id = $_GET['patient_id'];
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode(['status' => 'not_found']);
    } else {
        echo json_encode(['status' => 'found', 'name' => $patient['firstname'] . ' ' . $patient['lastname']]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { padding:30px; background:#f4f7fb; }
    h2 { color:#1a7a4a; margin-bottom:20px; }

    .scan-card { background:white; padding:30px; border-radius:14px; box-shadow:0 4px 16px rgba(0,0,0,0.07); max-width:500px; }

    #reader { width:100%; border-radius:10px; overflow:hidden; }

    /* STATUS MESSAGES */
    .alert {
        margin-top:15px;
        padding:15px;
        border-radius:8px;
        font-size:14px;
        display:none;
        text-align:center;
    }
    .alert-success  { background:#d4edda; color:#155724; }
    .alert-error    { background:#f8d7da; color:#721c24; }
    .alert-warning  { background:#fff3cd; color:#856404; }
    .alert-info     { background:#cce5ff; color:#004085; }

    .btn {
        margin-top:15px;
        padding:10px 24px;
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white;
        border:none;
        border-radius:8px;
        cursor:pointer;
        font-size:14px;
        font-family:'Poppins',sans-serif;
        font-weight:600;
        width:100%;
        display:none;
    }

    .btn:hover { opacity:0.9; }

    .status-bar {
        margin-top:15px;
        padding:10px;
        background:#f4f7fb;
        border-radius:8px;
        font-size:13px;
        color:#888;
        text-align:center;
    }

    .spinner {
        display:inline-block;
        width:14px;
        height:14px;
        border:2px solid #ccc;
        border-top:2px solid #28a745;
        border-radius:50%;
        animation:spin 0.8s linear infinite;
        vertical-align:middle;
        margin-right:6px;
    }

    @keyframes spin { to { transform:rotate(360deg); } }
</style>
</head>
<body>

<h2>📷 Scan Patient QR Code</h2>

<div class="scan-card">

    <div id="reader"></div>

    <div class="status-bar" id="statusBar">
        <span class="spinner"></span> Point camera at a QR code...
    </div>

    <!-- SUCCESS -->
    <div class="alert alert-success" id="alertSuccess">
        ✅ Patient found! Redirecting to profile...
    </div>

    <!-- NOT IN SYSTEM -->
    <div class="alert alert-error" id="alertNotFound">
        ❌ QR Code not recognized! This patient is not in the system.
    </div>

    <!-- CAMERA ERROR -->
    <div class="alert alert-warning" id="alertCamera">
        ⚠️ Camera error! Please allow camera access or check your device.
    </div>

    <!-- NO QR DETECTED TIMEOUT -->
    <div class="alert alert-info" id="alertTimeout">
        🔍 No QR Code detected. Make sure the QR is clear and well-lit.
    </div>

    <!-- RETRY BUTTON -->
    <button class="btn" id="btnRetry" onclick="retryScanner()">🔄 Try Again</button>

</div>

<script>
let html5QrCode = new Html5Qrcode("reader");
let scanning = true;
let timeoutTimer;

function showAlert(id) {
    document.querySelectorAll('.alert').forEach(a => a.style.display = 'none');
    document.getElementById(id).style.display = 'block';
    document.getElementById('btnRetry').style.display = 'block';
    document.getElementById('statusBar').style.display = 'none';
}

function startScanner() {
    scanning = true;
    document.getElementById('statusBar').style.display = 'block';
    document.getElementById('btnRetry').style.display = 'none';
    document.querySelectorAll('.alert').forEach(a => a.style.display = 'none');

    // NO QR DETECTED AFTER 15 SECONDS
    timeoutTimer = setTimeout(() => {
        if (scanning) {
            html5QrCode.stop().catch(() => {});
            scanning = false;
            showAlert('alertTimeout');
        }
    }, 15000);

    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            if (!scanning) return;
            scanning = false;
            clearTimeout(timeoutTimer);
            html5QrCode.stop().catch(() => {});

            // CHECK IF PATIENT EXISTS IN DATABASE
            fetch('scan_qr.php?patient_id=' + encodeURIComponent(decodedText))
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'found') {
                        document.getElementById('alertSuccess').innerHTML =
                            '✅ Patient found: <strong>' + data.name + '</strong><br>Redirecting...';
                        showAlert('alertSuccess');
                        setTimeout(() => {
                            window.top.location = 'patient_profile.php?id=' + decodedText;
                        }, 1500);
                    } else {
                        showAlert('alertNotFound');
                    }
                })
                .catch(() => {
                    showAlert('alertNotFound');
                });
        },
        (error) => {
            // Scanning in progress — ignore per-frame errors
        }
    ).catch((err) => {
        clearTimeout(timeoutTimer);
        scanning = false;
        showAlert('alertCamera');
        console.error("Camera error:", err);
    });
}

function retryScanner() {
    html5QrCode = new Html5Qrcode("reader");
    startScanner();
}

// START ON LOAD
startScanner();
</script>

</body>
</html>