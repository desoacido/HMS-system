<?php
session_start();
include __DIR__ . '/db.php';

// TOTAL PATIENTS
$totalPatients = $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn();

// MALE VS FEMALE
$genderData = $conn->query("SELECT gender, COUNT(*) as count FROM patients GROUP BY gender")->fetchAll(PDO::FETCH_ASSOC);
$males = 0; $females = 0;
foreach ($genderData as $g) {
    if ($g['gender'] === 'Male') $males = $g['count'];
    else $females = $g['count'];
}

// BLOOD TYPE DISTRIBUTION
$bloodData = $conn->query("SELECT blood_type, COUNT(*) as count FROM patients GROUP BY blood_type ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);

// TOTAL VISITS
$totalVisits = $conn->query("SELECT COUNT(*) FROM visits")->fetchColumn();

// PATIENTS REGISTERED PER MONTH (current year)
$year = date('Y');
$monthlyData = $conn->query("
    SELECT MONTH(created_at) as month, COUNT(*) as count 
    FROM patients 
    WHERE YEAR(created_at) = $year
    GROUP BY MONTH(created_at)
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Build monthly array (1-12)
$monthly = array_fill(1, 12, 0);
foreach ($monthlyData as $m) {
    $monthly[(int)$m['month']] = (int)$m['count'];
}

// RECENT PATIENTS (last 5)
$recentPatients = $conn->query("
    SELECT firstname, lastname, gender, blood_type, created_at 
    FROM patients 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reports</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { padding:30px; background:#f4f7fb; }

    h2 { color:#1a7a4a; margin-bottom:22px; }

    /* STAT CARDS */
    .cards {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
        gap:16px;
        margin-bottom:26px;
    }
    .card {
        background:white;
        border-radius:14px;
        padding:22px 20px;
        box-shadow:0 4px 14px rgba(0,0,0,0.06);
        display:flex;
        flex-direction:column;
        gap:6px;
        border-left:5px solid #28a745;
        transition: transform 0.2s;
    }
    .card:hover { transform:translateY(-3px); }
    .card .icon { font-size:28px; }
    .card .label { font-size:12px; color:#888; font-weight:600; text-transform:uppercase; }
    .card .value { font-size:28px; font-weight:700; color:#1a7a4a; }
    .card.blue  { border-left-color:#1565c0; }
    .card.blue .value { color:#1565c0; }
    .card.pink  { border-left-color:#c2185b; }
    .card.pink .value { color:#c2185b; }
    .card.orange { border-left-color:#e65100; }
    .card.orange .value { color:#e65100; }

    /* CHARTS ROW */
    .charts-row {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:16px;
        margin-bottom:26px;
    }

    .chart-card {
        background:white;
        border-radius:14px;
        padding:24px;
        box-shadow:0 4px 14px rgba(0,0,0,0.06);
    }
    .chart-card h3 {
        color:#1a7a4a;
        font-size:14px;
        margin-bottom:18px;
        padding-bottom:10px;
        border-bottom:2px solid #e8f5e9;
    }

    /* BAR CHART */
    .bar-chart { display:flex; align-items:flex-end; gap:6px; height:140px; }
    .bar-wrap { display:flex; flex-direction:column; align-items:center; flex:1; height:100%; justify-content:flex-end; }
    .bar {
        width:100%;
        background:linear-gradient(180deg,#28a745,#1a7a4a);
        border-radius:5px 5px 0 0;
        min-height:4px;
        transition:height 0.5s ease;
        position:relative;
    }
    .bar:hover { opacity:0.8; }
    .bar-label { font-size:10px; color:#aaa; margin-top:5px; }
    .bar-val { font-size:10px; color:#555; margin-bottom:3px; font-weight:600; }

    /* DONUT CHART */
    .donut-wrap { display:flex; align-items:center; gap:24px; }
    .donut-legend { display:flex; flex-direction:column; gap:10px; }
    .legend-item { display:flex; align-items:center; gap:8px; font-size:13px; color:#444; }
    .legend-dot { width:12px; height:12px; border-radius:50%; }

    /* BLOOD TYPE BARS */
    .blood-bars { display:flex; flex-direction:column; gap:10px; }
    .blood-row { display:flex; align-items:center; gap:10px; }
    .blood-name { font-size:12px; color:#555; width:30px; font-weight:600; }
    .blood-bar-bg { flex:1; background:#f0f0f0; border-radius:20px; height:10px; }
    .blood-bar-fill {
        height:10px;
        border-radius:20px;
        background:linear-gradient(90deg,#ff7043,#e65100);
        transition:width 0.6s ease;
    }
    .blood-count { font-size:12px; color:#888; width:20px; text-align:right; }

    /* RECENT TABLE */
    .recent-card {
        background:white;
        border-radius:14px;
        padding:24px;
        box-shadow:0 4px 14px rgba(0,0,0,0.06);
        margin-bottom:26px;
    }
    .recent-card h3 {
        color:#1a7a4a;
        font-size:14px;
        margin-bottom:16px;
        padding-bottom:10px;
        border-bottom:2px solid #e8f5e9;
    }
    table { width:100%; border-collapse:collapse; font-size:13px; }
    thead { background:#f6fff9; }
    thead th { padding:10px 14px; text-align:left; color:#1a7a4a; font-size:12px; font-weight:600; text-transform:uppercase; }
    tbody tr { border-bottom:1px solid #f5f5f5; }
    tbody tr:hover { background:#fafffe; }
    tbody td { padding:10px 14px; color:#444; }

    .badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .badge-male   { background:#e3f0ff; color:#1565c0; }
    .badge-female { background:#fce4ec; color:#c2185b; }
    .blood-badge  { background:#fff3e0; color:#e65100; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }

    @media (max-width:700px) {
        .charts-row { grid-template-columns:1fr; }
    }
</style>
</head>
<body>

<h2>📊 Reports & Statistics</h2>

<!-- STAT CARDS -->
<div class="cards">
    <div class="card">
        <span class="icon">👥</span>
        <span class="label">Total Patients</span>
        <span class="value"><?= $totalPatients ?></span>
    </div>
    <div class="card blue">
        <span class="icon">♂️</span>
        <span class="label">Male Patients</span>
        <span class="value"><?= $males ?></span>
    </div>
    <div class="card pink">
        <span class="icon">♀️</span>
        <span class="label">Female Patients</span>
        <span class="value"><?= $females ?></span>
    </div>
    <div class="card orange">
        <span class="icon">🏥</span>
        <span class="label">Total Visits</span>
        <span class="value"><?= $totalVisits ?></span>
    </div>
</div>

<!-- CHARTS ROW -->
<div class="charts-row">

    <!-- MONTHLY BAR CHART -->
    <div class="chart-card">
        <h3>📅 Patients Registered per Month (<?= $year ?>)</h3>
        <?php
        $maxVal = max(array_values($monthly)) ?: 1;
        ?>
        <div class="bar-chart">
            <?php for ($m = 1; $m <= 12; $m++): ?>
            <?php $h = round(($monthly[$m] / $maxVal) * 120); ?>
            <div class="bar-wrap">
                <span class="bar-val"><?= $monthly[$m] > 0 ? $monthly[$m] : '' ?></span>
                <div class="bar" style="height:<?= max($h, $monthly[$m] > 0 ? 10 : 4) ?>px"></div>
                <span class="bar-label"><?= $monthNames[$m-1] ?></span>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- GENDER DONUT -->
    <div class="chart-card">
        <h3>⚧ Gender Distribution</h3>
        <?php
        $total_g = $males + $females ?: 1;
        $maleAngle = round(($males / $total_g) * 360);
        $femaleAngle = 360 - $maleAngle;
        $malePct = round(($males / $total_g) * 100);
        $femalePct = 100 - $malePct;
        ?>
        <div class="donut-wrap">
            <svg width="130" height="130" viewBox="0 0 42 42">
                <circle cx="21" cy="21" r="15.91" fill="none" stroke="#e3f0ff" stroke-width="6"/>
                <!-- Male arc -->
                <circle cx="21" cy="21" r="15.91" fill="none"
                    stroke="#1565c0" stroke-width="6"
                    stroke-dasharray="<?= round(($males/$total_g)*100, 1) ?> <?= 100 - round(($males/$total_g)*100, 1) ?>"
                    stroke-dashoffset="25"
                    transform="rotate(-90 21 21)"/>
                <!-- Female arc -->
                <circle cx="21" cy="21" r="15.91" fill="none"
                    stroke="#c2185b" stroke-width="6"
                    stroke-dasharray="<?= round(($females/$total_g)*100, 1) ?> <?= 100 - round(($females/$total_g)*100, 1) ?>"
                    stroke-dashoffset="<?= 25 - round(($males/$total_g)*100, 1) ?>"
                    transform="rotate(-90 21 21)"/>
                <text x="21" y="24" text-anchor="middle" font-size="6" font-family="Poppins" font-weight="bold" fill="#333"><?= $total_g ?></text>
            </svg>
            <div class="donut-legend">
                <div class="legend-item">
                    <div class="legend-dot" style="background:#1565c0"></div>
                    Male — <strong><?= $males ?></strong> (<?= $malePct ?>%)
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:#c2185b"></div>
                    Female — <strong><?= $females ?></strong> (<?= $femalePct ?>%)
                </div>
            </div>
        </div>
    </div>

    <!-- BLOOD TYPE -->
    <div class="chart-card">
        <h3>🩸 Blood Type Distribution</h3>
        <div class="blood-bars">
        <?php
        $maxBlood = !empty($bloodData) ? max(array_column($bloodData, 'count')) : 1;
        foreach ($bloodData as $b):
            $pct = round(($b['count'] / $maxBlood) * 100);
        ?>
            <div class="blood-row">
                <span class="blood-name"><?= htmlspecialchars($b['blood_type']) ?></span>
                <div class="blood-bar-bg">
                    <div class="blood-bar-fill" style="width:<?= $pct ?>%"></div>
                </div>
                <span class="blood-count"><?= $b['count'] ?></span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($bloodData)): ?>
            <p style="color:#aaa; font-size:13px;">No data yet.</p>
        <?php endif; ?>
        </div>
    </div>

    <!-- QUICK SUMMARY -->
    <div class="chart-card">
        <h3>📝 Quick Summary</h3>
        <div style="display:flex; flex-direction:column; gap:12px; font-size:13px; color:#555;">
            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0f0f0; padding-bottom:10px;">
                <span>Total Patients</span>
                <strong style="color:#1a7a4a"><?= $totalPatients ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0f0f0; padding-bottom:10px;">
                <span>Male Patients</span>
                <strong style="color:#1565c0"><?= $males ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0f0f0; padding-bottom:10px;">
                <span>Female Patients</span>
                <strong style="color:#c2185b"><?= $females ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0f0f0; padding-bottom:10px;">
                <span>Total Visits Logged</span>
                <strong style="color:#e65100"><?= $totalVisits ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>Registered This Year</span>
                <strong style="color:#1a7a4a"><?= array_sum($monthly) ?></strong>
            </div>
        </div>
    </div>

</div>

<!-- RECENT PATIENTS TABLE -->
<div class="recent-card">
    <h3>🕐 Recently Registered Patients</h3>
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Blood Type</th>
                <th>Date Registered</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($recentPatients)): ?>
            <tr><td colspan="4" style="text-align:center; color:#aaa; padding:30px;">No patients yet.</td></tr>
        <?php else: ?>
            <?php foreach ($recentPatients as $r): ?>
            <tr>
                <td><strong><?= htmlspecialchars($r['firstname'] . ' ' . $r['lastname']) ?></strong></td>
                <td>
                    <span class="badge <?= $r['gender'] === 'Male' ? 'badge-male' : 'badge-female' ?>">
                        <?= $r['gender'] ?>
                    </span>
                </td>
                <td><span class="blood-badge"><?= htmlspecialchars($r['blood_type']) ?></span></td>
                <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>