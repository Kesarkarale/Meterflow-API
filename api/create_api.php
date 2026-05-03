<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_name = $_POST["api_name"];
    $base_url = rtrim($_POST["base_url"], "/");
    $user_id = $_SESSION["user_id"];

    $sql = "INSERT INTO apis (user_id, api_name, base_url) 
            VALUES ('$user_id', '$api_name', '$base_url')";

    if (mysqli_query($conn, $sql)) {
        $message = "API Created Successfully ✅";
        $messageType = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Create API - MeterFlow</title>

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

.main{
flex:1;
padding:30px;
}

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
}

.header p{
color:#e0e7ff;
margin-top:8px;
}

@keyframes fadeUp{
from{opacity:0;transform:translateY(20px);}
to{opacity:1;transform:translateY(0);}
}

@keyframes gradientMove{
0%{background-position:left;}
100%{background-position:right;}
}

.content-grid{
display:grid;
grid-template-columns:1.1fr 0.9fr;
gap:24px;
align-items:start;
}

.card{
background:rgba(15,23,42,0.7);
padding:28px;
border-radius:18px;
backdrop-filter:blur(12px);
border:1px solid rgba(255,255,255,0.1);
box-shadow:0 20px 40px rgba(0,0,0,0.25);
animation:fadeUp 0.8s ease;
}

.card h3{
margin:0 0 8px;
font-size:22px;
}

.card .subtext{
color:#94a3b8;
margin-bottom:25px;
line-height:1.6;
}

label{
display:block;
font-size:14px;
font-weight:700;
color:#cbd5e1;
margin-bottom:8px;
}

input{
width:100%;
padding:15px;
margin-bottom:20px;
border:1px solid rgba(255,255,255,0.14);
background:rgba(2,6,23,0.7);
color:white;
border-radius:14px;
font-size:15px;
outline:none;
transition:0.25s;
}

input::placeholder{
color:#64748b;
}

input:focus{
border-color:#60a5fa;
box-shadow:0 0 0 4px rgba(96,165,250,0.16);
transform:translateY(-1px);
}

button{
width:100%;
padding:15px;
background:linear-gradient(135deg,#2563eb,#7c3aed);
color:white;
border:none;
border-radius:14px;
font-size:16px;
font-weight:900;
cursor:pointer;
transition:0.25s;
box-shadow:0 14px 30px rgba(37,99,235,0.25);
}

button:hover{
transform:translateY(-3px);
box-shadow:0 18px 40px rgba(124,58,237,0.35);
}

.msg{
padding:13px;
border-radius:14px;
margin-bottom:20px;
font-weight:800;
font-size:14px;
animation:fadeUp 0.4s ease;
}

.success{
background:rgba(34,197,94,0.13);
border:1px solid rgba(34,197,94,0.35);
color:#bbf7d0;
}

.error{
background:rgba(239,68,68,0.13);
border:1px solid rgba(239,68,68,0.35);
color:#fecaca;
}

.info-box{
background:rgba(2,6,23,0.55);
padding:22px;
border-radius:18px;
border:1px solid rgba(255,255,255,0.08);
animation:fadeUp 1s ease;
}

.info-box h3{
margin-top:0;
}

.info-item{
background:rgba(255,255,255,0.06);
padding:14px;
border-radius:14px;
margin-bottom:12px;
color:#cbd5e1;
}

.code-box{
background:#020617;
color:#22c55e;
font-family:Consolas,monospace;
padding:14px;
border-radius:14px;
font-size:14px;
word-break:break-all;
border:1px solid rgba(34,197,94,0.25);
}

@media(max-width:900px){
.layout{flex-direction:column;}
.sidebar{width:100%;}
.content-grid{grid-template-columns:1fr;}
}

@media(max-width:600px){
.main{padding:18px;}
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
    <a class="active" href="create_api.php">Create API</a>
    <a href="generate_key.php">Generate API Key</a>
    <a href="manage_keys.php">Manage Keys</a>
    <a href="../api/test_api.php">Test API</a>
    <a href="../logs/usage_logs.php">Usage Logs</a>
    <a href="../billing/calculate.php">Billing</a>
    <a href="../admin/index.php">Admin Panel</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
<a class="plan-icon" href="../billing/plans.php" title="Plans">✦</a>
        <h2>Create New API 🚀</h2>
        <p>Add an external API endpoint and connect it to the MeterFlow gateway.</p>
    </div>

    <div class="content-grid">

        <div class="card">
            <h3>API Details</h3>
            <p class="subtext">Enter the API name and base URL. MeterFlow will use this base URL to forward gateway requests.</p>

            <?php if($message!=""){ ?>
                <div class="msg <?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php } ?>

            <form method="POST">
                <label>API Name</label>
                <input type="text" name="api_name" placeholder="e.g. JSON Placeholder API" required>

                <label>Base URL</label>
                <input type="text" name="base_url" placeholder="e.g. https://jsonplaceholder.typicode.com" required>

                <button type="submit">Create API</button>
            </form>
        </div>

        <div class="info-box">
            <h3>How it works</h3>

            <div class="info-item">1. Create API using its base URL.</div>
            <div class="info-item">2. Generate an API key for this API.</div>
            <div class="info-item">3. Send requests through your gateway URL.</div>

            <p style="color:#94a3b8;">Example base URL:</p>
            <div class="code-box">https://jsonplaceholder.typicode.com</div>

            <p style="color:#94a3b8;margin-top:18px;">Gateway format:</p>
            <div class="code-box">/api/gateway.php?key=YOUR_KEY&endpoint=posts</div>
        </div>

    </div>

</div>

</div>

</body>
</html>
