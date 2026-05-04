<?php
include 'db_connect.php'; // Siguraduhing tama ang path ng iyong connection file

// Kunin ang Referral ID mula sa URL (halimbawa: view_referral.php?id=1)
if (!isset($_GET['id'])) {
    die("Walang Referral ID na nahanap.");
}

$referral_id = $_GET['id'];

// SQL Query para makuha ang lahat ng impormasyon gamit ang JOINs
$sql = "SELECT r.*, p.name, p.age, p.address, v.visit_date, v.visit_type 
        FROM referrals r
        JOIN patients p ON r.patient_id = p.id
        JOIN visits v ON r.visit_id = v.id
        WHERE r.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $referral_id);
$stmt->execute();
$result = $stmt->get_result();
$referral = $result->fetch_assoc();

if (!$referral) {
    die("Hindi nahanap ang record.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Referral Details</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .section { margin-bottom: 15px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .label { font-weight: bold; color: #555; }
        .status-badge { padding: 5px 10px; border-radius: 4px; color: white; background: #28a745; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Referral Details #<?php echo $referral['id']; ?></h2>
        <p><strong>Type:</strong> <?php echo strtoupper($referral['visit_type']); ?></p>
    </div>

    <!-- Patient Info -->
    <div class="section">
        <h3>Patient Information</h3>
        <p><span class="label">Name:</span> <?php echo $referral['name']; ?></p>
        <p><span class="label">Age:</span> <?php echo $referral['age']; ?></p>
        <p><span class="label">Address:</span> <?php echo $referral['address']; ?></p>
    </div>

    <!-- Referral Info -->
    <div class="section">
        <h3>Referral Summary</h3>
        <p><span class="label">Reason for Referral:</span> <?php echo $referral['reason']; ?></p>
        <p><span class="label">Referred By:</span> <?php echo $referral['referred_by']; ?></p>
        <p><span class="label">Date:</span> <?php echo date('M d, Y', strtotime($referral['created_at'])); ?></p>
        <p><span class="label">Status:</span> <span class="status-badge"><?php echo $referral['status']; ?></span></p>
    </div>

    <!-- Action Buttons -->
    <div style="margin-top: 20px;">
        <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; cursor: pointer;">Print Referral</button>
        <a href="nurse_dashboard.php" style="text-decoration: none; color: #666; margin-left: 15px;">Back to Dashboard</a>
    </div>
</div>

</body>
</html>
