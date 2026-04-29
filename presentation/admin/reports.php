<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

// Total patients
$total = $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn();

// Monthly registrations (current year)
$year = date('Y');
$monthly = $conn->query("
    SELECT MONTH(created_at) as month, COUNT(*) as count 
    FROM patients 
    WHERE YEAR(created_at) = $year 
    GROUP BY MONTH(created_at)
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Age groups from birthdate
$ageGroups = ['0-12' => 0, '13-17' => 0, '18-59' => 0, '60+' => 0];
$patients = $conn->query("SELECT birthdate FROM patients")->fetchAll(PDO::FETCH_ASSOC);
foreach ($patients as $p) {
    if (!empty($p['birthdate'])) {
        $age = (new DateTime($p['birthdate']))->diff(new DateTime())->y;
        if ($age <= 12) $ageGroups['0-12']++;
        elseif ($age <= 17) $ageGroups['13-17']++;
        elseif ($age <= 59) $ageGroups['18-59']++;
        else $ageGroups['60+']++;
    }
}

// Top addresses
$addresses = $conn->query("
    SELECT address, COUNT(*) as count 
    FROM patients 
    GROUP BY address 
    ORDER BY count DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Prepare monthly data for chart
$monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$monthlyData = array_fill(0, 12, 0);
foreach ($monthly as $m) {
    $monthlyData[$m['month'] - 1] = (int)$m['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }
    body {
        background: #f4f7fb;
        padding: 30px;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    h2 { color: #333; }
    .btn-back {
        background: #4facfe;
        color: white;
        padding: 8px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
    }
    .btn-back:hover { background: #3a8fdd; }
    .btn-print {
        background: #5cb85c;
        color: white;
        padding: 8px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        border: none;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        margin-left: 10px;
    }
    .btn-print:hover { background: #449d44; }

    /* Summary Cards */
    .summary-cards {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 20px 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        flex: 1;
        min-width: 150px;
        text-align: center;
    }
    .summary-card .number {
        font-size: 36px;
        font-weight: 600;
        color: #4facfe;
    }
    .summary-card .label {
        font-size: 13px;
        color: #888;
        margin-top: 5px;
    }

    /* Charts */
    .charts-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    .chart-box {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        flex: 2;
        min-width: 300px;
    }
    .chart-box.small {
        flex: 1;
        min-width: 250px;
    }
    .chart-box h3 {
        font-size: 15px;
        color: #333;
        margin-bottom: 15px;
    }

    /* Top Addresses Table */
    .table-box {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }
    .table-box h3 {
        font-size: 15px;
        color: #333;
        margin-bottom: 15px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 10px 12px;
        text-align: left;
        font-size: 13px;
    }
    th {
        background: #4facfe;
        color: white;
    }
    tr:nth-child(even) { background: #f9f9f9; }

    @media print {
        .btn-back, .btn-print { display: none; }
        body { padding: 10px; }
    }
</style>
</head>
<body>

<div class="header">
    <h2>📊 Reports & Statistics</h2>
    <div>
        <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
        <button class="btn-print" onclick="window.print()">🖨 Print Report</button>
    </div>
</div>

<!-- Summary Cards -->
<div class="summary-cards">
    <div class="summary-card">
        <div class="number"><?= $total ?></div>
        <div class="label">Total Patients</div>
    </div>
    <div class="summary-card">
        <div class="number"><?= $ageGroups['0-12'] ?></div>
        <div class="label">Children (0-12)</div>
    </div>
    <div class="summary-card">
        <div class="number"><?= $ageGroups['13-17'] ?></div>
        <div class="label">Teens (13-17)</div>
    </div>
    <div class="summary-card">
        <div class="number"><?= $ageGroups['18-59'] ?></div>
        <div class="label">Adults (18-59)</div>
    </div>
    <div class="summary-card">
        <div class="number"><?= $ageGroups['60+'] ?></div>
        <div class="label">Senior (60+)</div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-row">
    <!-- Monthly Registrations Bar Chart -->
    <div class="chart-box">
        <h3>📅 Monthly Patient Registrations (<?= $year ?>)</h3>
        <canvas id="monthlyChart" height="100"></canvas>
    </div>
    <!-- Age Group Pie Chart -->
    <div class="chart-box small">
        <h3>👥 Age Group Breakdown</h3>
        <canvas id="ageChart"></canvas>
    </div>
</div>

<!-- Top Addresses -->
<div class="table-box">
    <h3>📍 Top 5 Locations with Most Patients</h3>
    <table>
        <tr>
            <th>#</th>
            <th>Address / Barangay</th>
            <th>No. of Patients</th>
        </tr>
        <?php foreach ($addresses as $i => $a): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= $a['address'] ?></td>
            <td><?= $a['count'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    // Monthly Bar Chart
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($monthNames) ?>,
            datasets: [{
                label: 'Patients Registered',
                data: <?= json_encode($monthlyData) ?>,
                backgroundColor: '#4facfe',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Age Group Pie Chart
    new Chart(document.getElementById('ageChart'), {
        type: 'doughnut',
        data: {
            labels: ['0-12', '13-17', '18-59', '60+'],
            datasets: [{
                data: <?= json_encode(array_values($ageGroups)) ?>,
                backgroundColor: ['#4facfe', '#f0ad4e', '#5cb85c', '#d9534f'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Poppins' } } }
            }
        }
    });
</script>

</body>
</html>
