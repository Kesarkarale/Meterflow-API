<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$logs = mysqli_query($conn, "
    SELECT * FROM usage_logs 
    WHERE user_id='$user_id' 
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Usage Logs - MeterFlow</title>

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

.card{
background:rgba(15,23,42,0.7);
padding:26px;
border-radius:18px;
backdrop-filter:blur(12px);
border:1px solid rgba(255,255,255,0.1);
box-shadow:0 20px 40px rgba(0,0,0,0.25);
animation:fadeUp 0.8s ease;
overflow-x:auto;
}

.card h3{
margin:0;
font-size:22px;
}

.card p{
color:#94a3b8;
margin-bottom:22px;
}

/* Table */
table{
width:100%;
border-collapse:collapse;
min-width:850px;
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

/* Key */
.key{
font-family:Consolas,monospace;
color:#60a5fa;
}

/* Status */
.status-ok{
color:#22c55e;
font-weight:900;
}

.status-error{
color:#ef4444;
font-weight:900;
}

/* Latency */
.latency{
font-weight:700;
}

.latency.fast{color:#22c55e;}
.latency.medium{color:#facc15;}
.latency.slow{color:#ef4444;}

/* Empty */
.empty{
text-align:center;
padding:20px;
color:#94a3b8;
}

@media(max-width:900px){
.layout{flex-direction:column;}
.sidebar{width:100%;}
}

@media(max-width:600px){
.main{padding:18px;}
}

.export-btn{
display:inline-block;
background:linear-gradient(135deg,#16a34a,#22c55e);
color:white;
padding:12px 16px;
border-radius:12px;
text-decoration:none;
font-weight:900;
margin-bottom:18px;
transition:0.25s;
}

.export-btn:hover{
transform:translateY(-2px);
box-shadow:0 10px 25px rgba(34,197,94,0.3);
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

    <a href="../dashboard/index.php">Dashboard</a>
    <a href="../api/create_api.php">Create API</a>
    <a href="../api/generate_key.php">Generate API Key</a>
    <a href="../api/manage_keys.php">Manage Keys</a>
    <a href="../api/test_api.php">Test API</a>
    <a class="active" href="usage_logs.php">Usage Logs</a>
    <a href="../billing/calculate.php">Billing</a>
    <a href="../admin/index.php">Admin Panel</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

<div class="header">
	<a class="plan-icon" href="../billing/plans.php" title="View Plans">✦</a>
    <h2>Usage Logs 📊</h2>
    <p>Track every API request, response status, and latency in real-time.</p>
</div>

<div class="card">
    <h3>Request Activity</h3>
    <p>All API calls routed through your MeterFlow gateway.</p>
<a class="export-btn" href="export.php">Download Logs CSV</a>
    <table>
        <tr>
            <th>ID</th>
            <th>API Key</th>
            <th>Endpoint</th>
            <th>Status</th>
            <th>Latency</th>
            <th>Time</th>
        </tr>

        <?php if(mysqli_num_rows($logs)>0){ ?>
        <?php while ($log = mysqli_fetch_assoc($logs)) { ?>

        <tr>
            <td><?php echo $log["id"]; ?></td>

            <td class="key">
                <?php echo substr($log["api_key"], 0, 10); ?>...
            </td>

            <td><?php echo $log["endpoint"]; ?></td>

            <td>
                <?php if ($log["status_code"] == 200) { ?>
                    <span class="status-ok">200</span>
                <?php } else { ?>
                    <span class="status-error"><?php echo $log["status_code"]; ?></span>
                <?php } ?>
            </td>

            <td class="latency 
                <?php 
                    if($log["latency_ms"] < 200) echo "fast";
                    elseif($log["latency_ms"] < 600) echo "medium";
                    else echo "slow";
                ?>">
                <?php echo $log["latency_ms"]; ?> ms
            </td>

            <td><?php echo $log["created_at"]; ?></td>
        </tr>

        <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="6" class="empty">No API requests yet.</td>
            </tr>
        <?php } ?>

    </table>

</div>

</div>

</div>

</body>
</html>