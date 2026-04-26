<?php
include '../config/db.php';

if (isset($_POST['add_patient'])) {

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $birthday = $_POST['birthday'];
    $address = $_POST['address'];
    $contact = $_POST['contact_number'];
    $purpose = $_POST['purpose'];

    // insert patient first
    $stmt = $conn->prepare("
        INSERT INTO patients 
        (first_name, last_name, age, birthday, address, contact_number)
        VALUES (:first_name, :last_name, :age, :birthday, :address, :contact)
        RETURNING id
    ");

    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':age' => $age,
        ':birthday' => $birthday,
        ':address' => $address,
        ':contact' => $contact
    ]);

    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    $patient_id = $patient['id'];

    // QR code content (simple for now)
    $qr_data = "PATIENT-" . $patient_id;

    // update patient with QR code
    $update = $conn->prepare("
        UPDATE patients 
        SET qr_code = :qr 
        WHERE id = :id
    ");

    $update->execute([
        ':qr' => $qr_data,
        ':id' => $patient_id
    ]);

    header("Location: ../presentation/bhw/patient_list.php");
}
?>