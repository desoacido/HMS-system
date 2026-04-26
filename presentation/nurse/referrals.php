<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

// FETCH PENDING REFERRALS
$stmt = $conn->prepare("
    SELECT r.*, p.first_name, p.last_name
    FROM referrals r
    JOIN patients p ON r.patient_id = p.id
    WHERE r.status = 'Pending'
    ORDER BY r.created_at DESC
");
$stmt->execute();
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nurse Referrals</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:#f4f7fb;
    padding:25px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

h2{
    color:#2c3e50;
}

/* TABLE CONTAINER */
.table-container{
    background:white;
    border-radius:12px;
    box-shadow:0 10px 20px rgba(0,0,0,0.08);
    overflow:hidden;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#3498db;
    color:white;
    padding:12px;
    text-align:left;
    font-size:14px;
}

td{
    padding:12px;
    border-bottom:1px solid #eee;
    font-size:14px;
}

tr:hover{
    background:#f9f9f9;
}

/* BADGES */
.badge{
    display:inline-block;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    background:#f39c12;
    color:white;
}

/* VIEW BUTTON */
.btn{
    display:inline-block;
    padding:6px 10px;
    background:#2ecc71;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-size:13px;
}

.btn:hover{
    opacity:0.9;
}

/* EMPTY STATE */
.empty{
    text-align:center;
    padding:20px;
    color:#777;
}

/* BACK BUTTON */
.back{
    display:inline-block;
    margin-top:15px;
    text-decoration:none;
    color:#555;
    font-weight:600;
}
</style>
</head>

<body>

<div class="header">
    <h2>🩺 Nurse Referrals (Pending)</h2>
</div>

<div class="table-container">

<table>
    <tr>
        <th>Patient</th>
        <th>Reason</th>
        <th>Consultation ID</th>
        <th>Assigned Nurse</th>
        <th>Date</th>
        <th>Action</th>
    </tr>

    <?php if (!empty($referrals)): ?>
        <?php foreach ($referrals as $r): ?>
        <tr>

            <td>
                <?= htmlspecialchars($r['first_name'] . " " . $r['last_name']) ?>
            </td>

            <td><?= htmlspecialchars($r['reason']) ?></td>

            <td><?= $r['consultation_id'] ?? 'N/A' ?></td>

            <td>
                <span class="badge">
                    <?= $r['assigned_nurse'] ?? 'Not assigned' ?>
                </span>
            </td>

            <td><?= $r['created_at'] ?></td>

            <td>
                <a class="btn" href="/hms2/presentation/nurse/view_referral.php?id=<?= $r['id'] ?>">
                    👁 View
                </a>
            </td>

        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" class="empty">No pending referrals found.</td>
        </tr>
    <?php endif; ?>

</table>

</div>

<a href="/hms2/presentation/nurse/dashboard.php" class="back">
    ⬅ Back to Dashboard
</a>

</body>
</html>