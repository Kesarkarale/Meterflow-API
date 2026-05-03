<?php
include "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

   $sql = "INSERT INTO users (name, email, password, role) 
        VALUES ('$name', '$email', '$password', 'admin')";

    if (mysqli_query($conn, $sql)) {
        $message = "Registration successful. Please login.";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - MeterFlow</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at top left, #1d4ed8, transparent 35%),
                        radial-gradient(circle at bottom right, #7c3aed, transparent 35%),
                        #020617;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .blob {
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            animation: float 7s ease-in-out infinite;
        }

        .blob.one {
            background: #2563eb;
            top: 8%;
            left: 8%;
        }

        .blob.two {
            background: #9333ea;
            bottom: 8%;
            right: 8%;
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-28px) scale(1.08);
            }
        }

        .auth-wrapper {
            position: relative;
            z-index: 2;
            width: 930px;
            min-height: 560px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            background: rgba(15, 23, 42, 0.78);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(22px);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.45);
            animation: slideUp 0.8s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(35px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .left-panel {
            padding: 48px;
            background: linear-gradient(135deg, rgba(37,99,235,0.35), rgba(124,58,237,0.25));
        }

        .brand {
            font-size: 30px;
            font-weight: 900;
            margin-bottom: 75px;
        }

        .brand span {
            color: #60a5fa;
        }

        .left-panel h1 {
            font-size: 42px;
            line-height: 1.1;
            margin: 0 0 18px;
        }

        .left-panel p {
            color: #cbd5e1;
            font-size: 16px;
            line-height: 1.7;
        }

        .feature-list {
            margin-top: 34px;
        }

        .feature {
            background: rgba(255,255,255,0.09);
            border: 1px solid rgba(255,255,255,0.10);
            padding: 13px 15px;
            border-radius: 16px;
            margin-bottom: 12px;
            color: #e5e7eb;
            animation: fadeIn 0.8s ease both;
        }

        .feature:nth-child(2) {
            animation-delay: 0.15s;
        }

        .feature:nth-child(3) {
            animation-delay: 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(-15px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .right-panel {
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(2, 6, 23, 0.38);
        }

        .right-panel h2 {
            font-size: 32px;
            margin: 0;
        }

        .subtitle {
            color: #94a3b8;
            margin: 10px 0 30px;
        }

        label {
            font-size: 14px;
            font-weight: 700;
            color: #cbd5e1;
        }

        input {
            width: 100%;
            padding: 15px;
            margin: 8px 0 18px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(15, 23, 42, 0.8);
            color: white;
            border-radius: 15px;
            font-size: 15px;
            outline: none;
            transition: 0.25s;
        }

        input::placeholder {
            color: #64748b;
        }

        input:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.16);
            transform: translateY(-1px);
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 900;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.28);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(124, 58, 237, 0.35);
        }

        .message {
            background: rgba(34, 197, 94, 0.13);
            border: 1px solid rgba(34, 197, 94, 0.35);
            color: #bbf7d0;
            padding: 12px;
            border-radius: 14px;
            margin-bottom: 18px;
            font-weight: 700;
            font-size: 14px;
        }

        .link {
            text-align: center;
            margin-top: 22px;
            color: #94a3b8;
        }

        .link a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 900;
        }

        .link a:hover {
            text-decoration: underline;
        }

        @media(max-width: 850px) {
            body {
                overflow: auto;
                padding: 25px 0;
            }

            .auth-wrapper {
                width: 92%;
                grid-template-columns: 1fr;
            }

            .left-panel {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="blob one"></div>
<div class="blob two"></div>

<div class="auth-wrapper">

    <div class="left-panel">
        <div class="brand">Meter<span>Flow</span></div>

        <h1>Start your API billing workspace today.</h1>
        <p>
            Create APIs, generate secure keys, track every request,
            and calculate usage-based billing like a real SaaS platform.
        </p>

        <div class="feature-list">
            <div class="feature">✓ Developer-ready API management</div>
            <div class="feature">✓ Gateway usage tracking and logs</div>
            <div class="feature">✓ Billing dashboard for SaaS projects</div>
        </div>
    </div>

    <div class="right-panel">
        <h2>Create account</h2>
        <p class="subtitle">Set up your MeterFlow workspace.</p>

        <?php if ($message != "") { ?>
            <div class="message"><?php echo $message; ?></div>
        <?php } ?>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter your full name" required>

            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Create a password" required>

            <button type="submit">Create Account</button>
        </form>

        <div class="link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>

</div>

</body>
</html>