<?php
session_start();
include 'includes/db.php'; // Include the database connection

$error = ''; // Initialize error variable

// Add to cart handler
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: pages/login.php");
        exit();
    }

    try {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $user_id = (int)$_SESSION['user_id'];

        if ($product_id <= 0) {
            throw new Exception('Invalid product selected.');
        }

        // check if product still exists
        $chk = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $chk->execute([$product_id]);
        if ($chk->rowCount() === 0) {
            throw new Exception('Product not found (may have been removed).');
        }

        // update existing cart row or insert new
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $newQty = $row['quantity'] + $quantity;
            $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $upd->execute([$newQty, $row['id']]);
        } else {
            $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $ins->execute([$user_id, $product_id, $quantity]);
        }

        header("Location: pages/cart.php");
        exit();
    } catch (Exception $e) {
        $error = 'Add to cart error: ' . $e->getMessage();
    }
}

// Fetch products from the database
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>

    <!-- 🧭 Modern Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* 🎨 Global Reset & Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            color: #333;
        }

        /* 🌈 Header Styling */
        header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 20px 40px;
            color: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        nav a:hover {
            text-decoration: underline;
            opacity: 0.9;
        }

        .cart-icon {
            width: 20px;
            vertical-align: middle;
            margin-right: 5px;
        }

        /* 🛍️ Main Layout */
        .main-container {
            padding: 40px 5%;
        }

        main h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            color: #333;
        }

        /* 🧊 Product Grid */
        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            justify-content: center;
        }

        /* 💎 Product Card */
        .product {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            backdrop-filter: blur(6px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.25);
        }

        .product img {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .product h3 {
            color: #444;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .product p {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        /* 🧮 Form Inputs */
        .product-actions {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        input[type="number"] {
            width: 70px;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1em;
            text-align: center;
            transition: 0.2s;
        }

        input[type="number"]:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        /* 🛒 Add to Cart Button */
        .btn-add-cart {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 10px 18px;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            background: linear-gradient(135deg, #5a6fdc, #6a3d98);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);
        }

        /* ⚠️ Error Message */
        div[style*="color:red"] {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            font-weight: 600;
            color: #d63031 !important;
            background: #ffeaea;
            border: 1px solid #ffb3b3;
            border-radius: 6px;
        }

        /* 🦶 Footer */
        footer {
            background: #222;
            color: #ccc;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            margin-top: 40px;
        }

        footer p {
            margin: 0;
        }

        /* 📱 Responsive Fixes */
        @media (max-width: 600px) {
            header {
                padding: 20px;
            }
            nav ul {
                gap: 10px;
            }
            .product-actions {
                flex-direction: column;
            }
            input[type="number"] {
                width: 100%;
            }
            .btn-add-cart {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php if ($error): ?>
        <div><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <header>
        <div class="header-container">
            <h1>🛍️ Our Online Store</h1>
            <nav>
                <ul>
                    <li><a href="pages/login.php">Login</a></li>
                    <li><a href="pages/register.php">Register</a></li>
                    <li><a href="pages/cart.php" class="cart-link"><img src="images/cart-icon.png" alt="Cart" class="cart-icon"> Cart</a></li>
                    <li><a href="pages/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-container">
        <main>
            <h2>✨ Explore Our Products</h2>
            <div class="product-list">
                <?php if (empty($products)) : ?>
                    <p style="text-align:center;">No products available.</p>
                <?php else : ?>
                    <?php foreach ($products as $product) : ?>
                        <div class="product">
                            <div>
                                <?php if (!empty($product['image'])) : ?>
                                    <img src="images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                                <?php endif; ?>
                                <h3><?= htmlspecialchars($product['name']); ?></h3>
                                <p><strong>Price:</strong> $<?= number_format($product['price'], 2); ?></p>
                                <p><?= htmlspecialchars($product['description']); ?></p>
                            </div>
                            <form method="POST" class="product-actions">
                                <input type="hidden" name="product_id" value="<?= (int)$product['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="999">
                                <button type="submit" name="add_to_cart" class="btn-add-cart">Add to Cart</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>
