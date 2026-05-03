<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$month_year = date("F Y");

$free_limit = 3;          // demo testing
$price_per_request = 0.5; // amount every extra request

$query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM usage_logs WHERE user_id='$user_id'");
$total_requests = mysqli_fetch_assoc($query)["total"];

$extra = max(0, $total_requests - $free_limit);
$amount = $extra * $price_per_request;

// Razorpay minimum amount ₹1
$payable_amount = ($amount > 0 && $amount < 1) ? 1 : $amount;
?>

<!DOCTYPE html>
<html>
<head>
<title>Billing - MeterFlow</title>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>
*{
box-sizing:border-box;
}
body
{
margin:0;
font-family:"Segoe UI",Arial;
background:#020617;
color:white;
}

.layout{
display:flex;
min-height:100vh;
}

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

.grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-bottom:25px;
}

.card,.summary{
background:rgba(15,23,42,0.7);
padding:22px;
border-radius:18px;
border:1px solid rgba(255,255,255,0.1);
}

.card h3{
margin:0;
font-size:14px;
color:#94a3b8;
}

.card p{
font-size:26px;
font-weight:900;
margin-top:10px;
}

.summary p{
font-size:16px;
color:#cbd5e1;
}

.amount{
font-size:34px;
font-weight:900;color:#22c55e;}
.btn,.pay-btn{display:inline-block;padding:12px 18px;border-radius:12px;font-weight:900;text-decoration:none;border:none;cursor:pointer;transition:0.25s;}
.btn{background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;}
.pay-btn{background:linear-gradient(135deg,#16a34a,#22c55e);color:white;margin-top:10px;}
.btn:hover,.pay-btn:hover{transform:translateY(-2px);}
.note{color:#facc15;font-size:14px;}
@media(max-width:900px){.layout{flex-direction:column}.sidebar{width:100%}.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.grid{grid-template-columns:1fr}.main{padding:18px}}
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
    <a href="../logs/usage_logs.php">Usage Logs</a>
    <a class="active" href="calculate.php">Billing</a>
    <a href="../admin/index.php">Admin Panel</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

<div class="header">
		<a class="plan-icon" href="../billing/plans.php" title="View Plans">✦</a>
    <h2>Billing Summary 💰</h2>
    <p>Your usage-based billing overview for this month.</p>
</div>

<div class="grid">
    <div class="card"><h3>Month</h3><p><?php echo $month_year; ?></p></div>
    <div class="card"><h3>Total Requests</h3><p><?php echo $total_requests; ?></p></div>
    <div class="card"><h3>Free Limit</h3><p><?php echo $free_limit; ?></p></div>
    <div class="card"><h3>Price / Extra Request</h3><p>₹<?php echo $price_per_request; ?></p></div>
</div>

<div class="summary">
    <p><b>Total Usage:</b> <?php echo $total_requests; ?> requests</p>
    <p><b>Free Usage:</b> <?php echo $free_limit; ?> requests</p>
    <p><b>Extra Usage:</b> <?php echo $extra; ?> requests</p>

    <h2 class="amount">₹<?php echo $amount; ?></h2>

    <?php if($amount > 0){ ?>
        <?php if($amount < 1){ ?>
            <p class="note">Razorpay minimum payment is ₹1, so payable amount is ₹1.</p>
        <?php } ?>
        <button class="pay-btn" onclick="payNow()">Pay Now</button>
    <?php } else { ?>
        <p style="color:#22c55e;font-weight:bold;">No payment required ✅</p>
    <?php } ?>

    <br><br>
    <a class="btn" href="../dashboard/index.php">Back to Dashboard</a>
    <a class="btn" href="plans.php">View Plans</a>
</div>

</div>
</div>

 <script>
function payNow(){
    fetch("../payments/create_order.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "amount=<?php echo $payable_amount; ?>"
    })
    .then(res => res.json())
    .then(data => {
        if(data.error){
            alert(data.error);
            return;
        }

        const options = {
            key: data.key,
            amount: data.amount,
            currency: "INR",
            name: "MeterFlow",
            description: "Usage Billing Payment",
            order_id: data.order_id,

            handler: function(response){
                fetch("../payments/verify_payment.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body:
                        "razorpay_order_id=" + encodeURIComponent(response.razorpay_order_id) +
                        "&razorpay_payment_id=" + encodeURIComponent(response.razorpay_payment_id) +
                        "&razorpay_signature=" + encodeURIComponent(response.razorpay_signature)
                })
                .then(res => res.json())
                .then(result => {
                    if(result.success){
                        alert("Payment successful ✅");
                        window.location.reload();
                    } else {
                        alert("Payment verification failed ❌");
                    }
                });
            },

            modal: {
                ondismiss: function(){
                    alert("Payment cancelled");
                }
            },

            theme: {
                color: "#7c3aed"
            }
        };

        const rzp = new Razorpay(options);

        rzp.on("payment.failed", function(response){
            alert("Payment failed ❌");
        });

        rzp.open();
    });
}
</script>

</body>
</html>