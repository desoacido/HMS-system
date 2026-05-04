<?php
session_start();
include __DIR__ . '/db.php';

$visit_id = $_GET['visit_id'] ?? 0;
$patient_id = $_GET['patient_id'] ?? 0;

if (!$visit_id || !$patient_id) {
    die("Invalid request");
}

/* ================= CHECK IF ALREADY REFERRED ================= */
$check = $conn->prepare("
    SELECT id FROM referrals 
    WHERE visit_id = ?
");
$check->execute([$visit_id]);

if ($check->rowCount() == 0) {

    /* ================= INSERT REFERRAL ================= */
    $stmt = $conn->prepare("
        INSERT INTO referrals 
        (source_type, visit_id, patient_id, referred_by, status)
        VALUES ('checkup', ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $visit_id,
        $patient_id,
        $_SESSION['user_id']
    ]);

    $_SESSION['success'] = "Sent to nurse successfully.";

} else {
    $_SESSION['success'] = "Already referred.";
}

/* ================= REDIRECT ================= */
header("Location: bhw_viewvisit.php?visit_id=$visit_id&patient_id=$patient_id");
exit;
?>