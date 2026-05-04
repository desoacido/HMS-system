<?php include 'db.php'; include 'nurse_dashboard.php'; ?>

<div class="content">

<h2>📊 Dashboard</h2>

<?php
$total = $conn->query("SELECT COUNT(*) FROM referrals")->fetchColumn();
$new = $conn->query("SELECT COUNT(*) FROM referrals WHERE status='new'")->fetchColumn();
$in = $conn->query("SELECT COUNT(*) FROM referrals WHERE status='in_progress'")->fetchColumn();
$done = $conn->query("SELECT COUNT(*) FROM referrals WHERE status='completed'")->fetchColumn();
?>

<div style="display:flex;gap:15px;">
    <div>📩 Total: <?= $total ?></div>
    <div>🆕 New: <?= $new ?></div>
    <div>🔄 Progress: <?= $in ?></div>
    <div>✅ Done: <?= $done ?></div>
</div>

</div>