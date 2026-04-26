<?php
include '../../application/includes/session_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BHW Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    display: flex;
    min-height: 100vh;
    background: #f4f7fb;
}

/* SIDEBAR */
.sidebar {
    width: 240px;
    background: linear-gradient(180deg, #43e97b, #38f9d7);
    color: white;
    padding: 20px;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: 0.3s;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.2);
}

.logout {
    margin-top: 20px;
    background: rgba(255,0,0,0.2);
}

/* MAIN */
.main {
    flex: 1;
    padding: 30px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.header h1 {
    color: #333;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    transition: 0.3s;
    text-decoration: none;
    color: #333;
}

.card:hover {
    transform: translateY(-5px);
}

.card h3 {
    margin-bottom: 10px;
}

.card p {
    font-size: 14px;
    color: #777;
}

</style>
</head>

<body>

<div class="sidebar">
    <h2>BHW Panel</h2>

    <a href="add_patient.php">➕ Add Patient</a>
    <a href="patient_list.php">📋 Patient List</a>
    <a href="/hms2/presentation/bhw/scan_qr.php">📷 Scan QR</a>

    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">

    <div class="header">
        <h1>BHW Dashboard</h1>
    </div>

    <div class="cards">

        <a href="add_patient.php" class="card">
            <h3>Add New Patient</h3>
            <p>Register new patient into the system.</p>
        </a>

        <a href="patient_list.php" class="card">
            <h3>Patient List</h3>
            <p>View and manage patient records.</p>
        </a>

        <a href="/hms2/presentation/bhw/scan_qr.php" class="card">
            <h3>Scan QR</h3>
            <p>Scan patient QR code for quick access.</p>
        </a>

    </div>

</div>

</body>
</html>