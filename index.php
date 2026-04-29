<!DOCTYPE html>
<html>
<head>
    <title>Barangay Health Center HMS</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #e3f2fd, #f8fbff);
        }

        .container {
            background: white;
            padding: 50px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            width: 400px;
        }

        .logo {
            font-size: 50px;
            margin-bottom: 10px;
        }

        h1 {
            margin: 10px 0;
            font-size: 22px;
            color: #2c3e50;
        }

        p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #2980b9;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
            font-weight: bold;
        }

        .btn:hover {
            background: #1f6fa5;
            transform: translateY(-2px);
        }
    </style>

</head>
<body>

    <div class="container">
        <div class="logo">🏥</div>

        <h1>Barangay Health Center HMS</h1>
        <p>Health Management System</p>

        <a class="btn" href="/presentation/login.php">
            Get Started
        </a>
    </div>

</body>
</html>
