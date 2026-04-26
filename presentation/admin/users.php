<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

// fetch users
$stmt = $conn->query("SELECT * FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users Management</title>

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

    .btn {
        padding: 10px 15px;
        border: none;
        border-radius: 8px;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
        text-decoration: none;
        font-size: 14px;
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

    .status-active {
        color: green;
        font-weight: 600;
    }

    .status-inactive {
        color: red;
        font-weight: 600;
    }

    .action a {
        text-decoration: none;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 13px;
    }

    .activate {
        background: #d4edda;
        color: #155724;
    }

    .deactivate {
        background: #f8d7da;
        color: #721c24;
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
    <h2>Users List</h2>
    <a href="add_user.php" class="btn">+ Add User</a>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Username</th>
        <th>Role</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user['id'] ?></td>
        <td><?= $user['fullname'] ?></td>
        <td><?= $user['username'] ?></td>
        <td><?= $user['role'] ?></td>

        <td>
            <?php if ($user['status'] == 'active'): ?>
                <span class="status-active">Active</span>
            <?php else: ?>
                <span class="status-inactive">Inactive</span>
            <?php endif; ?>
        </td>

        <td class="action">
            <?php if ($user['status'] == 'active'): ?>
                <a class="deactivate" href="../../application/controllers/toggle_user.php?id=<?= $user['id'] ?>&status=inactive">
                    Deactivate
                </a>
            <?php else: ?>
                <a class="activate" href="../../application/controllers/toggle_user.php?id=<?= $user['id'] ?>&status=active">
                    Activate
                </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>

</table>

<a href="../admin/dashboard.php" class="back">⬅ Back to Dashboard</a>

</body>
</html>
