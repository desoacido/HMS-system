<?php
session_start();
include __DIR__ . '/db.php';

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    die("Invalid patient ID");
}

/* GET PATIENT INFO */
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found");
}

/* 
   FIXED SQL QUERY: 
   Gagamit tayo ng LEFT JOIN para makuha ang vaccine_name mula sa immunization_visits
   at makita rin natin ang status mula sa referrals table.
*/
$stmt = $conn->prepare("
    SELECT v.*, 
           iv.vaccine_name, 
           r.status AS referral_status
    FROM visits v 
    LEFT JOIN immunization_visits iv ON v.id = iv.visit_id
    LEFT JOIN referrals r ON v.id = r.visit_id
    WHERE v.patient_id = ?
    ORDER BY v.visit_date DESC
");
$stmt->execute([$patient_id]);
$visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient History</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: Poppins; background: #f4f7fb; padding: 30px; }
        .container { max-width: 1000px; margin: auto; }
        .card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        h2 { color: #1a7a4a; margin-top: 0; }
        .info p { margin: 5px 0; font-size: 14px; }
        .visit { background: #fafafa; padding: 15px; border-left: 5px solid #28a745; margin-top: 15px; border-radius: 8px; position: relative; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; color: white; text-transform: uppercase; font-weight: 600; }
        .status-badge { float: right; font-size: 10px; padding: 2px 8px; border-radius: 5px; background: #ddd; color: #555; }
        
        /* Categories */
        .checkup { background: #2a5298; }
        .immunization { background: #28a745; }
        .family { background: #9c27b0; }
        
        /* Referral Status Colors */
        .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-completed { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .btn { padding: 8px 15px; border: none; border-radius: 6px; font-size: 12px; cursor: pointer; margin-right: 5px; transition: 0.3s; }
        .btn-view { background: #2a5298; color: white; }
        .btn-add { background: #28a745; color: white; margin-bottom: 20px; }
        .empty { text-align: center; padding: 20px; color: #888; }
    </style>
</head>
<body>

<div class="container">

    <!-- PATIENT INFO -->
    <div class="card info">
        <h2>👤 Patient Profile</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div>
                <p><b>Name:</b> <?= htmlspecialchars($patient['firstname'].' '.$patient['lastname']) ?></p>
                <p><b>Gender:</b> <?= $patient['gender'] ?></p>
            </div>
            <div>
                <p><b>Contact:</b> <?= $patient['contact'] ?? '—' ?></p>
                <p><b>Address:</b> <?= htmlspecialchars($patient['address'] ?? '—') ?></p>
            </div>
        </div>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
        <button class="btn btn-add" onclick="window.location='bhw_addvisit.php?patient_id=<?= $patient_id ?>'">
            ➕ Add New Visit
        </button>
    </div>

    <!-- VISITS -->
    <div class="card">
        <h2>📋 Visit Records</h2>

        <?php if (empty($visits)): ?>
            <div class="empty">No visits recorded for this patient.</div>
        <?php else: ?>

            <?php foreach ($visits as $v): ?>
                <?php
                    // Logic para sa kulay ng badge
                    $cat_class = "checkup";
                    $display_cat = $v['category'] ?: 'General'; // Default kung walang category

                    if ($v['category'] == 'immunization') $cat_class = "immunization";
                    if ($v['category'] == 'family_planning') $cat_class = "family";
                ?>

                <div class="visit" style="border-left-color: <?= ($v['category'] == 'immunization') ? '#28a745' : '#2a5298' ?>;">
                    
                    <!-- Status ng Referral (Para alam mo kung nakita na ni Nurse) -->
                    <?php if ($v['referral_status']): ?>
                        <span class="status-badge <?= $v['referral_status'] == 'pending' ? 'status-pending' : 'status-completed' ?>">
                            Referral: <?= ucfirst($v['referral_status']) ?>
                        </span>
                    <?php endif; ?>

                    <p>
                        <span class="badge <?= $cat_class ?>">
                            <?= $display_cat ?>
                        </span>
                    </p>

                    <p><b>Date:</b> <?= date('M d, Y - h:i A', strtotime($v['visit_date'])) ?></p>
                    
                    <!-- Dito lilitaw ang Vaccine Name kung immunization ito -->
                    <?php if ($v['category'] == 'immunization' && $v['vaccine_name']): ?>
                        <p><b>Vaccine:</b> <span style="color:#28a745; font-weight:600;"><?= $v['vaccine_name'] ?></span></p>
                    <?php endif; ?>

                    <div style="margin-top:10px;">
                        <button class="btn btn-view" onclick="window.location='bhw_viewvisit.php?visit_id=<?= $v['id'] ?>&category=<?= $v['category'] ?>'">
                            👁 View Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>

</body>
</html>