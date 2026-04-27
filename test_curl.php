<?php
echo "<pre>";

// 1. Check if cURL is enabled
echo "cURL enabled: " . (function_exists('curl_init') ? "YES ✅" : "NO ❌") . "\n\n";

// 2. Try the actual Render URL
$ch = curl_init("https://hms-ml-api.onrender.com/predict");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "symptom"     => "fever",
    "temperature" => 38.5,
    "heart_rate"  => 90,
    "bp"          => "120/80",
    "purpose"     => "general"
]));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// 3. Disable SSL verification (test only)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response   = curl_exec($ch);
$http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$curl_errno = curl_errno($ch);
curl_close($ch);

echo "HTTP Code:  $http_code\n";
echo "cURL Error: $curl_error\n";
echo "cURL Errno: $curl_errno\n";
echo "Response:   $response\n";

echo "</pre>";