<?php
include '../../application/config/db.php';
include '../../phpqrcode/qrlib.php';

$message = "";

if (isset($_POST['add_patient'])) {

    // INSERT PATIENT (NO QR YET)
    $stmt = $conn->prepare("INSERT INTO patients 
        (first_name, last_name, age, birthday, address, contact_number, qr_code)
        VALUES 
        (:first, :last, :age, :birthday, :address, :contact, NULL)");

    $stmt->execute([
        ':first' => $_POST['first_name'],
        ':last' => $_POST['last_name'],
        ':age' => $_POST['age'],
        ':birthday' => $_POST['birthday'],
        ':address' => $_POST['address'],
        ':contact' => $_POST['contact_number']
    ]);

    $patient_id = $conn->lastInsertId();

    // QR folder
    $path = "../../qrcodes/";
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    $file = $path . "patient_" . $patient_id . ".png";

    $data = "http://localhost/hms2/presentation/patient/view.php?id=" . $patient_id;

    QRcode::png($data, $file, QR_ECLEVEL_L, 6);

    $stmt = $conn->prepare("UPDATE patients SET qr_code = :qr WHERE id = :id");
    $stmt->execute([
        ':qr' => "qrcodes/patient_" . $patient_id . ".png",
        ':id' => $patient_id
    ]);

    $message = "Patient added successfully + QR generated 👍";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Patient</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

body{
    background:#f4f7fb;
    padding:25px;
}

.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#555;
    background:white;
    padding:8px 12px;
    border-radius:8px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.container{
    max-width:600px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:15px;
    color:#333;
}

input{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border-radius:8px;
    border:1px solid #ccc;
    outline:none;
}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:linear-gradient(135deg,#43e97b,#38f9d7);
    color:white;
    font-weight:600;
    cursor:pointer;
}

button:hover{
    opacity:0.9;
}

.success{
    background:#e8f5e9;
    color:#1b5e20;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    font-size:14px;
}

</style>
</head>

<body>

<a href="/hms2/presentation/bhw/dashboard.php" class="back">⬅ Back to Dashboard</a>

<div class="container">

<h2>Add Patient</h2>

<?php if($message): ?>
    <div class="success"><?= $message ?></div>
<?php endif; ?>

<form method="POST">

    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="number" name="age" placeholder="Age" required>
    <input type="date" name="birthday" required>
    <input type="text" name="address" placeholder="Address" required>
    <input type="text" name="contact_number" placeholder="Contact Number" required>

    <button type="submit" name="add_patient">Add Patient</button>

</form>

</div>

</body>
</html>
