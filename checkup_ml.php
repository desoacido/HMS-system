<?php
session_start();
include __DIR__ . '/db.php';

/* ================= INPUT ================= */
// Support both GET (?id=) and POST (form submission)
$referral_id = $_GET['id'] ?? $_POST['referral_id'] ?? null;

if (!$referral_id) {
    die("Invalid request: Missing Referral ID.");
}

/* ================= GET REFERRAL & VISIT INFO ================= */
$stmt = $conn->prepare("
    SELECT r.*, v.category 
    FROM referrals r 
    JOIN visits v ON v.id = r.visit_id 
    WHERE r.id = ?
");
$stmt->execute([$referral_id]);
$ref = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ref) {
    die("Referral not found in database.");
}

$visit_id = $ref['visit_id']; 
$category = $ref['category'];

/* ================= AUTO STATUS FLOW ================= */
if ($ref['status'] === 'pending') {
    $conn->prepare("
        UPDATE referrals 
        SET status='in_progress'
        WHERE id=?
    ")->execute([$referral_id]);
    $ref['status'] = 'in_progress';
}

/* ================= GET PATIENT ================= */
$stmt = $conn->prepare("SELECT * FROM patients WHERE id=?");
$stmt->execute([$ref['patient_id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

/* ================= FETCH ALL DATA ================= */
function getData($conn, $table, $visit_id) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE visit_id=?");
    $stmt->execute([$visit_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

$checkup      = getData($conn, "checkup_visits", $visit_id);
$immunization = getData($conn, "immunization_visits", $visit_id);
$fp           = getData($conn, "family_planning_visits", $visit_id);

/* ================= PROMPT ================= */
$prompt = "
You are a professional nurse AI assistant.

Give ONLY:
1. Risk Level (LOW / MODERATE / HIGH)
2. Recommendation
3. Emergency (YES/NO)

Patient: ".($patient['firstname'] ?? '')." ".($patient['lastname'] ?? '')."
Category: $category

CHECKUP:
".json_encode($checkup)."

IMMUNIZATION:
".json_encode($immunization)."

FAMILY PLANNING:
".json_encode($fp)."

Treat missing data as risk factors.
";

/* ================= GEMINI API ================= */
$apiKey = "AIzaSyCdF_sWFvUTMxrz6z9NtxM2mu6MkCHWMaI";
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=".$apiKey;

$payload = [
    "contents" => [[
        "parts" => [["text" => $prompt]]
    ]]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("CURL ERROR: " . curl_error($ch));
}
curl_close($ch);

$result = json_decode($response, true);

/* ================= GET ML RESULT ================= */
$ml = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$ml) {
    die("No ML response from API. Raw response: " . htmlspecialchars($response));
}

/* ================= SAVE RESULT ================= */
$conn->prepare("
    UPDATE referrals 
    SET ml_result = ?, status='completed'
    WHERE id=?
")->execute([$ml, $referral_id]);

/* ================= DISPLAY RESULT (instead of redirect) ================= */
$patientName = htmlspecialchars(($patient['firstname'] ?? '') . ' ' . ($patient['lastname'] ?? ''));

// Parse risk level for color coding
$riskColor = '#28a745'; // green = LOW
if (stripos($ml, 'HIGH') !== false) {
    $riskColor = '#dc3545'; // red
} elseif (stripos($ml, 'MODERATE') !== false) {
    $riskColor = '#fd7e14'; // orange
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ML Analysis Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
            min-height: 100vh;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 32px;
            max-width: 700px;
            width: 100%;
        }
        .card h2 {
            color: #2a5298;
            margin-bottom: 6px;
        }
        .card .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .risk-badge {
            display: inline-block;
            background: <?= $riskColor ?>;
            color: white;
            font-weight: bold;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .ml-result-box {
            background: #f8f9fa;
            border-left: 4px solid <?= $riskColor ?>;
            border-radius: 6px;
            padding: 20px;
            white-space: pre-wrap;
            font-size: 15px;
            line-height: 1.7;
            color: #333;
        }
        .actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #2a5298; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.88; }
        .meta {
            font-size: 13px;
            color: #888;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>🤖 ML Analysis Result</h2>
    <div class="subtitle">Powered by Gemini AI</div>

    <div class="meta">
        <strong>Patient:</strong> <?= $patientName ?> &nbsp;|&nbsp;
        <strong>Category:</strong> <?= htmlspecialchars($category) ?> &nbsp;|&nbsp;
        <strong>Referral ID:</strong> #<?= htmlspecialchars($referral_id) ?>
    </div>

    <div class="risk-badge">
        <?php
            if (stripos($ml, 'HIGH') !== false) echo '🔴 HIGH RISK';
            elseif (stripos($ml, 'MODERATE') !== false) echo '🟠 MODERATE RISK';
            else echo '🟢 LOW RISK';
        ?>
    </div>

    <div class="ml-result-box"><?= htmlspecialchars($ml) ?></div>

    <div class="actions">
        <a href="nurse_viewReferral.php?id=<?= urlencode($referral_id) ?>" class="btn btn-primary">
            📋 View Full Referral
        </a>
       
    </div>
</div>
</body>
</html>