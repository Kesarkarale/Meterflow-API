<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";
$messageType = "";
$generated_key = "";

$apis = mysqli_query($conn, "SELECT * FROM apis WHERE user_id='$user_id'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_id = $_POST["api_id"];
    $api_key = bin2hex(random_bytes(16));

    $sql = "INSERT INTO api_keys (api_id, user_id, api_key) 
            VALUES ('$api_id', '$user_id', '$api_key')";

    if (mysqli_query($conn, $sql)) {
        $message = "API Key Generated Successfully ✅";
        $messageType = "success";
        $generated_key = $api_key;
    } else {
        $message = "Error: " . mysqli_error($conn);
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Generate API Key - MeterFlow</title>

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
}

select{
width:100%;
padding:15px;
margin-bottom:20px;
border:1px solid rgba(255,255,255,0.14);
background:rgba(2,6,23,0.7);
color:white;
border-radius:14px;
font-size:15px;
outline:none;
}

select:focus{
border-color:#60a5fa;
box-shadow:0 0 0 4px rgba(96,165,250,0.16);
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

.key-box{
margin-top:20px;
background:#020617;
border:1px solid rgba(34,197,94,0.3);
padding:18px;
border-radius:14px;
}

.api-key{
font-family:Consolas,monospace;
color:#22c55e;
word-break:break-all;
}

.hint{
margin-top:15px;
background:rgba(59,130,246,0.12);
border:1px solid rgba(59,130,246,0.3);
padding:14px;
border-radius:14px;
font-size:14px;
color:#93c5fd;
}

@media(max-width:900px){
.layout{flex-direction:column;}
.sidebar{width:100%;}
.content-grid{grid-template-columns:1fr;}
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
    <a class="active" href="generate_key.php">Generate API Key</a>
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
<h2>Generate API Key 🔐</h2>
<p>Create secure keys to access your APIs through the MeterFlow gateway.</p>
</div>

<div class="content-grid">

<div class="card">
<h3>Select API</h3>
<p class="subtext">Choose an API and generate a secure key.</p>

<?php if ($message != "") { ?>
<div class="msg <?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php } ?>

<form method="POST">
<select name="api_id" required>
<option value="">Select API</option>

<?php while ($api = mysqli_fetch_assoc($apis)) { ?>
<option value="<?php echo $api['id']; ?>">
<?php echo $api['api_name']; ?> — <?php echo $api['base_url']; ?>
</option>
<?php } ?>
</select>

<button type="submit">Generate API Key</button>
</form>

<?php if ($generated_key != "") { ?>
<div class="key-box">
<p>Your API Key:</p>
<div class="api-key"><?php echo $generated_key; ?></div>
</div>

<div class="hint">
Test URL:<br>
/api/gateway.php?key=<?php echo $generated_key; ?>&endpoint=posts
</div>
<?php } ?>

</div>

<div class="card">
<h3>How it works</h3>

<p style="color:#94a3b8;">
1. Select your API<br><br>
2. Generate key<br><br>
3. Use gateway endpoint<br><br>
4. Track usage in logs
</p>

<div class="key-box">
<div class="api-key">
/api/gateway.php?key=YOUR_KEY&endpoint=posts
</div>
</div>

</div>

</div>

</div>

</div>

</body>
</html>