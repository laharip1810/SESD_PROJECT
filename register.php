<?php
include('../includes/db.php');
session_start();

if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<script>alert('Email is already registered!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$email, $password, $role]);
        $_SESSION['user_id'] = $conn->lastInsertId();
        header("Location: ../index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* 🌈 Global Style */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            overflow: hidden;
        }

        /* ✨ Floating Background Circles */
        .circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.25;
            animation: float 8s ease-in-out infinite;
        }

        .circle:nth-child(1) {
            width: 250px;
            height: 250px;
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

        /* 🧊 Registration Card */
        .register-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 40px 35px;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: #fff;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #eaeaea;
            text-align: left;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 1em;
            transition: 0.3s;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.4);
        }

        /* 💚 Register Button */
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #43c67a, #28a745);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 1.05em;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(67, 198, 122, 0.4);
        }

        /* ❗Error Message */
        .error-message {
            color: #ffb3b3;
            background: rgba(255, 0, 0, 0.15);
            border: 1px solid rgba(255, 0, 0, 0.3);
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            margin-top: 10px;
        }

        /* 🔗 Links */
        .links {
            margin-top: 20px;
        }

        .links a {
            color: #ffe66d;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="circle"></div>
    <div class="circle"></div>

    <div class="register-container">
        <h2>📝 Create Your Account</h2>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" placeholder="Enter your email" required>
            
            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <button type="submit" name="register">Register</button>
        </form>

        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <div class="links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <p><a href="../index.php">← Back to Home</a></p>
        </div>
    </div>
</body>
</html>
