<?php
session_start();
include __DIR__ . '/db.php';

// TOTAL PATIENTS
$totalRes = $conn->query("SELECT COUNT(*) as count FROM patients");
$totalPatients = $totalRes->fetch_assoc()['count'] ?? 0;

// MALE VS FEMALE
$genderRes = $conn->query("SELECT gender, COUNT(*) as count FROM patients GROUP BY gender");
$males = 0; $females = 0;
while ($g = $genderRes->fetch_assoc()) {
    if ($g['gender'] === 'Male') $males = $g['count'];
    else if ($g['gender'] === 'Female') $females = $g['count'];
}

// BLOOD TYPE DISTRIBUTION
$bloodRes = $conn->query("SELECT blood_type, COUNT(*) as count FROM patients GROUP BY blood_type ORDER BY count DESC");
$bloodData = $bloodRes->fetch_all(MYSQLI_ASSOC);

// TOTAL VISITS
$visitRes = $conn->query("SELECT COUNT(*) as count FROM visits");
$totalVisits = $visitRes->fetch_assoc()['count'] ?? 0;

// PATIENTS REGISTERED PER MONTH (current year)
$year = date('Y');
$monthlyRes = $conn->query("
    SELECT MONTH(created_at) as month, COUNT(*) as count 
    FROM patients 
    WHERE YEAR(created_at) = $year
    GROUP BY MONTH(created_at)
    ORDER BY month ASC
");
$monthlyData = $monthlyRes->fetch_all(MYSQLI_ASSOC);

// Build monthly array (1-12)
$monthly = array_fill(1, 12, 0);
foreach ($monthlyData as $m) {
    $monthly[(int)$m['month']] = (int)$m['count'];
}

// RECENT PATIENTS (last 5)
$recentRes = $conn->query("
    SELECT firstname, lastname, gender, blood_type, created_at 
    FROM patients 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentPatients = $recentRes->fetch_all(MYSQLI_ASSOC);

$monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
?>
