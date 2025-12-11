<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Poppins", Arial, sans-serif;
            background: linear-gradient(135deg, #74b9ff, #00cec9);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: #fff;
            width: 90%;
            max-width: 700px;
            padding: 40px 30px;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            color: #222;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
            letter-spacing: 0.5px;
        }

        nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        nav a {
            text-decoration: none;
            padding: 14px 30px;
            background: linear-gradient(135deg, #00cec9, #0984e3);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        nav a:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
        }

        nav a.logout {
            background: linear-gradient(135deg, #ff6b6b, #e84118);
        }

        nav a.logout:hover {
            background: linear-gradient(135deg, #e84118, #ff7675);
        }

        footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #fff;
            opacity: 0.9;
        }

        footer p {
            background: rgba(0, 0, 0, 0.15);
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
        }

        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 30px 20px;
            }

            nav a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <nav>
            <a href="add_product.php">Add Product</a>
            <a href="manage_products.php">Manage Products</a>
            <a href="logout.php" class="logout">Logout</a>
        </nav>
    </div>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Admin Dashboard</p>
    </footer>
</body>
</html>
