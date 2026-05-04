<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hospital Management System</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        height: 100vh;
        display: flex;
        flex-direction: column;
        color: white;
    }

    /* NAVBAR */
    .navbar {
        display: flex;
        justify-content: space-between;
        padding: 20px 60px;
        align-items: center;
    }

    .navbar h2 {
        font-weight: 600;
    }

    .navbar a {
        color: white;
        text-decoration: none;
        background: rgba(255,255,255,0.2);
        padding: 10px 20px;
        border-radius: 8px;
        transition: 0.3s;
    }

    .navbar a:hover {
        background: rgba(255,255,255,0.4);
    }

    /* HERO */
    .hero {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 60px;
    }

    .hero-text {
        max-width: 500px;
    }

    .hero-text h1 {
        font-size: 42px;
        margin-bottom: 20px;
    }

    .hero-text p {
        font-size: 18px;
        opacity: 0.9;
        margin-bottom: 30px;
    }

    .hero-text a {
        background: white;
        color: #2a5298;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }

    .hero-text a:hover {
        background: #ddd;
    }

    /* CARD SECTION */
    .features {
        display: flex;
        gap: 20px;
        padding: 40px 60px;
        background: white;
        color: #333;
    }

    .feature {
        flex: 1;
        background: #f4f6f9;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }

    .feature h3 {
        margin-bottom: 10px;
    }

</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <h2>🏥 HMS</h2>
    
</div>

<!-- HERO SECTION -->
<div class="hero">
    <div class="hero-text">
        <h1>Hospital Management System</h1>
        <p>
            Manage patients and healthcare records efficiently.
            Designed for Admin, BHW, and Nurses.
        </p>
        <a href="login.php">Get Started</a>
    </div>
</div>

<!-- FEATURES -->
<div class="features">
    <div class="feature">
        <h3>👩‍⚕️ Patient Management</h3>
        <p>Organize and track patient records بسهولة.</p>
    </div>

    <div class="feature">
        <h3>📅 Appointments</h3>
        <p>Schedule and manage visits quickly.</p>
    </div>

    <div class="feature">
        <h3>🔐 Secure Access</h3>
        <p>Role-based login for Admin, BHW, and Nurse.</p>
    </div>
</div>

</body>
</html>
