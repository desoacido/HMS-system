<?php
session_start();
include('db.php'); 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'nurse') {
    header("Location: /HMS-2/login.php");
    exit();
}

try {
    // 1. New Referrals (pending)
    $stmt_new = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE status = 'pending'");
    $stmt_new->execute();
    $count_new = $stmt_new->fetchColumn();

    // 2. In-Progress (viewed)
    $stmt_viewed = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE status = 'viewed'");
    $stmt_viewed->execute();
    $count_viewed = $stmt_viewed->fetchColumn();

    // 3. Completed
    $stmt_done = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE status = 'completed'");
    $stmt_done->execute();
    $count_done = $stmt_done->fetchColumn();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard Overview</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fb; padding: 20px; color: #333; }
        
        .stats-container { 
            display: flex; 
            gap: 20px; 
            margin-top: 20px; 
            align-items: stretch;
        }
        
        /* Ginawa nating flex ang anchor para hindi masira ang alignment ng cards */
        .stats-container a {
            flex: 1;
            text-decoration: none;
            color: inherit;
            display: flex;
        }

        .card {
            width: 100%;
            background: white;
            padding: 25px 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            background: #fff;
        }
        
        .card h3 { font-size: 13px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0; }
        .card .number { font-size: 28px; font-weight: 600; margin-top: 5px; }
        .card .icon { font-size: 35px; color: #dcdde1; opacity: 0.7; }

        /* Notification Badge */
        .bell-badge {
            position: absolute;
            top: -10px;
            right: -5px;
            background: #e84118;
            color: white;
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 50px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(232, 65, 24, 0.4);
            animation: pulse 1.5s infinite;
            z-index: 10;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(232, 65, 24, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(232, 65, 24, 0); }
            100% { box-shadow: 0 0 0 0 rgba(232, 65, 24, 0); }
        }

        /* Border colors base sa status */
        .card.new { border-bottom: 4px solid #e84118; }
        .card.viewed { border-bottom: 4px solid #f1c40f; }
        .card.completed { border-bottom: 4px solid #2ecc71; }
        
        .header-section { margin-bottom: 30px; }
    </style>
</head>
<body>

    <div class="header-section">
        <h2>Nurse Dashboard Overview</h2>
        <p style="color: #7f8c8d;">Real-time tracking of patient referrals.</p>
    </div>

    <div class="stats-container">
        <!-- New Referrals Card (Filtered to 'pending') -->
        <a href="nurse_referrallist.php?status=pending">
            <div class="card new">
                <div>
                    <h3>New Referrals</h3>
                    <div class="number"><?php echo $count_new; ?></div>
                </div>
                <div class="icon">🔔</div>
                <?php if($count_new > 0): ?>
                    <div class="bell-badge">NEW ACTION REQUIRED</div>
                <?php endif; ?>
            </div>
        </a>

        <!-- In-Progress Card (Filtered to 'viewed') -->
        <a href="nurse_referrallist.php?status=viewed">
            <div class="card viewed">
                <div>
                    <h3>In-Progress</h3>
                    <div class="number"><?php echo $count_viewed; ?></div>
                </div>
                <div class="icon">⏳</div>
            </div>
        </a>

        <!-- Completed Card (Filtered to 'completed') -->
        <a href="nurse_referrallist.php?status=completed">
            <div class="card completed">
                <div>
                    <h3>Completed</h3>
                    <div class="number"><?php echo $count_done; ?></div>
                </div>
                <div class="icon">📋</div>
            </div>
        </a>
    </div>

</body>
</html>