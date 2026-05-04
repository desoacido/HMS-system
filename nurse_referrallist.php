<?php
include 'db.php';

// 1. Kunin ang status filter mula sa URL (?status=pending)
// Default ay 'all' kung wala itong laman
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    // 2. Dynamic SQL Query base sa filter
    if ($status_filter !== 'all') {
        // Ipakita lang ang piniling status (e.g. pending, viewed, or completed)
        $stmt = $conn->prepare("
            SELECT r.*, p.firstname, p.lastname, v.category
            FROM referrals r
            JOIN patients p ON p.id = r.patient_id
            JOIN visits v ON v.id = r.visit_id
            WHERE r.status = :status
            ORDER BY r.created_at DESC
        ");
        $stmt->execute(['status' => $status_filter]);
    } else {
        // Default: Ipakita lahat ng referrals
        $stmt = $conn->prepare("
            SELECT r.*, p.firstname, p.lastname, v.category
            FROM referrals r
            JOIN patients p ON p.id = r.patient_id
            JOIN visits v ON v.id = r.visit_id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
    }
    
    $refs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Referral List</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fb;
            padding: 20px;
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        }

        .row {
            display: grid;
            /* In-adjust ko ang grid columns para mas pantay ang itsura */
            grid-template-columns: 2fr 1fr 1fr 1fr;
            padding: 15px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .header-row {
            background: #2a5298;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
        }

        .name { font-weight: 600; color: #2c3e50; }

        .status {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            background: #eee;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }

        /* Kulay base sa status */
        .status-pending { background: #ffeaa7; color: #d6a316; }
        .status-viewed { background: #fab1a0; color: #e17055; }
        .status-completed { background: #55efc4; color: #00b894; }

        .view-btn {
            text-decoration: none;
            background: #2a5298;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            transition: background 0.2s;
        }

        .view-btn:hover { background: #1e3c72; }

        .empty-state { padding: 40px; text-align: center; color: #888; }
    </style>
</head>
<body>

<div class="header-container">
    <h2>📩 Referral List 
        <small style="font-size: 14px; color: #7f8c8d;">
            <?php echo ($status_filter !== 'all') ? "(".ucfirst($status_filter).")" : "(All Records)"; ?>
        </small>
    </h2>
    <?php if($status_filter !== 'all'): ?>
        <a href="nurse_referrallist.php" style="font-size: 12px; color: #2a5298;">Show All</a>
    <?php endif; ?>
</div>

<div class="table">
    <div class="row header-row">
        <div>Patient Name</div>
        <div>Category</div>
        <div>Status</div>
        <div style="text-align: center;">Action</div>
    </div>

    <?php if (count($refs) > 0): ?>
        <?php foreach($refs as $r): ?>
            <div class="row">
                <div class="name">
                    <?= htmlspecialchars($r['firstname'].' '.$r['lastname']) ?>
                </div>

                <div>
                    <?= htmlspecialchars($r['category']) ?>
                </div>

                <div>
                    <!-- Nilagyan ko ng dynamic class para may kulay ang status -->
                    <span class="status status-<?= $r['status'] ?>">
                        <?= htmlspecialchars(str_replace('_', ' ', $r['status'])) ?>
                    </span>
                </div>

                <div style="text-align: center;">
                   <a class="view-btn" href="nurse_viewReferral.php?id=<?= $r['id'] ?? $r['referral_id'] ?>">
    VIEW
</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <p>No <?php echo $status_filter; ?> referrals found.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>