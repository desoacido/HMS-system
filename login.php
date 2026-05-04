<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HMS Login</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
    }

    /* MAIN CARD */
    .container {
        width: 900px;
        height: 520px;
        display: flex;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(12px);
    }

    /* LEFT PANEL */
    .left {
        flex: 1;
        background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 50px;
        color: white;
    }

    .left h1 {
        font-size: 34px;
        margin-bottom: 15px;
    }

    .left p {
        font-size: 15px;
        opacity: 0.9;
        line-height: 1.6;
    }

    /* RIGHT PANEL */
    .right {
        flex: 1;
        background: white;
        padding: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .right h2 {
        margin-bottom: 25px;
        color: #2a5298;
        font-size: 26px;
    }

    .input-group {
        margin-bottom: 18px;
    }

    .input-group label {
        font-size: 13px;
        color: #555;
    }

    .input-group input {
        width: 100%;
        padding: 12px;
        margin-top: 6px;
        border-radius: 8px;
        border: 1px solid #ddd;
        outline: none;
        transition: 0.3s;
    }

    .input-group input:focus {
        border-color: #2a5298;
        box-shadow: 0 0 6px rgba(42,82,152,0.3);
    }

    .btn {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
    }

    .btn:hover {
        transform: translateY(-2px);
        opacity: 0.95;
    }

    .error {
        background: #ffe6e6;
        color: #d8000c;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 13px;
    }

    /* MOBILE */
    @media (max-width: 768px) {
        .container {
            flex-direction: column;
            width: 90%;
            height: auto;
        }

        .left {
            display: none;
        }

        .right {
            border-radius: 18px;
        }
    }
</style>
</head>

<body>

<div class="container">

    <!-- LEFT SIDE -->
    <div class="left">
        <h1>🏥 HMS Portal</h1>
        <p>
            Secure access for Admin, BHW, and Nurses.<br>
            Manage patient records, referrals, and healthcare operations efficiently.
        </p>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right">

        <h2>Login to Your Account</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn" type="submit">Sign In</button>

        </form>

    </div>

</div>

</body>
</html>