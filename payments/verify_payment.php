<?php
session_start();
include "../config/db.php";
include "../config/razorpay.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["user_id"];

if (
    !isset($_POST["razorpay_order_id"]) ||
    !isset($_POST["razorpay_payment_id"]) ||
    !isset($_POST["razorpay_signature"])
) {
    echo json_encode(["success" => false, "message" => "Missing payment data"]);
    exit;
}

$order_id = mysqli_real_escape_string($conn, $_POST["razorpay_order_id"]);
$payment_id = mysqli_real_escape_string($conn, $_POST["razorpay_payment_id"]);
$signature = $_POST["razorpay_signature"];

$generated_signature = hash_hmac(
    "sha256",
    $_POST["razorpay_order_id"] . "|" . $_POST["razorpay_payment_id"],
    $razorpay_key_secret
);

if (hash_equals($generated_signature, $signature)) {
    mysqli_query($conn, "
        UPDATE payments 
        SET razorpay_payment_id='$payment_id', status='paid'
        WHERE razorpay_order_id='$order_id' AND user_id='$user_id'
    ");

    echo json_encode([
        "success" => true,
        "message" => "Payment successful"
    ]);
} else {
    mysqli_query($conn, "
        UPDATE payments 
        SET status='failed'
        WHERE razorpay_order_id='$order_id' AND user_id='$user_id'
    ");

    echo json_encode([
        "success" => false,
        "message" => "Payment verification failed"
    ]);
}
?>