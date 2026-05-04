<?php
session_start();
include __DIR__ . '/db.php';

// Guard — must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$errors  = [];

// Role-based redirect destination
$redirectMap = [
    'nurse' => 'nurse_dashboard.php',
    'bhw'   => 'bhw_dashboard.php',
    'admin' => 'admin_dashboard.php',
];
$redirectTo = $redirectMap[$role] ?? 'login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password']     ?? '';
    $confirm      = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($new_password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Save new password + mark as no longer first login
        $stmt = $conn->prepare("
            UPDATE users 
            SET password = ?, must_change_password = 0 
            WHERE id = ?
        ");
        $stmt->execute([$hashed, $user_id]);

        // Redirect to their dashboard
        header("Location: $redirectTo");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 18px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }

        .icon-wrap {
            text-align: center;
            margin-bottom: 20px;
        }
        .icon-wrap .icon {
            font-size: 48px;
            display: block;
        }

        h2 {
            text-align: center;
            color: #1e3c72;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #888;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .alert-error {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #fed7d7;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 6px;
        }

        .input-wrap { position: relative; }
        .input-wrap input {
            width: 100%;
            padding: 12px 42px 12px 14px;
            border: 1px solid #dde2ee;
            border-radius: 9px;
            font-size: 13px;
            color: #333;
            outline: none;
            transition: border-color 0.2s;
        }
        .input-wrap input:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42,82,152,0.1);
        }
        .eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #aaa;
        }

        /* Strength bar */
        .strength-bar {
            height: 4px;
            background: #eee;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: width 0.3s, background 0.3s;
        }
        .strength-text {
            font-size: 11px;
            color: #999;
            margin-top: 3px;
            min-height: 14px;
        }

        /* Match indicator */
        .match-text {
            font-size: 11px;
            margin-top: 4px;
            min-height: 14px;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .notice {
            text-align: center;
            font-size: 11px;
            color: #bbb;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon-wrap">
        <span class="icon">🔐</span>
    </div>

    <h2>Set Your New Password</h2>
    <p class="subtitle">
        Welcome! Since this is your first time logging in,<br>
        please set a new password to continue.
    </p>

    <?php if (!empty($errors)): ?>
        <div class="alert-error">
            ❌ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="changeForm">

        <div class="form-group">
            <label>New Password</label>
            <div class="input-wrap">
                <input type="password" name="new_password" id="new_password"
                       placeholder="Min. 6 characters" required
                       oninput="checkStrength(this.value); checkMatch()">
                <button type="button" class="eye-btn" onclick="toggleVis('new_password', this)">👁️</button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
            <div class="strength-text" id="strength-text"></div>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <div class="input-wrap">
                <input type="password" name="confirm_password" id="confirm_password"
                       placeholder="Re-enter new password" required
                       oninput="checkMatch()">
                <button type="button" class="eye-btn" onclick="toggleVis('confirm_password', this)">👁️</button>
            </div>
            <div class="match-text" id="match-text"></div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            ✅ Save Password & Continue
        </button>
    </form>

    <p class="notice">You will be redirected to your dashboard after saving.</p>
</div>

<script>
function toggleVis(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁️' : '🙈';
}

function checkStrength(val) {
    const fill = document.getElementById('strength-fill');
    const text = document.getElementById('strength-text');
    let score = 0;
    if (val.length >= 6)            score++;
    if (val.length >= 10)           score++;
    if (/[A-Z]/.test(val))          score++;
    if (/[0-9]/.test(val))          score++;
    if (/[^A-Za-z0-9]/.test(val))   score++;

    const levels = [
        { w: '0%',   c: '#eee',    t: '' },
        { w: '25%',  c: '#e53e3e', t: 'Weak' },
        { w: '50%',  c: '#dd6b20', t: 'Fair' },
        { w: '75%',  c: '#d69e2e', t: 'Good' },
        { w: '100%', c: '#38a169', t: 'Strong ✓' },
    ];
    const lvl = Math.min(score, 4);
    fill.style.width      = levels[lvl].w;
    fill.style.background = levels[lvl].c;
    text.textContent      = levels[lvl].t;
    text.style.color      = levels[lvl].c;
}

function checkMatch() {
    const pw  = document.getElementById('new_password').value;
    const cfm = document.getElementById('confirm_password').value;
    const txt = document.getElementById('match-text');
    const btn = document.getElementById('submitBtn');

    if (cfm === '') {
        txt.textContent = '';
        return;
    }

    if (pw === cfm) {
        txt.textContent = '✅ Passwords match';
        txt.style.color = '#38a169';
        btn.disabled = false;
    } else {
        txt.textContent = '❌ Passwords do not match';
        txt.style.color = '#e53e3e';
        btn.disabled = true;
    }
}
</script>

</body>
</html>