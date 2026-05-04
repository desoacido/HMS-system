<?php
session_start();
include __DIR__ . '/db.php';

/* 🔒 AUTH CHECK */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'nurse') {
    header("Location: login.php");
    exit();
}

/* 📊 FETCH HISTORY (COMPLETED + VIEWED ONLY) */
$stmt = $conn->prepare("
    SELECT 
        r.id,
        r.source_type AS category,
        r.created_at AS referral_date,
        r.ml_result,
        r.status,
        p.firstname,
        p.lastname,
        u.fullname AS bhw_name
    FROM referrals r
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON r.referred_by = u.id
    WHERE r.status IN ('completed','viewed')
    ORDER BY r.created_at DESC
");

$stmt->execute();
$result = $stmt->get_result();

/* ✅ SAFE ARRAY (kahit walang data, hindi error) */
$history = [];
if ($result) {
    $history = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Records</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fb; padding: 20px; }
        .header { margin-bottom: 20px; }

        .record-card { 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
            margin-bottom: 15px;
            border-left: 5px solid #00b894;
        }

        .record-header { display: flex; justify-content: space-between; align-items: center; }
        .patient-name { font-size: 18px; font-weight: 600; color: #2a5298; }
        .date { font-size: 12px; color: #888; }

        .category-tag { 
            background: #e1f5fe; color: #039be5; padding: 4px 10px; 
            border-radius: 5px; font-size: 12px; font-weight: 600; 
            margin-left: 10px;
        }

        .ml-summary { 
            background: #f9f9f9; padding: 10px; border-radius: 6px; 
            margin-top: 10px; font-size: 13px; color: #555; 
        }

        .view-btn {
            display: inline-block; margin-top: 10px; text-decoration: none; 
            color: #2a5298; font-size: 13px; font-weight: 600;
        }

        .empty { text-align: center; padding: 50px; color: #888; }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .completed { background: #d4edda; color: #155724; }
        .viewed { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>

<div class="header">
    <h2>📂 Patient Records</h2>
    <p style="color: #666; font-size: 14px;">
        List of all completed and reviewed referrals with ML evaluation.
    </p>
</div>

<?php if (!empty($history)): ?>
    
    <?php foreach($history as $row): ?>
        <div class="record-card">
            
            <div class="record-header">
                <div>
                    <span class="patient-name">
                        <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>
                    </span>

                    <span class="category-tag">
                        <?= strtoupper($row['category']) ?>
                    </span>
                </div>

                <div>
                    <span class="date">
                        <?= date('M d, Y | h:i A', strtotime($row['referral_date'])) ?>
                    </span>
                    <br>
                    <span class="badge <?= strtolower($row['status']) ?>">
                        <?= $row['status'] ?>
                    </span>
                </div>
            </div>

            <div class="ml-summary">
                <strong>ML Recommendation:</strong><br>
                <?= !empty($row['ml_result']) 
                    ? substr(htmlspecialchars($row['ml_result']), 0, 150) . '...' 
                    : 'No ML result available.' ?>
            </div>

            <div style="margin-top:8px; font-size:12px; color:#777;">
                Referred by: <?= htmlspecialchars($row['bhw_name']) ?>
            </div>

            <a href="nurse_viewReferral.php?id=<?= $row['id'] ?>" class="view-btn">
                View Full Details →
            </a>

        </div>
    <?php endforeach; ?>

<?php else: ?>

    <div class="record-card empty">
        <p>No completed records found.</p>
    </div>

<?php endif; ?>

</body>
</html>
