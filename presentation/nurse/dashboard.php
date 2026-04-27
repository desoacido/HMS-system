<?php
include '../../application/config/db.php';
include '../../application/includes/session_check.php';

// COUNT STATS
$total     = $conn->query("SELECT COUNT(*) FROM referrals")->fetchColumn();
$pending   = $conn->query("SELECT COUNT(*) FROM referrals WHERE status = 'Pending'")->fetchColumn();
$inprog    = $conn->query("SELECT COUNT(*) FROM referrals WHERE status = 'In Progress'")->fetchColumn();
$completed = $conn->query("SELECT COUNT(*) FROM referrals WHERE status IN ('Completed','Done','reviewed')")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Nurse Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: Poppins, sans-serif;
    display: flex;
    min-height: 100vh;
    background: #f4f7fb;
}

/* ── SIDEBAR ── */
.sidebar {
    width: 230px;
    background: linear-gradient(180deg, #4facfe 0%, #00c9a7 100%);
    display: flex;
    flex-direction: column;
    padding: 30px 20px;
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
}

.sidebar .brand {
    color: white;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 35px;
    letter-spacing: 0.5px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    text-decoration: none;
    padding: 11px 15px;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 6px;
    transition: background 0.2s;
}

.sidebar a:hover,
.sidebar a.active {
    background: rgba(255,255,255,0.25);
}

.sidebar .logout-btn {
    margin-top: auto;
    background: rgba(255,255,255,0.15);
}

.sidebar .logout-btn:hover {
    background: rgba(255,255,255,0.3);
}

/* ── MAIN ── */
.main {
    margin-left: 230px;
    padding: 35px 30px;
    width: 100%;
}

.main h2 {
    font-size: 24px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 25px;
}

/* ── STAT CARDS ── */
.stat-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 18px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 14px;
    padding: 22px 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.07);
    border-left: 5px solid #4facfe;
}

.stat-card.pending  { border-left-color: #f39c12; }
.stat-card.progress { border-left-color: #3498db; }
.stat-card.done     { border-left-color: #2ecc71; }
.stat-card.total    { border-left-color: #9b59b6; }

.stat-card .number {
    font-size: 36px;
    font-weight: 700;
    color: #2d3748;
    line-height: 1;
}

.stat-card .label {
    font-size: 13px;
    color: #888;
    margin-top: 6px;
}

/* ── ACTION CARDS ── */
.action-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 30px;
}

.action-card {
    background: white;
    border-radius: 14px;
    padding: 24px 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.07);
    text-decoration: none;
    color: #2d3748;
    transition: transform 0.2s, box-shadow 0.2s;
    display: block;
}

.action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    color: #2d3748;
}

.action-card .icon {
    font-size: 28px;
    margin-bottom: 10px;
}

.action-card h5 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
}

.action-card p {
    font-size: 13px;
    color: #888;
    margin: 0;
}

/* ── LIVE ALERT ── */
.alert-box {
    background: white;
    border-radius: 14px;
    padding: 22px 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.07);
    display: flex;
    align-items: center;
    gap: 18px;
    max-width: 480px;
}

.live-dot {
    background: #f39c12;
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
    letter-spacing: 1px;
}

.alert-box .alert-text h5 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
}

.alert-box .alert-text p {
    font-size: 13px;
    color: #888;
    margin-bottom: 12px;
}

.btn-view {
    background: linear-gradient(135deg, #4facfe, #00c9a7);
    color: white;
    border: none;
    padding: 9px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: opacity 0.2s;
}

.btn-view:hover { opacity: 0.85; color: white; }
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="brand">🩺 Nurse Panel</div>

    <a href="dashboard.php" class="active">🏠 Dashboard</a>
    <a href="referrals.php?filter=Pending">📋 Pending Referrals</a>
    <a href="referrals.php?filter=In Progress">🔄 In Progress</a>
    <a href="referrals.php?filter=Completed">✅ Completed</a>

    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <h2>🩺 Nurse Dashboard</h2>

    <!-- STAT CARDS -->
    <div class="stat-cards">
        <div class="stat-card total">
            <div class="number"><?= $total ?></div>
            <div class="label">Total Referrals</div>
        </div>
        <div class="stat-card pending">
            <div class="number"><?= $pending ?></div>
            <div class="label">Pending Referrals</div>
        </div>
        <div class="stat-card progress">
            <div class="number"><?= $inprog ?></div>
            <div class="label">In Progress</div>
        </div>
        <div class="stat-card done">
            <div class="number"><?= $completed ?></div>
            <div class="label">Completed</div>
        </div>
    </div>

    <!-- ACTION CARDS -->
    <div class="action-cards">
        <a class="action-card" href="referrals.php?filter=Pending">
            <div class="icon">📋</div>
            <h5>Pending Referrals</h5>
            <p>View and attend to referrals awaiting nurse action.</p>
        </a>
        <a class="action-card" href="referrals.php?filter=In Progress">
            <div class="icon">🔄</div>
            <h5>In Progress</h5>
            <p>Continue referrals currently being processed.</p>
        </a>
        <a class="action-card" href="referrals.php?filter=Completed">
            <div class="icon">✅</div>
            <h5>Completed</h5>
            <p>Review referrals that have been completed.</p>
        </a>
    </div>

    <!-- LIVE ALERT -->
    <div class="alert-box">
        <div>
            <div class="live-dot">LIVE</div>
        </div>
        <div class="alert-text">
            <h5>🔔 <?= $pending ?> Pending Referral<?= $pending != 1 ? 's' : '' ?></h5>
            <p><?= $pending > 0 ? 'You have referrals that need nurse attention.' : 'All referrals are up to date.' ?></p>
            <a href="referrals.php?filter=Pending" class="btn-view">View Referrals</a>
        </div>
    </div>
</div>

</body>
</html>