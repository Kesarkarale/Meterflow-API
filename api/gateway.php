<?php
include "../config/db.php";

header("Content-Type: application/json");

$start_time = microtime(true);

// ---------- INPUT VALIDATION ----------
if (!isset($_GET["key"]) || !isset($_GET["endpoint"])) {
    http_response_code(400);
    echo json_encode(["error" => "API key and endpoint required"]);
    exit;
}

$api_key = mysqli_real_escape_string($conn, $_GET["key"]);
$endpoint = trim($_GET["endpoint"]);
$endpoint = ltrim($endpoint, "/");

// ---------- FETCH API DETAILS ----------
$keyQuery = mysqli_query($conn, "
    SELECT api_keys.*, apis.base_url 
    FROM api_keys 
    JOIN apis ON api_keys.api_id = apis.id 
    WHERE api_keys.api_key='$api_key' 
    AND api_keys.status='active'
");

$keyData = mysqli_fetch_assoc($keyQuery);

if (!$keyData) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid or revoked API key"]);
    exit;
}

$user_id = $keyData["user_id"];
$base_url = rtrim($keyData["base_url"], "/");
$request_limit = $keyData["request_limit"];

// ---------- RATE LIMIT (PER MINUTE) ----------
$current_minute = date("Y-m-d H:i");

$rateQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM usage_logs 
    WHERE api_key='$api_key' 
    AND DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = '$current_minute'
");

$rateCount = mysqli_fetch_assoc($rateQuery)["total"];

if ($rateCount >= 10) {
    http_response_code(429);
    echo json_encode([
        "error" => "Too many requests (limit: 10 per minute)"
    ]);
    exit;
}

// ---------- TOTAL REQUEST LIMIT ----------
$countQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM usage_logs 
    WHERE api_key='$api_key'
");

$totalRequests = mysqli_fetch_assoc($countQuery)["total"];

if ($totalRequests >= $request_limit) {
    http_response_code(429);
    echo json_encode([
        "error" => "Monthly request limit exceeded"
    ]);
    exit;
}

// ---------- BUILD TARGET URL ----------
$target_url = $base_url . "/" . $endpoint;
$target_url = str_replace(" ", "", $target_url);

// 🔥 URL VALIDATION (NEW)
if (!filter_var($target_url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid target URL"]);
    exit;
}

// ---------- cURL REQUEST ----------
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);

// 🔥 cURL ERROR HANDLE
if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);

    http_response_code(500);
    echo json_encode([
        "error" => "cURL Error",
        "message" => $error
    ]);
    exit;
}

$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);

// ---------- LATENCY ----------
$end_time = microtime(true);
$latency_ms = round(($end_time - $start_time) * 1000, 2);

// ---------- LOG REQUEST ----------
mysqli_query($conn, "
    INSERT INTO usage_logs 
    (user_id, api_key, endpoint, status_code, latency_ms) 
    VALUES 
    ('$user_id', '$api_key', '$endpoint', '$status_code', '$latency_ms')
");

// ---------- ERROR HANDLING ----------
if ($status_code >= 400) {
    http_response_code($status_code);
    echo json_encode([
        "error" => "API returned error",
        "status" => $status_code,
        "endpoint" => $endpoint
    ]);
    exit;
}

// ---------- SUCCESS ----------
// 🔥 Forward original content type
if ($content_type) {
    header("Content-Type: " . $content_type);
}

http_response_code($status_code);
echo $response;
?>