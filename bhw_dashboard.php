<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bhw') {
    header("Location: /HMS-2/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BHW Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { display:flex; height:100vh; overflow:hidden; background:#f4f7fb; }

    /* SIDEBAR */
    .sidebar {
        width: 240px;
        background: linear-gradient(180deg, #1a7a4a, #28a745);
        color: white;
        display: flex;
        flex-direction: column;
        padding: 20px 15px;
        flex-shrink: 0;
        transition: width 0.3s ease;
        overflow: hidden;
    }

    .sidebar.collapsed { width: 65px; }

    .sidebar-top {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
    }

    .hamburger {
        background: none;
        border: none;
        color: white;
        font-size: 22px;
        cursor: pointer;
        flex-shrink: 0;
        padding: 4px;
    }

    .sidebar-title {
        font-size: 16px;
        font-weight: 600;
        white-space: nowrap;
        transition: opacity 0.2s;
    }

    .sidebar.collapsed .sidebar-title { opacity:0; pointer-events:none; }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
        text-decoration: none;
        padding: 12px 10px;
        margin-bottom: 6px;
        border-radius: 8px;
        font-size: 14px;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .sidebar a:hover, .sidebar a.active {
        background: rgba(255,255,255,0.2);
    }

    .sidebar a .icon { font-size:18px; flex-shrink:0; width:24px; text-align:center; }

    .sidebar a .label { transition: opacity 0.2s; }
    .sidebar.collapsed a .label { opacity:0; pointer-events:none; }

    /* TOOLTIP */
    .sidebar.collapsed a { position:relative; }
    .sidebar.collapsed a:hover::after {
        content: attr(data-label);
        position: absolute;
        left: 60px;
        background: #1a7a4a;
        color: white;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 13px;
        white-space: nowrap;
        z-index: 999;
    }

    .sidebar .logout { margin-top:auto; background:rgba(255,80,80,0.2); }

    /* MAIN */
    .main { flex:1; display:flex; flex-direction:column; overflow:hidden; }

    .topbar {
        background: white;
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .topbar h1 { font-size:18px; color:#333; }
    .topbar small { color:#888; font-size:13px; }

    iframe { flex:1; border:none; width:100%; height:100%; background:#f4f7fb; }
</style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <button class="hamburger" onclick="toggleSidebar()">☰</button>
        <span class="sidebar-title">🏥 BHW Portal</span>
    </div>

    <a href="bhw_home.php" target="content" class="active" data-label="Dashboard">
        <span class="icon">🏠</span>
        <span class="label">Dashboard</span>
    </a>

    <a href="bhw_addpatient.php" target="content" data-label="Add Patient">
        <span class="icon">➕</span>
        <span class="label">Add Patient</span>
    </a>

    <a href="bhw_patientlist.php" target="content" data-label="Patient List">
        <span class="icon">📋</span>
        <span class="label">Patient List</span>
    </a>

    
    <a href="bhw_scanqr.php" target="content" data-label="Scan QR">
        <span class="icon">📷</span>
        <span class="label">Scan QR</span>
    </a>

    <a href="logout.php" class="logout" data-label="Logout">
        <span class="icon">🚪</span>
        <span class="label">Logout</span>
    </a>
</div>

<div class="main">
    <div class="topbar">
        <h1>BHW Dashboard</h1>
        <small>Welcome, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'BHW'); ?> 👋</small>
    </div>
    <iframe name="content" src="bhw_home.php"></iframe>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}
document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.sidebar a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
</body>
</html>