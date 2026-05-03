<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$apis = mysqli_query($conn, "SELECT * FROM apis WHERE user_id='$user_id'");
$response = "";
$pretty_response = "";
$error = "";
$test_url = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_id = $_POST["api_id"];
    $endpoint = trim($_POST["endpoint"]);

    $keyQuery = mysqli_query($conn, "
        SELECT api_keys.* 
        FROM api_keys 
        WHERE api_id='$api_id' 
        AND user_id='$user_id' 
        AND status='active' 
        LIMIT 1
    ");

    $keyData = mysqli_fetch_assoc($keyQuery);

    if ($keyData) {
        $test_url = "http://localhost/meterflow/api/gateway.php?key=" . $keyData["api_key"] . "&endpoint=" . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
        } else {
            $decoded = json_decode($response, true);

            if ($decoded !== null) {
                $pretty_response = json_encode($decoded, JSON_PRETTY_PRINT);
            } else {
                $pretty_response = $response;
            }
        }

        curl_close($ch);
    } else {
        $error = "No active API key found for this API. Please generate an API key first.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Test API - MeterFlow</title>

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

.grid{
display:grid;
grid-template-columns:0.9fr 1.1fr;
gap:24px;
align-items:start;
}

.card{
background:rgba(15,23,42,0.7);
padding:26px;
border-radius:18px;
backdrop-filter:blur(12px);
border:1px solid rgba(255,255,255,0.1);
box-shadow:0 20px 40px rgba(0,0,0,0.25);
animation:fadeUp 0.8s ease;
}

.card h3{
margin-top:0;
font-size:22px;
}

.card p{
color:#94a3b8;
line-height:1.6;
}

label{
display:block;
font-size:14px;
font-weight:800;
color:#cbd5e1;
margin-bottom:8px;
}

select,
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

select:focus,
input:focus{
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

.copy-btn{
width:auto;
padding:10px 14px;
font-size:14px;
margin-bottom:15px;
}

.msg-error{
background:rgba(239,68,68,0.13);
border:1px solid rgba(239,68,68,0.35);
color:#fecaca;
padding:13px;
border-radius:14px;
font-weight:800;
margin-bottom:18px;
}

.url-box{
background:#020617;
color:#60a5fa;
font-family:Consolas,monospace;
padding:14px;
border-radius:14px;
word-break:break-all;
border:1px solid rgba(96,165,250,0.25);
margin-bottom:12px;
}

pre{
background:#020617;
color:#22c55e;
padding:18px;
border-radius:16px;
border:1px solid rgba(34,197,94,0.25);
max-height:420px;
overflow:auto;
white-space:pre-wrap;
word-wrap:break-word;
font-family:Consolas,monospace;
font-size:14px;
}

.empty{
color:#94a3b8;
}

@media(max-width:900px){
.layout{flex-direction:column;}
.sidebar{width:100%;}
.grid{grid-template-columns:1fr;}
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
    <a href="create_api.php">Create API</a>
    <a href="generate_key.php">Generate API Key</a>
    <a href="manage_keys.php">Manage Keys</a>
    <a class="active" href="test_api.php">Test API</a>
    <a href="../logs/usage_logs.php">Usage Logs</a>
    <a href="../billing/calculate.php">Billing</a>
    <a href="../admin/index.php">Admin Panel</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

    <div class="header">
<a class="plan-icon" href="../billing/plans.php" title="Plans">✦</a>
        <h2>Test API Playground 🧪</h2>
        <p>Test your APIs directly through the MeterFlow gateway and view live responses.</p>
    </div>

    <div class="grid">

        <div class="card">
            <h3>Request Builder</h3>
            <p>Select an API, enter endpoint, and send a gateway request.</p>

            <?php if ($error != "") { ?>
                <div class="msg-error"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                <label>Select API</label>
                <select name="api_id" required>
                    <option value="">Choose API</option>
                    <?php while ($api = mysqli_fetch_assoc($apis)) { ?>
                        <option value="<?php echo $api['id']; ?>">
                            <?php echo $api['api_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Endpoint</label>
                <input type="text" name="endpoint" placeholder="e.g. posts / users / products" required>

                <button type="submit">Send Test Request</button>
            </form>
        </div>

        <div class="card">
            <h3>Response Viewer</h3>

            <?php if ($test_url != "") { ?>
                <p>Gateway URL:</p>
                <div class="url-box" id="gatewayUrl"><?php echo $test_url; ?></div>
                <button class="copy-btn" onclick="copyUrl()">Copy Gateway URL</button>
            <?php } ?>

            <?php if ($pretty_response != "") { ?>
                <pre><?php echo htmlspecialchars($pretty_response); ?></pre>
            <?php } else { ?>
                <p class="empty">Response will appear here after testing an API.</p>
            <?php } ?>
        </div>

    </div>

</div>

</div>

<script>
function copyUrl(){
    const text = document.getElementById("gatewayUrl").innerText;
    navigator.clipboard.writeText(text);
    alert("Gateway URL copied!");
}
</script>

</body>
</html>