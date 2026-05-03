<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$userCheck = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$currentUser = mysqli_fetch_assoc($userCheck);

if ($currentUser["role"] != "admin") {
    echo "Access Denied. Admin only.";
    exit;
}

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))["total"];
$totalApis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM apis"))["total"];
$totalKeys = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM api_keys"))["total"];
$totalRequests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM usage_logs"))["total"];

$free_limit = 3;  
$price_per_100 = 0.5;

$totalRevenue = 0;

$allUsers = mysqli_query($conn, "SELECT id FROM users");

while ($u = mysqli_fetch_assoc($allUsers)) {
    $uid = $u["id"];

    $reqData = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) AS total 
        FROM usage_logs 
        WHERE user_id='$uid'
    "));

    $userRequests = $reqData["total"];

    if ($userRequests > $free_limit) {
        $extra = $userRequests - $free_limit;
        $totalRevenue += ceil($extra / 100) * $price_per_100;
    }
}
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

$dailyLogs = mysqli_query($conn, "
    SELECT DATE(created_at) AS log_date, COUNT(*) AS total 
    FROM usage_logs 
    GROUP BY DATE(created_at)
    ORDER BY log_date ASC
");

$chartLabels = [];
$chartData = [];

while ($row = mysqli_fetch_assoc($dailyLogs)) {
    $chartLabels[] = $row["log_date"];
    $chartData[] = $row["total"];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Panel - MeterFlow</title>

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

.header h2{margin:0;font-size:28px;}
.header p{color:#e0e7ff;margin-top:8px;}

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
grid-template-columns:repeat(5,1fr);
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
font-size:14px;
color:#94a3b8;
font-weight:600;
}

.card p{
font-size:28px;
font-weight:900;
margin-top:10px;
animation:countUp 0.6s ease;
}

@keyframes countUp{
from{transform:translateY(10px);opacity:0;}
to{transform:translateY(0);opacity:1;}
}

.card:last-child p{
color:#22c55e;
}

.box{
background:rgba(15,23,42,0.7);
padding:25px;
border-radius:18px;
backdrop-filter:blur(12px);
border:1px solid rgba(255,255,255,0.1);
box-shadow:0 20px 40px rgba(0,0,0,0.25);
margin-bottom:25px;
animation:fadeUp 0.9s ease;
overflow-x:auto;
}

.box h3{
margin-top:0;
font-size:22px;
}

.chart-wrap{
height:320px;
}

table{
width:100%;
border-collapse:collapse;
min-width:750px;
border-radius:16px;
overflow:hidden;
}

th{
background:#0f172a;
color:#cbd5e1;
padding:15px;
text-align:left;
font-size:14px;
}

td{
padding:15px;
border-bottom:1px solid rgba(255,255,255,0.08);
font-size:14px;
color:#e5e7eb;
}

tr{
transition:0.25s;
}

tr:hover{
background:rgba(59,130,246,0.08);
}

.badge{
padding:7px 12px;
border-radius:999px;
font-weight:900;
font-size:13px;
background:rgba(59,130,246,0.14);
border:1px solid rgba(59,130,246,0.35);
color:#93c5fd;
}

.admin-badge{
background:rgba(34,197,94,0.13);
border:1px solid rgba(34,197,94,0.35);
color:#bbf7d0;
}

@media(max-width:1000px){
.cards{grid-template-columns:repeat(2,1fr);}
}

@media(max-width:900px){
.layout{flex-direction:column;}
.sidebar{width:100%;}
}

@media(max-width:600px){
.main{padding:18px;}
.cards{grid-template-columns:1fr;}
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
</style>
</head>

<body>

<div class="layout">

<div class="sidebar">
    <div class="logo">MeterFlow</div>

    <a class="active" href="index.php">Admin Panel</a>
    <a href="../dashboard/index.php">Dashboard</a>
    <a href="../api/create_api.php">Create API</a>
    <a href="../api/generate_key.php">Generate API Key</a>
    <a href="../api/manage_keys.php">Manage Keys</a>
    <a href="../api/test_api.php">Test API</a>
    <a href="../logs/usage_logs.php">Usage Logs</a>
    <a href="../billing/calculate.php">Billing</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
<a class="plan-icon" href="../billing/plans.php" title="Plans">✦</a>
        <h2>Admin Panel 🛡️</h2>
        <p>Monitor all users, APIs, keys, usage, and platform revenue.</p>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Total Users</h3>
            <p><?php echo $totalUsers; ?></p>
        </div>

        <div class="card">
            <h3>Total APIs</h3>
            <p><?php echo $totalApis; ?></p>
        </div>

        <div class="card">
            <h3>Total Keys</h3>
            <p><?php echo $totalKeys; ?></p>
        </div>

        <div class="card">
            <h3>Total Requests</h3>
            <p><?php echo $totalRequests; ?></p>
        </div>

        <div class="card">
            <h3>Revenue</h3>
            <p>₹<?php echo $totalRevenue; ?></p>
        </div>
    </div>

    <div class="box">
        <h3>API Usage Analytics 📈</h3>
        <div class="chart-wrap">
            <canvas id="usageChart"></canvas>
        </div>
    </div>

    <div class="box">
        <h3>All Users</h3>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
            </tr>

            <?php while ($user = mysqli_fetch_assoc($users)) { ?>
                <tr>
                    <td><?php echo $user["id"]; ?></td>
                    <td><?php echo $user["name"]; ?></td>
                    <td><?php echo $user["email"]; ?></td>
                    <td>
                        <?php if($user["role"] == "admin"){ ?>
                            <span class="badge admin-badge">Admin</span>
                        <?php } else { ?>
                            <span class="badge"><?php echo $user["role"]; ?></span>
                        <?php } ?>
                    </td>
                    <td><?php echo $user["created_at"]; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

</div>

</div>

<script>
const labels = <?php echo json_encode($chartLabels); ?>;
const dataValues = <?php echo json_encode($chartData); ?>;

const ctx = document.getElementById('usageChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Requests per Day',
            data: dataValues,
            borderColor: '#60a5fa',
            backgroundColor: 'rgba(96,165,250,0.18)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#a78bfa',
            pointBorderColor: '#ffffff',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#cbd5e1'
                }
            }
        },
        scales: {
            x: {
                ticks: { color: '#94a3b8' },
                grid: { color: 'rgba(255,255,255,0.08)' }
            },
            y: {
                ticks: { color: '#94a3b8' },
                grid: { color: 'rgba(255,255,255,0.08)' },
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>
