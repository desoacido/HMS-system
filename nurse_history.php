<?php
include 'db.php';

try {
    // Kukunin lang natin ang mga 'completed' na referrals
    $stmt = $conn->prepare("
        SELECT r.*, p.firstname, p.lastname, v.category, r.ml_result, r.created_at as referral_date
        FROM referrals r
        JOIN patients p ON p.id = r.patient_id
        JOIN visits v ON v.id = r.visit_id
        WHERE r.status = 'completed'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
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
            border-left: 5px solid #00b894; /* Green border para sa Completed */
        }
        .record-header { display: flex; justify-content: space-between; align-items: center; }
        .patient-name { font-size: 18px; font-weight: 600; color: #2a5298; }
        .date { font-size: 12px; color: #888; }
        .category-tag { 
            background: #e1f5fe; color: #039be5; padding: 4px 10px; 
            border-radius: 5px; font-size: 12px; font-weight: 600; 
        }
        .ml-summary { 
            background: #f9f9f9; padding: 10px; border-radius: 6px; 
            margin-top: 10px; font-size: 13px; color: #555; 
            max-height: 80px; overflow: hidden; text-overflow: ellipsis;
        }
        .view-btn {
            display: inline-block; margin-top: 10px; text-decoration: none; 
            color: #2a5298; font-size: 13px; font-weight: 600;
        }
        .empty { text-align: center; padding: 50px; color: #888; }
    </style>
</head>
<body>

    <div class="header">
        <h2>📂 Patient Records</h2>
        <p style="color: #666; font-size: 14px;">List of all completed referrals and medical evaluations.</p>
    </div>

    <?php if (count($records) > 0): ?>
        <?php foreach($records as $row): ?>
            <div class="record-card">
                <div class="record-header">
                    <div>
                        <span class="patient-name"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></span>
                        <span class="category-tag"><?= strtoupper($row['category']) ?></span>
                    </div>
                    <span class="date"><?= date('M d, Y | h:i A', strtotime($row['referral_date'])) ?></span>
                </div>

                <div class="ml-summary">
                    <strong>ML Recommendation Preview:</strong><br>
                    <?= substr(htmlspecialchars($row['ml_result']), 0, 150) ?>...
                </div>

                <a href="nurse_viewReferral.php?id=<?= $row['id'] ?>" class="view-btn">View Full Details →</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="record-card empty">
            <p>No completed records found.</p>
        </div>
    <?php endif; ?>

</body>
</html>