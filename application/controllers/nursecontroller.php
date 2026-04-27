<?php
include '../config/db.php';
session_start();

if (isset($_POST['submit_nurse'])) {

    $referral_id = $_POST['referral_id'];
    $patient_id  = $_POST['patient_id'];
    $assessment  = $_POST['assessment'];
    $diagnosis   = $_POST['diagnosis'];
    $notes = $_POST['notes'] ?? '';

    // 1. GET PATIENT + PURPOSE
    $p_stmt = $conn->prepare("
        SELECT r.purpose 
        FROM patients p 
        JOIN referrals r ON r.patient_id = p.id 
        WHERE r.id = :rid
    ");
    $p_stmt->execute([':rid' => $referral_id]);
    $data = $p_stmt->fetch(PDO::FETCH_ASSOC);

    $age = 0;
    $purpose_text = $data['purpose'] ?? 'General';

    // 2. GET LATEST VITALS
    $v_stmt = $conn->prepare("
        SELECT * FROM patient_visits 
        WHERE patient_id = :pid 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $v_stmt->execute([':pid' => $patient_id]);
    $v = $v_stmt->fetch(PDO::FETCH_ASSOC);

    $v_temp = $v['temperature'] ?? 36.5;
    $v_hr   = $v['heart_rate'] ?? 75;
    $v_bp   = $v['bp'] ?? "120/80";

    // ---------------------------------------------------------
    // 🤖 ML TRIGGER - Render API
    // ---------------------------------------------------------
    $ml_url = "https://hms-ml-api.onrender.com/predict";

    $payload = json_encode(["symptom" => $assessment]);

    $ch = curl_init($ml_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    curl_close($ch);

    $ai_label = "Unknown";
    $ai_score = 0;

    if ($response) {
        $res = json_decode($response, true);
        $ai_label = $res['label'] ?? "Unknown";
        $ai_score = $res['score'] ?? 0;
    }

    // 3. UPDATE REFERRAL
    $update = $conn->prepare("
        UPDATE referrals 
        SET nurse_assessment = :asm,
            nurse_diagnosis = :diag,
            nurse_notes = :notes,
            ai_validation_label = :ai_l,
            ai_validation_score = :ai_s,
            status = 'completed'
        WHERE id = :rid
    ");

    $update->execute([
        ':asm'   => $assessment,
        ':diag'  => $diagnosis,
        ':notes' => $notes,
        ':ai_l'  => $ai_label,
        ':ai_s'  => $ai_score,
        ':rid'   => $referral_id
    ]);

    header("Location: ../../presentation/nurse/view_referral.php?id=$referral_id&success=1");
    exit();
}
?>
