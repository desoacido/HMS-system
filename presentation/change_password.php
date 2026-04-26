<?php
session_start();
include __DIR__ . '/../application/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../presentation/login.php");
    exit();
}

$error = "";
$success = "";

if (isset($_POST['change_password'])) {

    $user_id          = $_SESSION['user_id'];
    $old_password     = $_POST['old_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // KUNIN ANG USER
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // CHECK OLD PASSWORD
    if (!password_verify($old_password, $user['password'])) {
        $error = "Mali ang current password!";

    // PASSWORD RESTRICTIONS
    } elseif (strlen($new_password) < 8) {
        $error = "Minimum 8 characters ang password!";

    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = "Kailangan may uppercase letter! (A-Z)";

    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error = "Kailangan may lowercase letter! (a-z)";

    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = "Kailangan may number! (0-9)";

    } elseif (!preg_match('/[\W_]/', $new_password)) {
        $error = "Kailangan may special character! (!@#$%^&*)";

    // CHECK KUNG MATCH
    } elseif ($new_password !== $confirm_password) {
        $error = "Hindi match ang new password!";

    // CHECK KUNG SAME SA DATI
    } elseif (password_verify($new_password, $user['password'])) {
        $error = "Same pa rin sa dati ang password!";

    } else {
        // I-SAVE NA
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("
            UPDATE users 
            SET password = :password, must_change_password = 0 
            WHERE id = :id
        ");
        $update->execute([':password' => $hashed, ':id' => $user_id]);

        // REDIRECT
        $role = $_SESSION['role'];
        if ($role == 'admin') {
            header("Location: /hms2/presentation/admin/dashboard.php");
        } elseif ($role == 'bhw') {
            header("Location: /hms2/presentation/bhw/dashboard.php");
        } else {
            header("Location: /hms2/presentation/nurse/dashboard.php");
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 450px;
            margin: 60px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        h2 { text-align: center; color: #333; }
        label { font-weight: bold; }

        /* PASSWORD FIELD WITH EYE */
        .password-wrapper {
            position: relative;
            margin: 6px 0 16px 0;
        }
        .password-wrapper input {
            width: 100%;
            padding: 8px 40px 8px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .toggle-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover { background-color: #45a049; }
        .error   { color: red;   background: #ffe6e6; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        #strength-msg { font-size: 13px; margin-bottom: 10px; }
        #match-msg    { font-size: 13px; margin-bottom: 10px; }
        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #333;
            text-decoration: none;
        }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h2>🔐 Change Password</h2>
<p style="text-align:center; color:#666;">Para sa iyong seguridad, palitan ang iyong password.</p>

<?php if ($error): ?>
    <div class="error">❌ <?= $error ?></div>
<?php endif; ?>

<form method="POST">

    <label>Current Password:</label>
    <div class="password-wrapper">
        <input type="password" name="old_password" id="old_password" required>
        <span class="toggle-eye" onclick="toggleEye('old_password', this)"></span>
    </div>

    <label>New Password:</label>
    <div class="password-wrapper">
        <input type="password" name="new_password" id="new_password" required>
        <span class="toggle-eye" onclick="toggleEye('new_password', this)"></span>
    </div>
    <div id="strength-msg"></div>

    <label>Confirm New Password:</label>
    <div class="password-wrapper">
        <input type="password" name="confirm_password" id="confirm_password" required>
        <span class="toggle-eye" onclick="toggleEye('confirm_password', this)"></span>
    </div>
    <div id="match-msg"></div>

    <button type="submit" name="change_password">Change Password</button>

</form>

<a href="javascript:history.back()">⬅ Back</a>

<script>
    // SHOW / HIDE PASSWORD
    function toggleEye(fieldId, icon) {
        const input = document.getElementById(fieldId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = '🙈';
        } else {
            input.type = 'password';
            icon.textContent = '👁️';
        }
    }

    // REAL-TIME PASSWORD STRENGTH
    document.getElementById('new_password').addEventListener('input', function () {
        const val = this.value;
        const msg = document.getElementById('strength-msg');
        let issues = [];

        if (val.length < 8)         issues.push("• Minimum 8 characters");
        if (!/[A-Z]/.test(val))     issues.push("• May uppercase letter (A-Z)");
        if (!/[a-z]/.test(val))     issues.push("• May lowercase letter (a-z)");
        if (!/[0-9]/.test(val))     issues.push("• May number (0-9)");
        if (!/[\W_]/.test(val))     issues.push("• May special character (!@#$%)");

        if (issues.length === 0) {
            msg.style.color = "green";
            msg.innerHTML = "✅ Strong password!";
        } else {
            msg.style.color = "red";
            msg.innerHTML = issues.join("<br>");
        }
    });

    // REAL-TIME CONFIRM MATCH
    document.getElementById('confirm_password').addEventListener('input', function () {
        const newPass     = document.getElementById('new_password').value;
        const confirmPass = this.value;
        const msg         = document.getElementById('match-msg');

        if (confirmPass === "") {
            msg.innerHTML = "";
        } else if (newPass === confirmPass) {
            msg.style.color = "green";
            msg.innerHTML = "✅ Match!";
        } else {
            msg.style.color = "red";
            msg.innerHTML = "❌ Hindi match!";
        }
    });
</script>

</body>
</html>