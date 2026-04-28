<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Scanner</title>

<script src="https://unpkg.com/html5-qrcode"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }

body {
    background:#f4f7fb;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.container {
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    text-align:center;
    width:350px;
}

h2 {
    margin-bottom:15px;
    color:#333;
}

#reader {
    margin:10px auto;
    border-radius:10px;
    overflow:hidden;
}

.status {
    margin-top:10px;
    font-size:13px;
    color:#777;
}

.back {
    display:inline-block;
    margin-top:15px;
    text-decoration:none;
    color:#555;
}

.success {
    color:green;
    font-weight:600;
}

</style>
</head>

<body>

<div class="container">
    <h2>Scan Patient QR</h2>

    <div id="reader" style="width:300px;"></div>

    <div class="status" id="statusText">Align QR code inside the box</div>

    <a href="/presentation/bhw/dashboard.php" class="back">⬅ Back to Dashboard</a>
</div>

<script>
function onScanSuccess(decodedText, decodedResult) {

    // ERROR HANDLER LANG - para sa hindi system QR
    const allowedBase = "https://hms-system-cv2z.onrender.com";

    if (!decodedText.startsWith(allowedBase)) {
        document.getElementById("statusText").innerHTML = 
            "❌ Invalid QR Code! Not registered in this system.";
        document.getElementById("statusText").style.color = "red";
        return;
    }

    // ORIGINAL MO - hindi binago
    document.getElementById("statusText").innerHTML = "✅ QR detected! Redirecting...";
    document.getElementById("statusText").classList.add("success");
    setTimeout(() => {
        window.location.href = decodedText;
    }, 1000);
}

var html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: 250 }
);
html5QrcodeScanner.render(onScanSuccess);
</script>
</body>
</html>
