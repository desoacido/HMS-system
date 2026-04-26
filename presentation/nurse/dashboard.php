<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

// COUNTS
$total = $conn->query("SELECT COUNT(*) FROM referrals")->fetchColumn();

$pending_stmt = $conn->query("
    SELECT COUNT(*) 
    FROM referrals 
    WHERE status IN ('Pending', 'In Progress')
");
$pending = $pending_stmt->fetchColumn();

$done_stmt = $conn->query("
    SELECT COUNT(*) 
    FROM referrals 
    WHERE status IN ('Done', 'reviewed', 'Completed')
");
$done = $done_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nurse Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body {
    background:#f4f7fb;
    padding:25px;
}

/* HEADER */
.header {
    margin-bottom:20px;
}

h2 {
    color:#2c3e50;
}

/* DASHBOARD CARDS */
.cards {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
    gap:15px;
    margin-top:15px;
}

.card {
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 10px 20px rgba(0,0,0,0.08);
    transition:0.2s;
}

.card:hover {
    transform:translateY(-3px);
}

.card h3 {
    font-size:28px;
    margin-bottom:5px;
}

.card p {
    color:#777;
    font-size:14px;
}

/* COLORS */
.total { border-left:5px solid #3498db; }
.pending { border-left:5px solid #f39c12; }
.done { border-left:5px solid #2ecc71; }

/* NOTIFICATION PANEL */
.notification {
    margin-top:30px;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 10px 20px rgba(0,0,0,0.08);
    max-width:450px;
}

.notification h3 {
    margin-bottom:10px;
    color:#333;
}

.badge {
    display:inline-block;
    background:#f39c12;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    margin-bottom:10px;
}

/* BUTTON */
.btn {
    display:inline-block;
    margin-top:10px;
    padding:10px 14px;
    background:#007bff;
    color:white;
    text-decoration:none;
    border-radius:8px;
    font-weight:600;
}

.btn:hover {
    opacity:0.9;
}

/* LOGOUT */
.logout {
    display:inline-block;
    margin-top:25px;
    color:#e74c3c;
    text-decoration:none;
    font-weight:600;
}

@media(max-width:600px){
    body { padding:15px; }
}
</style>
</head>

<body>

<div class="header">
    <h2>👩‍⚕️ Nurse Dashboard</h2>
</div>

<!-- DASH CARDS -->
<div class="cards">

    <div class="card total">
        <h3><?= $total ?></h3>
        <p>Total Referrals</p>
    </div>

    <div class="card pending">
        <h3><?= $pending ?></h3>
        <p>Pending Referrals</p>
    </div>

    <div class="card done">
        <h3><?= $done ?></h3>
        <p>Completed</p>
    </div>

</div>

<!-- NOTIFICATIONS -->
<div class="notification">

    <span class="badge">LIVE</span>

    <h3>🔔 <?= $pending ?> Pending Referrals</h3>
    <p>You have referrals that need nurse attention.</p>

    <a href="referrals.php" class="btn">
        View Referrals
    </a>

</div>

<a href="../logout.php" class="logout">
    🚪 Logout
</a>

</body>
</html>