<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: manage_keys.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$key_id = mysqli_real_escape_string($conn, $_GET["id"]);

$checkKey = mysqli_query($conn, "
    SELECT * FROM api_keys 
    WHERE id='$key_id' AND user_id='$user_id'
");

if (mysqli_num_rows($checkKey) == 0) {
    header("Location: manage_keys.php?error=invalid_key");
    exit;
}

mysqli_query($conn, "
    UPDATE api_keys 
    SET status='revoked' 
    WHERE id='$key_id' AND user_id='$user_id'
");

header("Location: manage_keys.php?success=key_revoked");
exit;
?>