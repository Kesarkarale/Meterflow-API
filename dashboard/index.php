<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$totalRequests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM usage_logs WHERE user_id=$user_id"))["total"];
$totalApis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM apis WHERE user_id=$user_id"))["total"];
$totalKeys = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM api_keys WHERE user_id=$user_id AND status='active'"))["total"];
$totalErrors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM usage_logs WHERE user_id=$user_id AND status_code >= 400"))["total"];

$chartQuery = mysqli_query($conn, "
    SELECT DATE(created_at) AS request_date, COUNT(*) AS total
    FROM usage_logs
    WHERE user_id='$user_id'
    GROUP BY DATE(created_at)
    ORDER BY request_date ASC
");

$chartLabels = [];
$chartData = [];

while ($row = mysqli_fetch_assoc($chartQuery)) {
    $chartLabels[] = $row["request_date"];
    $chartData[] = $row["total"];
}

$recentLogs = mysqli_query($conn, "
    SELECT endpoint, status_code, created_at 
    FROM usage_logs 
    WHERE user_id='$user_id' 
    ORDER BY created_at DESC 
    LIMIT 5
");

$freeLimit = 3;
$pricePer100 = 0.5;
$amount = 0;

if ($totalRequests > $freeLimit) {
    $extraRequests = $totalRequests - $freeLimit;
    $amount = ceil($extraRequests / 100) * $pricePer100;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard - MeterFlow</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{box-sizing:border-box;}

body{
margin:0;
font-family:"Segoe UI",Arial;
background:#020617;
color:white;
}

.layout{display:flex;min-height:100vh;}

.sidebar{
width:240px;
background:#0f172a;
padding:25px;
}

.logo{
font-size:26px;
font-weight:900;
margin-bottom:30px;
}

.sidebar a{
display:block;
color:#94a3b8;
padding:12px;
border-radius:10px;
text-decoration:none;
margin-bottom:8px;
transition:0.2s;
}

.sidebar a:hover{
background:#2563eb;
color:white;
transform:translateX(3px);
}

.sidebar a.active{
background:linear-gradient(135deg,#2563eb,#7c3aed);
box-shadow:0 0 15px rgba(59,130,246,0.4);
color:white;
}

.sidebar a:last-child{
color:#f87171;
}

.sidebar a:last-child:hover{
background:#dc2626;
color:white;
}

.main{flex:1;padding:30px;}

.header{
position:relative;
background:linear-gradient(135deg,#6366f1,#a855f7,#ec4899);
background-size:200% 200%;
padding:30px;
border-radius:20px;
margin-bottom:25px;
animation:gradientMove 6s infinite alternate;
box-shadow:0 15px 50px rgba(168,85,247,0.3);
}

.header h2{
margin:0;
font-size:28px;
padding-right:55px;
}

.header p{
color:#e0e7ff;
margin-top:8px;
padding-right:55px;
}

.plan-icon{
position:absolute;
top:28px;
right:28px;
width:58px;
height:58px;
display:flex;
align-items:center;
justify-content:center;
border-radius:18px;
font-size:28px;
font-weight:900;
text-decoration:none;
color:white;
background:linear-gradient(135deg,#8b5cf6,#ec4899);
box-shadow:0 18px 35px rgba(139,92,246,0.35);
transition:0.25s;
}

.plan-icon:hover{
transform:translateY(-3px) scale(1.05);
box-shadow:0 22px 45px rgba(236,72,153,0.45);
}

@keyframes fadeUp{
from{opacity:0;transform:translateY(20px);}
to{opacity:1;transform:translateY(0);}
}

@keyframes gradientMove{
0%{background-position:left;}
100%{background-position:right;}
}

.cards{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:18px;
margin-bottom:25px;
}

.card{
background:rgba(15,23,42,0.7);
padding:22px;
border-radius:18px;
backdrop-filter:blur(12px);
border:1px solid rgba(255,255,255,0.1);
transition:0.3s;
animation:fadeUp 0.8s ease;
}

.card:hover{
transform:translateY(-5px);
box-shadow:0 0 25px rgba(59,130,246,0.25);
}

.card h3{
margin:0;
color:#94a3b8;
font-size:14px;
font-weight:600;
}

.card p{
font-size:30px;
font-weight:900;
margin-top:10px;
}

.card:last-child p{
color:#22c55e;
animation:pulse 1.5s infinite;
}

@keyframes pulse{
0%{opacity:1;}
50%{opacity:0.6;}
100%{opacity:1;}
}

.section{
background:rgba(15,23,42,0.7);
padding:25px;
border-radius:18px;
border:1px solid rgba(255,255,255,0.08);
margin-bottom:25px;
animation:fadeUp 0.8s ease;
}

.section h3{margin-top:0;}

.chart-section p,
.activity-text{
color:#94a3b8;
}

.chart-section canvas{
width:100%;
max-height:320px;
}

.activity-item{
padding:13px 0;
border-bottom:1px solid rgba(255,255,255,0.08);
color:#e5e7eb;
}

.activity-item:last-child{
border-bottom:none;
}

.status-ok{color:#22c55e;font-weight:900;}
.status-error{color:#ef4444;font-weight:900;}

.quick-actions{
margin-top:10px;
}

.quick-actions a{
text-decoration:none;
padding:13px 18px;
border-radius:12px;
margin-right:10px;
margin-bottom:10px;
display:inline-block;
font-weight:800;
color:white;
transition:0.25s;
}

.quick-actions a:nth-child(1){
background:linear-gradient(135deg,#2563eb,#3b82f6);
}

.quick-actions a:nth-child(2){
background:linear-gradient(135deg,#7c3aed,#9333ea);
}

.quick-actions a:nth-child(3){
background:linear-gradient(135deg,#16a34a,#22c55e);
}

.quick-actions a:nth-child(4){
background:linear-gradient(135deg,#dc2626,#ef4444);
}

.quick-actions a:hover{
transform:translateY(-3px);
box-shadow:0 12px 28px rgba(0,0,0,0.35);
}

@media(max-width:900px){
.layout{flex-direction:column;}
.sidebar{width:100%;}
.cards{grid-template-columns:repeat(2,1fr);}
}

@media(max-width:600px){
.cards{grid-template-columns:1fr;}
.main{padding:18px;}
}
</style>
</head>

<body>

<div class="layout">

<div class="sidebar">
    <div class="logo">MeterFlow</div>

    <a class="active" href="index.php">Dashboard</a>
    <a href="../api/create_api.php">Create API</a>
    <a href="../api/generate_key.php">Generate API Key</a>
    <a href="../api/manage_keys.php">Manage Keys</a>
    <a href="../api/test_api.php">Test API</a>
    <a href="../logs/usage_logs.php">Usage Logs</a>
    <a href="../billing/calculate.php">Billing</a>
    <a href="../admin/index.php">Admin Panel</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
        <a class="plan-icon" href="../billing/plans.php" title="View Plans">✦</a>
        <h2>Welcome, <?php echo $_SESSION["name"]; ?> 👋</h2>
        <p>Your API SaaS control panel with real-time insights.</p>
    </div>

    <div class="cards">
        <div class="card"><h3>Total APIs</h3><p><?php echo $totalApis; ?></p></div>
        <div class="card"><h3>Active Keys</h3><p><?php echo $totalKeys; ?></p></div>
        <div class="card"><h3>Total Requests</h3><p><?php echo $totalRequests; ?></p></div>
        <div class="card"><h3>Billing</h3><p>₹<?php echo $amount; ?></p></div>
    </div>

    <div class="cards">
        <div class="card"><h3>Free Limit</h3><p><?php echo $freeLimit; ?></p></div>
        <div class="card"><h3>Error Requests</h3><p><?php echo $totalErrors; ?></p></div>
        <div class="card"><h3>Price / 100</h3><p>₹<?php echo $pricePer100; ?></p></div>
        <div class="card"><h3>Status</h3><p>Live 🚀</p></div>
    </div>

    <div class="section chart-section">
        <h3>API Usage Graph 📈</h3>
        <p>Daily API request activity through your gateway.</p>
        <canvas id="usageChart"></canvas>
    </div>

    <div class="section">
        <h3>Recent Activity 🕒</h3>

        <?php if(mysqli_num_rows($recentLogs) > 0){ ?>
            <?php while($log = mysqli_fetch_assoc($recentLogs)){ ?>
                <div class="activity-item">
                    <?php echo $log['endpoint']; ?> 
                    <?php if ($log['status_code'] == 200) { ?>
                        <span class="status-ok">(<?php echo $log['status_code']; ?>)</span>
                    <?php } else { ?>
                        <span class="status-error">(<?php echo $log['status_code']; ?>)</span>
                    <?php } ?>
                    - <?php echo $log['created_at']; ?>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="activity-text">No activity yet.</p>
        <?php } ?>
    </div>

    <div class="section">
        <h3>Quick Actions ⚡</h3>

        <div class="quick-actions">
            <a href="../api/create_api.php">Create API</a>
            <a href="../api/generate_key.php">Generate Key</a>
            <a href="../logs/usage_logs.php">Logs</a>
            <a href="../billing/calculate.php">Billing</a>
        </div>
    </div>

</div>

</div>

<script>
const chartLabels = <?php echo json_encode($chartLabels); ?>;
const chartData = <?php echo json_encode($chartData); ?>;

const ctx = document.getElementById("usageChart");

new Chart(ctx, {
    type: "line",
    data: {
        labels: chartLabels,
        datasets: [{
            label: "Requests per Day",
            data: chartData,
            borderColor: "#60a5fa",
            backgroundColor: "rgba(96,165,250,0.18)",
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: "#a78bfa",
            pointBorderColor: "#ffffff",
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: "#cbd5e1"
                }
            }
        },
        scales: {
            x: {
                ticks: { color: "#94a3b8" },
                grid: { color: "rgba(255,255,255,0.08)" }
            },
            y: {
                beginAtZero: true,
                ticks: { color: "#94a3b8" },
                grid: { color: "rgba(255,255,255,0.08)" }
            }
        }
    }
});
</script>

</body>
</html>
