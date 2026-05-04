<?php
session_start();
include __DIR__ . '/db.php';

// ADD USER
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $role     = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // MySQLi prepare & bind
    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, role, status, must_change_password) VALUES (?, ?, ?, ?, 'active', 1)");
    $stmt->bind_param("ssss", $username, $password, $fullname, $role);
    $stmt->execute();
    header("Location: users.php");
    exit();
}

// EDIT USER
if (isset($_POST['edit_user'])) {
    $id       = $_POST['id'];
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $role     = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET username=?, fullname=?, role=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $fullname, $role, $password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, fullname=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $fullname, $role, $id);
    }
    $stmt->execute();
    header("Location: users.php");
    exit();
}

// TOGGLE STATUS
if (isset($_GET['toggle'])) {
    $id   = $_GET['toggle'];
    // MySQLi fetch for status
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $newStatus = ($user['status'] == 'active') ? 'inactive' : 'active';
        $updateStmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newStatus, $id);
        $updateStmt->execute();
    }
    header("Location: users.php");
    exit();
}

// DELETE USER
if (isset($_GET['delete'])) {
    $id   = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: users.php");
    exit();
}

// FETCH ALL USERS (Eto yung Line 60 fix)
$result = $conn->query("SELECT * FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { background:#f4f7fb; padding:30px; }
    h2 { color:#2a5298; margin-bottom:20px; }
    .btn { padding:8px 14px; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-family:'Poppins',sans-serif; }
    .btn-primary { background:#2a5298; color:white; }
    .btn-warning { background:#f0a500; color:white; }
    .btn-success { background:#28a745; color:white; }
    .btn-danger  { background:#dc3545; color:white; }
    table { width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.08); }
    th { background:#2a5298; color:white; padding:12px 15px; text-align:left; font-size:13px; }
    td { padding:12px 15px; font-size:13px; border-bottom:1px solid #eee; }
    .badge { padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .badge-active   { background:#d4edda; color:#155724; }
    .badge-inactive { background:#f8d7da; color:#721c24; }
    .modal-bg { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; z-index:999; }
    .modal-bg.show { display:flex; }
    .modal { background:white; padding:30px; border-radius:14px; width:420px; }
    .form-group { margin-bottom:15px; }
    .form-group label { font-size:13px; color:#555; display:block; margin-bottom:5px; }
    .form-group input, .form-group select { width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:13px; }
    .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
</style>
</head>
<body>

<div class="top-bar">
    <h2>👤 Manage Users</h2>
    <button class="btn btn-primary" onclick="openAddModal()">+ Add User</button>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $i => $u): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['fullname']) ?></td>
            <td><?= ucfirst($u['role']) ?></td>
            <td>
                <span class="badge <?= $u['status'] == 'active' ? 'badge-active' : 'badge-inactive' ?>">
                    <?= ucfirst($u['status']) ?>
                </span>
            </td>
            <td style="display:flex; gap:6px;">
                <button class="btn btn-warning" onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)">✏️ Edit</button>
                <a href="?toggle=<?= $u['id'] ?>" class="btn <?= $u['status'] == 'active' ? 'btn-danger' : 'btn-success' ?>">
                    <?= $u['status'] == 'active' ? '🔒 Deactivate' : '✅ Activate' ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- ADD MODAL -->
<div class="modal-bg" id="addModal">
    <div class="modal">
        <h3>➕ Add New User</h3>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="admin">Admin</option>
                    <option value="bhw">BHW</option>
                    <option value="nurse">Nurse</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button class="btn btn-primary" name="add_user">Save</button>
                <button type="button" class="btn" style="background:#eee;" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-bg" id="editModal">
    <div class="modal">
        <h3>✏️ Edit User</h3>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="edit_username" required>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" id="edit_fullname" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="edit_role">
                    <option value="admin">Admin</option>
                    <option value="bhw">BHW</option>
                    <option value="nurse">Nurse</option>
                </select>
            </div>
            <div class="form-group">
                <label>New Password <small style="color:#aaa;">(leave blank to keep current)</small></label>
                <input type="password" name="password">
            </div>
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button class="btn btn-primary" name="edit_user">Update</button>
                <button type="button" class="btn" style="background:#eee;" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() { document.getElementById('addModal').classList.add('show'); }
function openEditModal(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_fullname').value = user.fullname;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('editModal').classList.add('show');
}
function closeModals() {
    document.getElementById('addModal').classList.remove('show');
    document.getElementById('editModal').classList.remove('show');
}
document.querySelectorAll('.modal-bg').forEach(bg => {
    bg.addEventListener('click', function(e) { if (e.target === this) closeModals(); });
});
</script>

</body>
</html>
