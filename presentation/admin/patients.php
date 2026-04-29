<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';
// fetch patients
$stmt = $conn->query("SELECT * FROM patients ORDER BY id ASC");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patients List</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }
    body {
        background: #f4f7fb;
        padding: 30px;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    h2 {
        color: #333;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px;
        text-align: left;
    }
    th {
        background: #4facfe;
        color: white;
    }
    tr:nth-child(even) {
        background: #f9f9f9;
    }
    .back {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        color: #555;
    }
</style>
</head>
<body>
<div class="header">
    <h2>Patients List</h2>
</div>
<table>
    <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Birthdate</th>
        <th>Age</th>
        <th>Address</th>
        <th>Contact</th>
    </tr>
    <?php foreach ($patients as $p): ?>
    <?php
        $age = 'N/A';
        if (!empty($p['birthdate'])) {
            $birth = new DateTime($p['birthdate']);
            $today = new DateTime();
            $age = $birth->diff($today)->y;
        }
    ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= $p['first_name'] ?></td>
        <td><?= $p['last_name'] ?></td>
        <td><?= $p['birthdate'] ?></td>
        <td><?= $age ?></td>
        <td><?= $p['address'] ?></td>
        <td><?= $p['contact_number'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="../admin/dashboard.php" class="back">⬅ Back to Dashboard</a>
</body>
</html>
