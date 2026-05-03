<?php
session_start();
include "../config/db.php";
include "../config/razorpay.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["user_id"];
$amount = isset($_POST["amount"]) ? floatval($_POST["amount"]) : 0;

if ($amount <= 0) {
    echo json_encode(["error" => "Invalid amount"]);
    exit;
}

if (
    empty($razorpay_key_id) ||
    empty($razorpay_key_secret) ||
    $razorpay_key_id == "rzp_test_YOUR_REAL_KEY_ID" ||
    $razorpay_key_secret == "YOUR_REAL_KEY_SECRET"
) {
    echo json_encode(["error" => "Please add real Razorpay Test API keys in config/razorpay.php"]);
    exit;
}

// Razorpay amount is in paise; minimum ₹1 = 100 paise
$amount_paise = max(100, round($amount * 100));

$data = [
    "amount" => $amount_paise,
    "currency" => "INR",
    "receipt" => "meterflow_" . time()
];

$ch = curl_init("https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $razorpay_key_id . ":" . $razorpay_key_secret);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo json_encode(["error" => "cURL Error: " . $curl_error]);
    exit;
}

$order = json_decode($response, true);

if (!isset($order["id"])) {
    echo json_encode([
        "error" => "Order creation failed",
        "details" => $response
    ]);
    exit;
}

$order_id = mysqli_real_escape_string($conn, $order["id"]);
$amount_db = mysqli_real_escape_string($conn, $amount);

mysqli_query($conn, "
    INSERT INTO payments (user_id, razorpay_order_id, amount, status)
    VALUES ('$user_id', '$order_id', '$amount_db', 'created')
");

echo json_encode([
    "order_id" => $order["id"],
    "amount" => $amount_paise,
    "key" => $razorpay_key_id
]);
?>
