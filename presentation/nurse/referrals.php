<?php
include '../../application/includes/session_check.php';
include '../../application/config/db.php';

// DEFAULT FILTER
$filter = $_GET['filter'] ?? 'Pending';

if ($filter == 'Completed') {
    $stmt = $conn->prepare("
        SELECT r.*, p.first_name, p.last_name
        FROM referrals r
        JOIN patients p ON r.patient_id = p.id
        WHERE r.status IN ('Completed', 'Done', 'reviewed')
        ORDER BY r.created_at DESC
    ");
} elseif ($filter == 'In Progress') {
    $stmt = $conn->prepare("
        SELECT r.*, p.first_name, p.last_name
        FROM referrals r
        JOIN patients p ON r.patient_id = p.id
        WHERE r.status = 'In Progress'
        ORDER BY r.created_at DESC
    ");
} else {
    $stmt = $conn->prepare("
        SELECT r.*, p.first_name, p.last_name
        FROM referrals r
        JOIN patients p ON r.patient_id = p.id
        WHERE r.status = 'Pending'
        ORDER BY r.created_at DESC
    ");
}

$stmt->execute();
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Referrals</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: Poppins, sans-serif;
    display: flex;
    min-height: 100vh;
    background: #f4f7fb;
}

/* ── SIDEBAR ── */
.sidebar {
    width: 230px;
    background: linear-gradient(180deg, #4facfe 0%, #00c9a7 100%);
    display: flex;
    flex-direction: column;
    padding: 30px 20px;
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
}

.sidebar .brand {
    color: white;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 35px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    text-decoration: none;
    padding: 11px 15px;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 6px;
    transition: background 0.2s;
}

.sidebar a:hover,
.sidebar a.active {
    background: rgba(255,255,255,0.25);
}

.sidebar .logout-btn {
    margin-top: auto;
    background: rgba(255,255,255,0.15);
}

.sidebar .logout-btn:hover {
    background: rgba(255,255,255,0.3);
}

/* ── MAIN ── */
.main {
    margin-left: 230px;
    padding: 35px 30px;
    width: 100%;
}

.top-bar {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.top-bar h2 {
    font-size: 22px;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
}

.btn-back {
    background: white;
    border: 2px solid #4facfe;
    color: #4facfe;
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #4facfe;
    color: white;
}

/* ── FILTER TABS ── */
.filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-tabs a {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    background: white;
    color: #666;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    transition: all 0.2s;
}

.filter-tabs a:hover {
    background: #e8f4ff;
    color: #4facfe;
}

.filter-tabs a.active {
    background: linear-gradient(135deg, #4facfe, #00c9a7);
    color: white;
}

/* ── TABLE CARD ── */
.table-card {
    background: white;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.07);
}

.table thead th {
    background: #f8faff;
    color: #555;
    font-size: 13px;
    font-weight: 600;
    border: none;
    padding: 12px 15px;
}

.table tbody td {
    font-size: 13px;
    padding: 12px 15px;
    vertical-align: middle;
    border-color: #f0f0f0;
}

.table tbody tr:hover {
    background: #f8faff;
}

.badge-status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.status-pending     { background: #f39c12; }
.status-in-progress { background: #3498db; }
.status-completed   { background: #2ecc71; }
.status-done        { background: #2ecc71; }
.status-reviewed    { background: #9b59b6; }

.btn-open {
    background: linear-gradient(135deg, #4facfe, #00c9a7);
    color: white;
    border: none;
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: opacity 0.2s;
}

.btn-open:hover {
    opacity: 0.85;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #aaa;
    font-size: 15px;
}

.empty-state .icon {
    font-size: 48px;
    margin-bottom: 12px;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="brand">🩺 Nurse Panel</div>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="referrals.php?filter=Pending"      class="<?= $filter == 'Pending'     ? 'active' : '' ?>">📋 Pending Referrals</a>
    <a href="referrals.php?filter=In Progress"  class="<?= $filter == 'In Progress' ? 'active' : '' ?>">🔄 In Progress</a>
    <a href="referrals.php?filter=Completed"    class="<?= $filter == 'Completed'   ? 'active' : '' ?>">✅ Completed</a>

    <a href="../logout.php" class="logout-btn">🚪 Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <!-- TOP BAR -->
    <div class="top-bar">
        <a href="dashboard.php" class="btn-back">← Back</a>
        <h2>
            <?php
                if ($filter == 'Pending')     echo '📋';
                elseif ($filter == 'In Progress') echo '🔄';
                else echo '✅';
            ?>
            <?= htmlspecialchars($filter) ?> Referrals
        </h2>
    </div>

    <!-- FILTER TABS -->
    <div class="filter-tabs">
        <a href="referrals.php?filter=Pending"     class="<?= $filter == 'Pending'     ? 'active' : '' ?>">📋 Pending</a>
        <a href="referrals.php?filter=In Progress" class="<?= $filter == 'In Progress' ? 'active' : '' ?>">🔄 In Progress</a>
        <a href="referrals.php?filter=Completed"   class="<?= $filter == 'Completed'   ? 'active' : '' ?>">✅ Completed</a>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <?php if (count($referrals) > 0): ?>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($referrals as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><b><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></b></td>
                    <td><?= htmlspecialchars($r['purpose'] ?? $r['reason'] ?? 'N/A') ?></td>
                    <td>
                        <?php
                            $s = strtolower($r['status']);
                            $cls = match($s) {
                                'pending'     => 'status-pending',
                                'in progress' => 'status-in-progress',
                                'completed'   => 'status-completed',
                                'done'        => 'status-done',
                                'reviewed'    => 'status-reviewed',
                                default       => 'status-pending'
                            };
                        ?>
                        <span class="badge-status <?= $cls ?>">
                            <?= htmlspecialchars($r['status']) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                    <td>
                        <a class="btn-open" href="view_referral.php?id=<?= $r['id'] ?>">Open →</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <div class="icon">📭</div>
            No <?= htmlspecialchars($filter) ?> referrals found.
        </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>