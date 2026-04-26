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

    // 1. KUNIN ANG DATA MULA SA FORM
    $bp = $_POST['bp'] ?? '';
    $temp = $_POST['temperature'] ?? '';
    $hr = $_POST['heart_rate'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $height = $_POST['height'] ?? '';
    $notes = $_POST['notes'] ?? ''; // Ito ang papasok sa 'note' column

    try {
        // 2. SAVE SA 'immunization' TABLE (Sinunod ang columns na nakita mo sa pgAdmin)
        $stmt = $conn->prepare("
            INSERT INTO immunization 
            (patient_id, bp, temperature, heart_rate, weight, height, notes, created_by, created_at)
            VALUES 
            (:patient_id, :bp, :temp, :hr, :weight, :height, :note, :created_by, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':patient_id' => $patient_id,
            ':bp'         => $bp,
            ':temp'       => $temp,
            ':hr'         => $hr,
            ':weight'     => $weight,
            ':height'     => $height,
            ':note'       => $notes,
            ':created_by' => $_SESSION['user_id']
        ]);

        // 3. REFERRAL LOGIC (Kung pinindot ang 'Save and Referral')
        if (isset($_POST['save_and_referral'])) {
            $ref = $conn->prepare("
                INSERT INTO referrals 
                (patient_id, consultation_id, reason, status, created_by)
                VALUES 
                (:patient_id, :consultation_id, :reason, :status, :created_by)
            ");

            $ref->execute([
                ':patient_id'     => $patient_id,
                ':consultation_id'=> null,
                ':reason'         => 'Immunization (Note: ' . $notes . ')',
                ':status'         => 'Pending',
                ':created_by'     => $_SESSION['user_id']
            ]);

            echo "<h2 style='color:green;'>✅ Successfully Referred!</h2>";
            echo "<p>Patient referral has been sent to Nurse and recorded in Immunization.</p>";
        } else {
            echo "<h2 style='color:green;'>✅ Immunization Saved!</h2>";
            echo "<p>Patient record has been saved successfully in Immunization table.</p>";
        }

        echo "<br>";
        echo '<a href="/hms2/presentation/bhw/patient_list.php">⬅ Back to Patient List</a>';

    } catch (PDOException $e) {
        // Kung mag-error, dito natin malalaman kung may typo sa column name
        die("Database Error: " . $e->getMessage());
    }
}
?>