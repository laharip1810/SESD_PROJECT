<?php
include '../includes/db.php';
session_start();

$error_message = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        try {
            // Check USERS table (NOT admins table)
            $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header("Location: ../index.php");
                exit();
            } else {
                $error_message = '❌ Invalid email or password.';
            }
        } catch (Exception $e) {
            $error_message = '❌ Login error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        /* 🌌 Background Gradient */
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Poppins", sans-serif;
            background: radial-gradient(circle at top left, #667eea, #764ba2);
            overflow: hidden;
        }

        /* ✨ Floating Animation for background circles */
        .circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.25;
            animation: float 10s infinite ease-in-out;
        }
        .circle:nth-child(1) {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            background: #667eea;
        }
        .circle:nth-child(2) {
            width: 300px;
            height: 300px;
            bottom: 15%;
            right: 10%;
            background: #764ba2;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }

        /* 🧊 Glass Card */
        .login-container {
            position: relative;
            width: 100%;
            max-width: 420px;
            padding: 45px 40px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(15px);
            color: #fff;
            z-index: 2;
        }

        /* 🧾 Title */
        h2 {
            text-align: center;
            font-size: 1.8em;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 25px;
            color: #fff;
        }

        /* ⚠️ Error Message */
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
        }
        .error {
            background-color: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ffb3b3;
        }

        /* 🧍 Input Fields */
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #ddd;
            font-size: 0.9em;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1em;
            transition: 0.3s;
            outline: none;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.7);
        }

        /* 🔘 Button */
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1em;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
        }

        /* 🔗 Links */
        .links {
            text-align: center;
            margin-top: 25px;
            font-size: 0.95em;
        }
        .links a {
            color: #a6b1ff;
            text-decoration: none;
            font-weight: 600;
            margin: 0 8px;
            transition: 0.2s;
        }
        .links a:hover {
            text-decoration: underline;
            color: #fff;
        }

        /* 🪞 Subtle Fade In Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-container {
            animation: fadeIn 0.8s ease-in-out;
        }

        /* 📱 Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 35px 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Background Circles -->
    <div class="circle"></div>
    <div class="circle"></div>

    <div class="login-container">
        <h2>🔐 User Login</h2>

        <?php if ($error_message): ?>
            <div class="message error"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="email">Email</label>
            <input type="email" name="email" id="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" name="login">Login</button>
        </form>

        <div class="links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="../index.php">← Back to Home</a></p>
        </div>
    </div>
</body>
</html>
