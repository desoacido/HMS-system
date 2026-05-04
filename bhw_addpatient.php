<?php
session_start();
include __DIR__ . '/db.php';

$saved = false;
$patient_name = '';

if (isset($_POST['submit'])) {
    $firstname     = $_POST['firstname'];
    $lastname      = $_POST['lastname'];
    $birthdate     = $_POST['birthdate'];
    $gender        = $_POST['gender'];
    $address       = $_POST['address'];
    $contact       = $_POST['contact'];
    $blood_type    = $_POST['blood_type'];
    $registered_by = $_SESSION['user_id'];

    // 1. Prepare the INSERT for patients
    $stmt = $conn->prepare("INSERT INTO patients 
        (firstname, lastname, birthdate, gender, address, contact, blood_type, registered_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // 2. Bind parameters (s = string, i = integer)
    $stmt->bind_param("sssssssi", $firstname, $lastname, $birthdate, $gender, $address, $contact, $blood_type, $registered_by);
    $stmt->execute();

    // 3. FIX: Use $conn->insert_id for MySQLi
    $patient_id = $conn->insert_id;

    // 4. LOG AS 1ST VISIT
    $stmt2 = $conn->prepare("INSERT INTO visits (patient_id, visit_number, attended_by) VALUES (?, 1, ?)");
    $stmt2->bind_param("ii", $patient_id, $registered_by);
    $stmt2->execute();

    $saved = true;
    $patient_name = $firstname . ' ' . $lastname;
}
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { padding:30px; background:#f4f7fb; }
    h2 { color:#1a7a4a; margin-bottom:20px; }

    .form-card {
        background:white;
        padding:30px;
        border-radius:14px;
        box-shadow:0 4px 16px rgba(0,0,0,0.07);
        max-width:600px;
    }
    .row { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
    .form-group { margin-bottom:15px; }
    .form-group label { font-size:13px; color:#555; display:block; margin-bottom:5px; }
    .form-group input, .form-group select {
        width:100%; padding:10px; border:1px solid #ddd;
        border-radius:8px; font-family:'Poppins',sans-serif; font-size:13px;
    }
    .form-group input:focus, .form-group select:focus {
        border-color:#28a745; outline:none;
    }
    .btn {
        padding:12px 28px;
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white; border:none; border-radius:8px;
        cursor:pointer; font-size:14px;
        font-family:'Poppins',sans-serif; font-weight:600;
    }
    .btn:hover { opacity:0.9; }

    /* SUCCESS MODAL */
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
        padding:40px 35px;
        border-radius:16px;
        text-align:center;
        max-width:380px;
        width:90%;
        box-shadow:0 10px 40px rgba(0,0,0,0.2);
        animation: popIn 0.3s ease;
    }
    @keyframes popIn {
        from { transform:scale(0.8); opacity:0; }
        to   { transform:scale(1);   opacity:1; }
    }
    .check-icon {
        font-size:60px;
        margin-bottom:15px;
    }
    .modal h3 {
        color:#1a7a4a;
        font-size:20px;
        margin-bottom:8px;
    }
    .modal p {
        color:#888;
        font-size:13px;
        margin-bottom:25px;
    }
    .modal .patient-name {
        color:#333;
        font-weight:600;
        font-size:15px;
    }
    .modal-btns { display:flex; gap:10px; justify-content:center; }
    .btn-view {
        padding:10px 22px;
        background:linear-gradient(135deg,#1a7a4a,#28a745);
        color:white; border:none; border-radius:8px;
        cursor:pointer; font-size:13px;
        font-family:'Poppins',sans-serif; font-weight:600;
    }
    .btn-add-another {
        padding:10px 22px;
        background:#f0f0f0;
        color:#555; border:none; border-radius:8px;
        cursor:pointer; font-size:13px;
        font-family:'Poppins',sans-serif; font-weight:600;
    }
</style>
</head>
<body>

<h2>➕ Add New Patient</h2>

<div class="form-card">
    <form method="POST" id="patientForm">
        <div class="row">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="firstname" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="lastname" required>
            </div>
        </div>
        <div class="row">
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
        <div class="row">
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact">
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
            <input type="text" name="address">
        </div>
        <button class="btn" name="submit">Save Patient</button>
    </form>
</div>

<!-- SUCCESS MODAL -->
<div class="modal-bg <?= $saved ? 'show' : '' ?>" id="successModal">
    <div class="modal">
        <div class="check-icon">✅</div>
        <h3>Patient Saved!</h3>
        <p>
            <span class="patient-name"><?= htmlspecialchars($patient_name) ?></span><br>
            has been successfully registered.
        </p>
        <div class="modal-btns">
            
            <button class="btn-view" onclick="window.location='bhw_patientlist.php'">
                📋 View Patient List
            </button>
            <button class="btn-add-another" onclick="addAnother()">
                ➕ Add Another
            </button>
        </div>
    </div>
</div>

<script>
function addAnother() {
    document.getElementById('successModal').classList.remove('show');
    document.getElementById('patientForm').reset();
}
</script>

</body>
</html>
