<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

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

/* BACK BUTTON (TOP) */
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

.back:hover {
    transform:translateY(-2px);
}

/* HEADER */
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

/* TABLE */
.table-container {
    background:white;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    overflow:hidden;
}

table {
    width:100%;
    border-collapse:collapse;
}

th, td {
    padding:12px;
    text-align:left;
}

th {
    background:#43e97b;
    color:white;
}

tr:nth-child(even) { background:#f9f9f9; }

img {
    border-radius:8px;
}

/* ACTIONS */
.actions a {
    display:inline-block;
    margin:2px 0;
    padding:5px 8px;
    border-radius:6px;
    font-size:12px;
    text-decoration:none;
}

.qr { background:#e3f2fd; color:#0d47a1; }
.visit { background:#e8f5e9; color:#1b5e20; }
.history { background:#fff3e0; color:#e65100; }
.record { background:#f3e5f5; color:#6a1b9a; }

</style>
</head>

<body>

<!-- ✅ BACK BUTTON MOVED HERE -->
<a href="/presentation/bhw/dashboard.php" class="back">
    ⬅ Back to Dashboard
</a>

<div class="header">
    <h2>Patient List (BHW)</h2>

    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search patient (name, ID, contact...)">
    </div>
</div>

<div class="table-container">
<table id="patientTable">

<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Age</th>
    <th>Contact</th>
    <th>QR Code</th>
    <th>Actions</th>
</tr>

<?php foreach ($patients as $p): ?>
<tr>
    <td><?= $p['id'] ?></td>

    <td><?= $p['first_name'] . " " . $p['last_name'] ?></td>

    <td>
        <?php
            if (!empty($p['birthdate'])) {
                $birthDate = new DateTime($p['birthdate']);
                $today = new DateTime();
                echo $birthDate->diff($today)->y;
            } else {
                echo "N/A";
            }
        ?>
    </td>

    <td><?= $p['contact_number'] ?></td>

    <td>
        <?php if (!empty($p['qr_code'])): ?>
            <img src="<?= $p['qr_code'] ?>" width="70">
        <?php else: ?>
            <span style="color:#999;">No QR</span>
        <?php endif; ?>
    </td>

    <td class="actions">
        <a class="qr" href="/presentation/qrcode/generate_patient_qr.php?id=<?= $p['id'] ?>">QR</a>
        <a class="visit" href="select_category.php?patient_id=<?= $p['id'] ?>">Visit</a>
        <a class="history" href="view_patient_history.php?patient_id=<?= $p['id'] ?>">History</a>
        <a class="record" href="select_category.php?patient_id=<?= $p['id'] ?>">New</a>
    </td>
</tr>
<?php endforeach; ?>
