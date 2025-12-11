<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = (int)$_SESSION['user_id'];
$message = '';

// Handle remove from cart
if (isset($_POST['remove_from_cart'])) {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    if ($cart_id > 0) {
        try {
            $del = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $del->execute([$cart_id, $user_id]);
            $message = 'Item removed from cart.';
        } catch (Exception $e) {
            $message = 'Remove error: ' . $e->getMessage();
        }
    }
}

// Handle update quantity
if (isset($_POST['update_quantity'])) {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if ($cart_id > 0) {
        try {
            $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $upd->execute([$quantity, $cart_id, $user_id]);
            $message = 'Cart updated.';
        } catch (Exception $e) {
            $message = 'Update error: ' . $e->getMessage();
        }
    }
}

// Fetch cart items with product details
try {
    $stmt = $conn->prepare("
        SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $cart_items = [];
    $message = 'Error fetching cart: ' . $e->getMessage();
}

// Calculate total
$total_cost = 0;
foreach ($cart_items as $item) {
    $total_cost += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>

    <!-- 🧭 Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* 🌈 Global Base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 10px;
            color: #333;
        }

        /* 🧊 Cart Container */
        .cart-container {
            width: 100%;
            max-width: 1050px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 35px 25px;
            backdrop-filter: blur(20px);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
            color: #fff;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 25px;
        }

        /* ✅ Message Styles */
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }
        .message {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.4);
            color: #d4edda;
        }
        .message.error {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.4);
            color: #f8d7da;
        }

        /* 📋 Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
            margin-bottom: 30px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        th {
            font-weight: 600;
            background: rgba(255, 255, 255, 0.1);
        }

        td {
            vertical-align: middle;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        /* 🔢 Inputs */
        input[type="number"] {
            width: 70px;
            padding: 8px;
            border: none;
            border-radius: 8px;
            text-align: center;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-weight: 600;
        }

        input[type="number"]:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }

        /* 🔘 Buttons */
        button {
            border: none;
            cursor: pointer;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.9em;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-update {
            background: linear-gradient(135deg, #4e9eff, #667eea);
            color: white;
        }

        .btn-update:hover {
            background: linear-gradient(135deg, #5aa0ff, #6f85f0);
            transform: translateY(-2px);
        }

        .btn-remove {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-remove:hover {
            background: linear-gradient(135deg, #ff6f61, #e74c3c);
            transform: translateY(-2px);
        }

        /* 💰 Summary Box */
        .cart-summary {
            text-align: right;
            padding: 20px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            font-size: 1.2em;
            font-weight: 600;
            color: #fff;
            box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.15);
        }

        .cart-summary strong {
            font-size: 1.4em;
            color: #ffe66d;
        }

        /* 💳 Checkout Button */
        .checkout-btn {
            background: linear-gradient(135deg, #28a745, #43c67a);
            color: white;
            padding: 14px 40px;
            font-size: 1em;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            float: right;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            background: linear-gradient(135deg, #43c67a, #28a745);
            box-shadow: 0 8px 20px rgba(67, 198, 122, 0.4);
            transform: translateY(-2px);
        }

        /* 🛒 Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 60px;
            color: #f1f1f1;
            font-size: 1.1em;
        }

        .empty-cart a {
            color: #ffe66d;
            text-decoration: none;
            font-weight: 700;
        }

        .empty-cart a:hover {
            text-decoration: underline;
        }

        /* 📱 Responsive Design */
        @media (max-width: 700px) {
            table thead {
                display: none;
            }

            table, tbody, tr, td {
                display: block;
                width: 100%;
            }

            tr {
                margin-bottom: 20px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 10px;
                padding: 10px;
            }

            td {
                border: none;
                padding: 10px;
                text-align: right;
                position: relative;
            }

            td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                font-weight: 600;
                color: #ffe66d;
                text-align: left;
            }

            .checkout-btn {
                width: 100%;
                float: none;
                text-align: center;
            }

            .cart-summary {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h2>🛍️ Your Shopping Cart</h2>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Error') ? 'error' : ''; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty 🛒</p>
                <a href="../index.php">← Continue Shopping</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td data-label="Product">
                            <div class="product-info">
                                <img src="../images/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" class="product-image">
                                <span><?= htmlspecialchars($item['name']); ?></span>
                            </div>
                        </td>
                        <td data-label="Price">$<?= number_format($item['price'], 2); ?></td>
                        <td data-label="Quantity">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id']; ?>">
                                <input type="number" name="quantity" value="<?= (int)$item['quantity']; ?>" min="1" max="999">
                                <button type="submit" name="update_quantity" class="btn-update">Update</button>
                            </form>
                        </td>
                        <td data-label="Subtotal">$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                        <td data-label="Actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id']; ?>">
                                <button type="submit" name="remove_from_cart" class="btn-remove" onclick="return confirm('Remove this item?');">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="overflow: auto;">
                <div class="cart-summary">
                    Total: <strong>$<?= number_format($total_cost, 2); ?></strong>
                </div>
                <button class="checkout-btn">Proceed to Checkout</button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
