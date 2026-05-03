<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=meterflow_usage_logs.csv");

$output = fopen("php://output", "w");

fputcsv($output, ["ID", "API Key", "Endpoint", "Status Code", "Latency (ms)", "Created At"]);

$query = mysqli_query($conn, "
    SELECT * FROM usage_logs 
    WHERE user_id='$user_id'
    ORDER BY created_at DESC
");

while ($row = mysqli_fetch_assoc($query)) {
    fputcsv($output, [
        $row["id"],
        $row["api_key"],
        $row["endpoint"],
        $row["status_code"],
        $row["latency_ms"],
        $row["created_at"]
    ]);
}

fclose($output);
exit;
?>
