<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please login.");
}

if (isset($_POST['save_only']) || isset($_POST['save_and_referral'])) {

    $patient_id = $_POST['patient_id'] ?? null;

    if (!$patient_id) {
        die("Patient ID missing!");
    }

    // FORM DATA
    $bp = $_POST['bp'] ?? '';
    $temp = $_POST['temperature'] ?? '';
    $hr = $_POST['heart_rate'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $height = $_POST['height'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // SAVE IMMUNIZATION VISIT
    $stmt = $conn->prepare("
        INSERT INTO patient_visits
        (patient_id, category, notes, bp, temperature, heart_rate, weight, height, created_by)
        VALUES
        (:patient_id, 'Immunization', :notes, :bp, :temp, :hr, :weight, :height, :created_by)
    ");

    $stmt->execute([
        ':patient_id' => $patient_id,
        ':notes' => $notes,
        ':bp' => $bp,
        ':temp' => $temp,
        ':hr' => $hr,
        ':weight' => $weight,
        ':height' => $height,
        ':created_by' => $_SESSION['user_id']
    ]);

    // REFERRAL
    if (isset($_POST['save_and_referral'])) {

        $ref = $conn->prepare("
            INSERT INTO referrals
            (patient_id, purpose, status, created_by)
            VALUES
            (:patient_id, :purpose, :status, :created_by)
        ");

        $ref->execute([
            ':patient_id' => $patient_id,
            ':purpose' => 'Immunization - ' . $notes,
            ':status' => 'pending',
            ':created_by' => $_SESSION['user_id']
        ]);

        echo "<h2 style='color:green;'>✅ Successfully Referred!</h2>";
        echo "<p>Patient referral has been sent to Nurse.</p>";

    } else {

        echo "<h2 style='color:green;'>✅ Immunization Saved!</h2>";
        echo "<p>Patient record has been saved successfully.</p>";
    }

    echo "<br>";
    echo '<a href="/hms2/presentation/bhw/patient_list.php">⬅ Back to Patient List</a>';
}
?>