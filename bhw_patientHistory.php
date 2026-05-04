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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient History</title>
    <!-- Isama ang SweetAlert2 para sa magandang alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; padding: 20px; background-color: #f4f7fb; }
        .history-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .status-pending { color: orange; font-weight: bold; }
        .status-completed { color: green; font-weight: bold; }
    </style>
</head>
<body>

<div class="history-card">
    <h2>Visit History for <?= htmlspecialchars($patient['firstname'] . ' ' . $patient['lastname']) ?></h2>
    <a href="bhw_dashboard.php">← Back to Dashboard</a>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Vaccine</th>
                <th>Referral Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($visits as $v): ?>
            <tr>
                <td><?= date('M d, Y', strtotime($v['visit_date'])) ?></td>
                <td><?= ucfirst($v['category']) ?></td>
                <td><?= htmlspecialchars($v['vaccine_name'] ?? 'N/A') ?></td>
                <td>
                    <span class="status-<?= strtolower($v['referral_status']) ?>">
                        <?= strtoupper($v['referral_status'] ?? 'NONE') ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- DITO LALABAS ANG ALERT PAGKA-REFER -->
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'Immunization Saved'): ?>
<script>
    Swal.fire({
        title: 'Successfully Sent!',
        text: 'The record has been saved and referred to the nurse.',
        icon: 'success',
        confirmButtonColor: '#8e44ad'
    });
</script>
<?php endif; ?>

</body>
</html>
