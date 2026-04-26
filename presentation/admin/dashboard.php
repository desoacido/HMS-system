<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        display: flex;
        min-height: 100vh;
        background: #f4f7fb;
    }

    /* SIDEBAR */
    .sidebar {
        width: 250px;
        background: linear-gradient(180deg, #4facfe, #00f2fe);
        color: white;
        padding: 20px;
    }

    .sidebar h2 {
        margin-bottom: 30px;
        text-align: center;
    }

    .sidebar a {
        display: block;
        color: white;
        text-decoration: none;
        padding: 12px;
        margin-bottom: 10px;
        border-radius: 8px;
        transition: 0.3s;
    }

    .sidebar a:hover {
        background: rgba(255,255,255,0.2);
    }

    .logout {
        margin-top: 20px;
        background: rgba(255,0,0,0.2);
    }

    /* MAIN */
    .main {
        flex: 1;
        padding: 30px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header h1 {
        color: #333;
    }

    .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        transition: 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card h3 {
        margin-bottom: 10px;
        color: #555;
    }

    .card p {
        font-size: 14px;
        color: #888;
    }

</style>
</head>

<body>

<div class="sidebar">
    <h2>HMS Admin</h2>

    <a href="users.php">👤 Manage Users</a>
    <a href="patients.php">🧾 Patient List</a>

    <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">

    <div class="header">
        <h1>Welcome Admin</h1>
        <div>
            <small>Welcome, <?php echo $_SESSION['name'] ?? 'Admin'; ?></small>
        </div>
    </div>

    <div class="card-container">

        <div class="card">
            <h3>Manage Users</h3>
            <p>Add, edit, and manage system users.</p>
        </div>

        <div class="card">
            <h3>Patient Records</h3>
            <p>View and manage patient information.</p>
        </div>

        <div class="card">
            <h3>Reports</h3>
            <p>Generate health reports and statistics.</p>
        </div>

    </div>

</div>

</body>
</html>
