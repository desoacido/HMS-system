<?php
include '../../application/config/db.php';

$message = "";
$msg_color = "green";

if (isset($_POST['add_user'])) {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if (empty($fullname) || empty($username) || empty($password) || empty($role)) {
        $message   = "All fields are required!";
        $msg_color = "red";
    } else {

        // CHECK DUPLICATE USERNAME
        $check = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $check->execute([':username' => $username]);

        if ($check->rowCount() > 0) {
            $message   = "Username already exists!";
            $msg_color = "red";
        } else {

            // HASH PASSWORD
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // INSERT — must_change_password = 1 para forced mag-change pag first login
            $stmt = $conn->prepare("
                INSERT INTO users (fullname, username, password, role, status, must_change_password)
                VALUES (:fullname, :username, :password, :role, 'active', 1)
            ");

            $stmt->execute([
                ':fullname' => $fullname,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':role'     => $role
            ]);

            $message = "User added successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
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
        input, select {
            width: 100%;
            padding: 8px;
            margin: 6px 0 16px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background-color: #45a049; }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 12px;
            text-align: center;
            font-weight: bold;
        }
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

<h2>➕ Add User</h2>

<?php if ($message): ?>
    <div class="message" style="color: <?= $msg_color ?>; background: <?= $msg_color == 'green' ? '#e6ffe6' : '#ffe6e6' ?>;">
        <?= $msg_color == 'green' ? '✅' : '❌' ?> <?= $message ?>
    </div>
<?php endif; ?>

<form method="POST">

    <label>Full Name:</label>
    <input type="text" name="fullname" placeholder="Juan Dela Cruz" required>

    <label>Username:</label>
    <input type="text" name="username" placeholder="juandelacruz" required>

    <label>Password:</label>
    <input type="password" name="password" placeholder="Temporary password" required>

    <label>Role:</label>
    <select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="admin">Admin</option>
        <option value="bhw">BHW</option>
        <option value="nurse">Nurse</option>
    </select>

    <button type="submit" name="add_user">Add User</button>

</form>

<a href="../admin/dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>