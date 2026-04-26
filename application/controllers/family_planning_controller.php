<?php
include '../config/db.php';

if (isset($_POST['save_only']) || isset($_POST['save_and_referral'])) {

    $patient_id = $_POST['patient_id'];
    $age = $_POST['age'];
    $children = $_POST['children'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $breastfeeding = $_POST['breastfeeding'];
    $smoking = $_POST['smoking'];
    $bp = $_POST['bp'];

    // BMI CALCULATION (SERVER SIDE SAFETY)
    $height_m = $height / 100;
    $bmi = ($height_m > 0) ? $weight / ($height_m * $height_m) : 0;

    // SAVE DATA
    $stmt = $conn->prepare("
        INSERT INTO family_planning 
        (patient_id, age, children, height, weight, bmi, breastfeeding, smoking, bp)
        VALUES (:patient_id, :age, :children, :height, :weight, :bmi, :breastfeeding, :smoking, :bp)
    ");

    $stmt->execute([
        ':patient_id' => $patient_id,
        ':age' => $age,
        ':children' => $children,
        ':height' => $height,
        ':weight' => $weight,
        ':bmi' => $bmi,
        ':breastfeeding' => $breastfeeding,
        ':smoking' => $smoking,
        ':bp' => $bp
    ]);

    // REFERRAL
    if (isset($_POST['save_and_referral'])) {

        $ref = $conn->prepare("
            INSERT INTO referrals 
            (patient_id, consultation_id, reason, status, created_by)
            VALUES 
            (:patient_id, :consultation_id, :reason, :status, :created_by)
        ");

        $ref->execute([
            ':patient_id' => $patient_id,
            ':consultation_id' => null,
            ':reason' => 'Family Planning Assessment (BMI Included)',
            ':status' => 'pending',
            ':created_by' => 'BHW'
        ]);
    }

    echo "<h3>✔ Saved successfully</h3>";

    if (isset($_POST['save_and_referral'])) {
        echo "<h4>✔ Referral sent to Nurse</h4>";
    }

    echo "<a href='../presentation/bhw/patient_list.php'>⬅ Back</a>";
}
?>