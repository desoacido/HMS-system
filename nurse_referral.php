<?php
include 'db.php';

$referrals = $conn->query("
SELECT r.*, p.firstname, p.lastname, v.category
FROM referrals r
JOIN patients p ON p.id = r.patient_id
JOIN visits v ON v.id = r.visit_id
ORDER BY r.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Referrals</title>
<style>
body { font-family:Poppins; background:#f4f7fb; padding:20px; }
.card {
    background:white;
    padding:15px;
    margin-bottom:10px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
.status { font-weight:bold; color:#7b1fa2; }
</style>
</head>
<body>

<h2>📩 Referral List</h2>

<?php if (count($referrals) == 0): ?>
    <p>Walang referrals.</p>
<?php endif; ?>

<?php foreach ($referrals as $r): ?>
<div class="card">
    <b><?= $r['firstname'].' '.$r['lastname'] ?></b><br>
    Category: <?= $r['category'] ?><br>
    Status: <span class="status"><?= $r['status'] ?></span><br>

    <a href="open_referral.php?id=<?= $r['id'] ?>">
        Open
    </a>
</div>
<?php endforeach; ?>

</body>
</html>