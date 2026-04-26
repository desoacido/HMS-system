<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HMS Login</title>

<!-- Google Font -->
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
        background: linear-gradient(135deg, #4facfe, #00f2fe);
    }

    .container {
        display: flex;
        width: 850px;
        height: 500px;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }

    .left {
        flex: 1;
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 30px;
        text-align: center;
    }

    .left h1 {
        font-size: 28px;
        margin-bottom: 10px;
    }

    .left p {
        font-size: 14px;
        opacity: 0.9;
    }

    .right {
        flex: 1;
        padding: 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .right h2 {
        margin-bottom: 20px;
        color: #333;
    }

    .input-group {
        margin-bottom: 15px;
    }

    .input-group label {
        font-size: 13px;
        color: #555;
    }

    .input-group input {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 8px;
        border: 1px solid #ccc;
        outline: none;
        transition: 0.3s;
    }

    .input-group input:focus {
        border-color: #4facfe;
        box-shadow: 0 0 5px rgba(79,172,254,0.5);
    }

    .btn {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }

    .error {
        background: #ffe6e6;
        color: #d8000c;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
            width: 90%;
            height: auto;
        }

        .left {
            display: none;
        }
    }
</style>
</head>

<body>

<?php session_start(); ?>

<div class="container">

    <!-- LEFT SIDE -->
    <div class="left">
        <h1>Barangay HMS</h1>
        <p>Manage patient records, referrals, and health services efficiently.</p>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right">

        <h2>Login</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/hms2/application/controllers/login_process.php" method="POST">

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn" type="submit">Login</button>

        </form>

    </div>

</div>

</body>
</html>
