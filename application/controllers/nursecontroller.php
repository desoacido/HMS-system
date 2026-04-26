<?php
include '../config/db.php';
session_start();

if (isset($_POST['submit_nurse'])) {
    $referral_id = $_POST['referral_id'];
    $patient_id  = $_POST['patient_id'];
    $assessment  = $_POST['assessment']; // Input ng Nurse
    $diagnosis   = $_POST['diagnosis'];  // Input ng Nurse
    $notes       = $_POST['notes'];      // Extra notes

    // 1. Kunin ang Age at REASON ng Pasyente (Importante ang Reason para sa ML)
    $p_stmt = $conn->prepare("
        SELECT p.birthday, r.reason 
        FROM patients p 
        JOIN referrals r ON r.patient_id = p.id 
        WHERE r.id = :rid
    ");
    $p_stmt->execute([':rid' => $referral_id]);
    $data = $p_stmt->fetch();

    $age = 0;
    $reason_text = $data['reason'] ?? 'General';

    if ($data && $data['birthday']) {
        $bday = new DateTime($data['birthday']);
        $today = new DateTime();
        $age = $today->diff($bday)->y;
    }

    // 2. Kunin ang pinakabagong Vitals
    $v_stmt = $conn->prepare("SELECT * FROM patient_visits WHERE patient_id = :pid ORDER BY created_at DESC LIMIT 1");
    $v_stmt->execute([':pid' => $patient_id]);
    $v = $v_stmt->fetch();

    $v_temp = $v['temperature'] ?? 36.5;
    $v_hr   = $v['heart_rate'] ?? 75;
    $v_bp   = $v['bp'] ?? "120/80";

    // ---------------------------------------------------------
    // 🤖 MACHINE LEARNING TRIGGER (8 Arguments)
    // ---------------------------------------------------------
    $python = "python";
    $script = "C:/xampp/htdocs/hms2/ml/predict.py";
    
    // Ginamit ang escapeshellarg para iwas error sa spaces o special characters
    $command = "$python \"$script\" " . 
               escapeshellarg($assessment) . " " . 
               escapeshellarg($v_temp) . " " . 
               escapeshellarg($v_hr) . " " . 
               escapeshellarg($v_bp) . " " . 
               escapeshellarg($age) . " 0 0 " . 
               escapeshellarg($reason_text) . " 2>&1";

    $output = shell_exec($command);

    $ai_label = "Unknown";
    $ai_score = 0;

    if ($output) {
        $res = explode('|', trim($output));
        if (count($res) == 2) {
            $ai_label = $res[0];
            $ai_score = (float)$res[1];
        }
    }

    // 3. I-UPDATE ang Referrals Table
    try {
        $update = $conn->prepare("
            UPDATE referrals 
            SET nurse_assessment = :asm, 
                nurse_diagnosis = :diag, 
                nurse_notes = :notes,
                ai_validation_label = :ai_l,
                ai_validation_score = :ai_s,
                status = 'reviewed'
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

        // 4. Redirect pabalik sa View Page
        header("Location: ../../presentation/nurse/view_referral.php?id=$referral_id&success=1");
        exit();

    } catch (PDOException $e) {
        die("Database Update Error: " . $e->getMessage());
    }
}

// 5. LOGIC PARA SA "MARK AS COMPLETED" BUTTON
if (isset($_POST['complete'])) {
    $referral_id = $_POST['referral_id'];
    try {
        $stmt = $conn->prepare("UPDATE referrals SET status = 'Completed' WHERE id = :id");
        $stmt->execute([':id' => $referral_id]);
        header("Location: ../../presentation/nurse/dashboard.php?completed=1");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>