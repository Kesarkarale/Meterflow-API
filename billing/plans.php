<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Plans - MeterFlow</title>

<style>
*{box-sizing:border-box;}
body{margin:0;font-family:"Segoe UI",Arial;background:#020617;color:white;}
.layout{display:flex;min-height:100vh;}
.sidebar{width:240px;background:#0f172a;padding:25px;}
.logo{font-size:26px;font-weight:900;margin-bottom:30px;}
.sidebar a{display:block;color:#94a3b8;padding:12px;border-radius:10px;text-decoration:none;margin-bottom:8px;transition:0.2s;}
.sidebar a:hover{background:#2563eb;color:white;transform:translateX(3px);}
.sidebar a.active{background:linear-gradient(135deg,#2563eb,#7c3aed);box-shadow:0 0 15px rgba(59,130,246,0.4);color:white;}
.sidebar a:last-child{color:#f87171;}
.sidebar a:last-child:hover{background:#dc2626;color:white;}
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

.plans{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;}
.plan{background:rgba(15,23,42,0.7);padding:28px;border-radius:18px;border:1px solid rgba(255,255,255,0.1);transition:0.3s;}
.plan:hover{transform:translateY(-5px);box-shadow:0 0 25px rgba(59,130,246,0.25);}
.plan h2{margin-top:0;}
.price{font-size:32px;font-weight:900;color:#22c55e;margin:18px 0;}
.plan ul{padding-left:20px;color:#cbd5e1;line-height:1.9;}
.btn{display:inline-block;width:100%;text-align:center;padding:13px;border-radius:12px;background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;text-decoration:none;font-weight:900;margin-top:18px;}
.badge{display:inline-block;background:rgba(34,197,94,0.13);color:#bbf7d0;border:1px solid rgba(34,197,94,0.35);padding:6px 10px;border-radius:999px;font-size:13px;font-weight:900;}
@media(max-width:900px){.layout{flex-direction:column}.sidebar{width:100%}.plans{grid-template-columns:1fr}}
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
    <a href="../logs/usage_logs.php">Usage Logs</a>
    <a href="calculate.php">Billing</a>
    <a href="../admin/index.php">Admin Panel</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

<div class="header">
    <h2>Subscription Plans 🚀</h2>
    <p>Choose the best plan for your API usage and billing needs.</p>
</div>

<div class="plans">

    <div class="plan">
        <span class="badge">Starter</span>
        <h2>Free</h2>
        <div class="price">₹0</div>
        <ul>
            <li>1000 requests / month</li>
            <li>Basic API key management</li>
            <li>Usage logs</li>
            <li>Community support</li>
        </ul>
        <a class="btn" href="calculate.php">Current Billing</a>
    </div>

    <div class="plan">
        <span class="badge">Popular</span>
        <h2>Pro</h2>
        <div class="price">₹0.5 / extra request</div>
        <ul>
            <li>Usage-based billing</li>
            <li>API gateway access</li>
            <li>Analytics dashboard</li>
            <li>Razorpay payment support</li>
        </ul>
        <a class="btn" href="calculate.php">Pay Usage Bill</a>
    </div>

    <div class="plan">
        <span class="badge">Business</span>
        <h2>Enterprise</h2>
        <div class="price">Custom</div>
        <ul>
            <li>Unlimited API usage</li>
            <li>Priority support</li>
            <li>Custom rate limits</li>
            <li>Dedicated billing setup</li>
        </ul>
        <a class="btn" href="calculate.php">Contact Admin</a>
    </div>

</div>

</div>
</div>

</body>
</html>
