<?php
session_start();
include __DIR__ . '/db.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Invalid patient ID");
}

/* 1. GET PATIENT INFO */
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    die("Patient not found");
}

/* 2. SQL QUERY FOR VISITS */
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; padding: 20px; background-color: #f4f7fb; }
        /* Itatago muna natin ang history card hangga't hindi pa naki-click ang OK sa alert */
        .history-card { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            display: <?= isset($_GET['msg']) ? 'none' : 'block' ?>; 
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #7f8c8d; font-size: 13px; text-transform: uppercase; }
        .status-pending { color: #f39c12; font-weight: 600; }
        .status-completed { color: #27ae60; font-weight: 600; }
        .btn-view {
            background: #8e44ad; color: white; padding: 8px 15px; 
            border-radius: 5px; text-decoration: none; font-size: 12px;
        }
    </style>
</head>
<body>

<div class="history-card" id="historyCard">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Visit History for <?= htmlspecialchars($patient['firstname'] . ' ' . $patient['lastname']) ?></h2>
        <a href="bhw_dashboard.php" style="color: #8e44ad; text-decoration: none; font-weight: 600;">← Back to Dashboard</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Vaccine</th>
                <th>Referral Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($visits as $v): ?>
            <tr>
                <td><?= date('M d, Y', strtotime($v['visit_date'])) ?></td>
                <!-- Inayos ang line 83 error dito gamit ang ?? '' -->
                <td><?= ucfirst($v['category'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['vaccine_name'] ?? 'N/A') ?></td>
                <td>
                    <span class="status-<?= strtolower($v['referral_status'] ?? 'none') ?>">
                        <?= strtoupper($v['referral_status'] ?? 'NONE') ?>
                    </span>
                </td>
                <td>
                    <a href="view_referral.php?visit_id=<?= $v['id'] ?>" class="btn-view">View Referral</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'Immunization Saved'): ?>
<script>
    // Lalabas ang Alert bago ipakita ang history
    Swal.fire({
        title: 'Successfully Sent!',
        text: 'The record has been saved and referred to the nurse.',
        icon: 'success',
        confirmButtonColor: '#8e44ad',
        confirmButtonText: 'View Patient History'
    }).then((result) => {
        if (result.isConfirmed) {
            // Ipapakita ang history card pagkatapos i-click ang OK
            document.getElementById('historyCard').style.display = 'block';
        }
    });
</script>
<?php endif; ?>

</body>
</html>
