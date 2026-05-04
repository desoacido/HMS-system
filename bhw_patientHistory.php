<?php
session_start();
include __DIR__ . '/db.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Invalid patient ID");
}

/* 1. GET PATIENT INFO (MySQLi Style) */
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id); // Kailangan ng bind_param sa MySQLi
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc(); // Ito ang tamang paraan sa MySQLi

if (!$patient) {
    die("Patient not found");
}

/* 
   2. FIXED SQL QUERY FOR VISITS:
   Gagamit tayo ng LEFT JOIN para makuha ang vaccine_name mula sa immunization_visits
   at makita rin natin ang status mula sa referrals table.
*/
$stmt_visits = $conn->prepare("
    SELECT v.*, 
           iv.vaccine_name, 
           r.status AS referral_status
    FROM visits v 
    LEFT JOIN immunization_visits iv ON v.id = iv.visit_id
    LEFT JOIN referrals r ON v.id = r.visit_id
    WHERE v.patient_id = ?
    ORDER BY v.visit_date DESC
");
$stmt_visits->bind_param("i", $patient_id);
$stmt_visits->execute();
$result_visits = $stmt_visits->get_result();

// I-store lahat ng visits sa isang array
$visits = [];
while ($row = $result_visits->fetch_assoc()) {
    $visits[] = $row;
}
?>
