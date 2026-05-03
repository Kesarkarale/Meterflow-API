<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$keys = mysqli_query($conn, "
    SELECT api_keys.*, apis.api_name, apis.base_url
    FROM api_keys
    JOIN apis ON api_keys.api_id = apis.id
    WHERE api_keys.user_id='$user_id'
    ORDER BY api_keys.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage API Keys - MeterFlow</title>

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

/* 🔥 MESSAGE STYLES */
.msg{
padding:13px;
border-radius:14px;
margin-bottom:18px;
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

table{
width:100%;
border-collapse:collapse;
min-width:850px;
overflow:hidden;
border-radius:16px;
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
color:#e5e7eb;
font-size:14px;
}

tr{
transition:0.25s;
}

tr:hover{
background:rgba(59,130,246,0.08);
}

.key{
font-family:Consolas,monospace;
color:#60a5fa;
}

.badge{
padding:7px 12px;
border-radius:999px;
font-weight:900;
font-size:13px;
display:inline-block;
}

.active-badge{
background:rgba(34,197,94,0.13);
border:1px solid rgba(34,197,94,0.35);
color:#bbf7d0;
}

.revoked-badge{
background:rgba(239,68,68,0.13);
border:1px solid rgba(239,68,68,0.35);
color:#fecaca;
}

.btn-danger{
background:linear-gradient(135deg,#dc2626,#ef4444);
color:white;
padding:9px 13px;
border-radius:10px;
text-decoration:none;
font-weight:800;
display:inline-block;
transition:0.25s;
}

.btn-danger:hover{
transform:translateY(-2px);
box-shadow:0 10px 25px rgba(239,68,68,0.3);
}

.empty{
padding:20px;
text-align:center;
color:#94a3b8;
}

@media(max-width:900px){
.layout{flex-direction:column;}
.sidebar{width:100%;}
}

@media(max-width:600px){
.main{padding:18px;}
}

.copy-btn{
background:linear-gradient(135deg,#2563eb,#7c3aed);
color:white;
border:none;
padding:7px 10px;
border-radius:8px;
margin-left:8px;
cursor:pointer;
font-weight:800;
transition:0.25s;
}

.copy-btn:hover{
transform:translateY(-2px);
box-shadow:0 8px 20px rgba(124,58,237,0.35);
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
    <a href="create_api.php">Create API</a>
    <a href="generate_key.php">Generate API Key</a>
    <a class="active" href="manage_keys.php">Manage Keys</a>
    <a href="../api/test_api.php">Test API</a>
    <a href="../logs/usage_logs.php">Usage Logs</a>
    <a href="../billing/calculate.php">Billing</a>
    <a href="../admin/index.php">Admin Panel</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
<a class="plan-icon" href="../billing/plans.php" title="Plans">✦</a>
        <h2>Manage API Keys 🔐</h2>
        <p>View, monitor, and revoke API keys from your MeterFlow workspace.</p>
    </div>

    <div class="card">
        <h3>API Key Inventory</h3>
        <p>Securely manage active and revoked keys connected to your APIs.</p>

        <!-- 🔥 SUCCESS / ERROR MESSAGE -->
        <?php if (isset($_GET["success"]) && $_GET["success"] == "key_revoked") { ?>
            <div class="msg success">API key revoked successfully ✅</div>
        <?php } ?>

        <?php if (isset($_GET["error"]) && $_GET["error"] == "invalid_key") { ?>
            <div class="msg error">Invalid API key request ❌</div>
        <?php } ?>

        <table>
            <tr>
                <th>ID</th>
                <th>API Name</th>
                <th>API Key</th>
                <th>Base URL</th>
                <th>Limit</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php if (mysqli_num_rows($keys) > 0) { ?>
                <?php while ($key = mysqli_fetch_assoc($keys)) { ?>
                <tr>
                    <td><?php echo $key["id"]; ?></td>
                    <td><?php echo $key["api_name"]; ?></td>
		   <td class="key">
    		<?php echo substr($key["api_key"], 0, 10); ?>...
    <button class="copy-btn" onclick="copyKey('<?php echo $key['api_key']; ?>')">Copy</button>
		</td>                  
		    <td><?php echo $key["base_url"]; ?></td>
                    <td><?php echo $key["request_limit"]; ?></td>

                    <td>
                        <?php if ($key["status"] == "active") { ?>
                            <span class="badge active-badge">Active</span>
                        <?php } else { ?>
                            <span class="badge revoked-badge">Revoked</span>
                        <?php } ?>
                    </td>

                    <td>
                        <?php if ($key["status"] == "active") { ?>
                            <a class="btn-danger" href="revoke_key.php?id=<?php echo $key['id']; ?>"
                               onclick="return confirm('Are you sure you want to revoke this key?')">
                               Revoke
                            </a>
                        <?php } else { ?>
                            <span style="color:#64748b;">No Action</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="7" class="empty">No API keys found. Generate your first API key.</td>
                </tr>
            <?php } ?>

        </table>
    </div>

</div>

</div>
<script>
function copyKey(key){
    navigator.clipboard.writeText(key);
    alert("API Key copied successfully!");
}
</script>

</body>
</html>