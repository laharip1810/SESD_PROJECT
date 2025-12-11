<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>
<?php
include '../includes/db.php';
// Add product delete handler
if (isset($_POST['delete_product'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if ($product_id > 0) {
        try {
            // get image filename to delete file
            $s = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $s->execute([$product_id]);
            $row = $s->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['image'])) {
                $imgPath = __DIR__ . '/../images/' . $row['image'];
                if (is_file($imgPath)) {
                    @unlink($imgPath);
                }
            }

            // delete product
            $del = $conn->prepare("DELETE FROM products WHERE id = ?");
            $del->execute([$product_id]);

            // remove any cart rows referencing this product
            $delCart = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
            $delCart->execute([$product_id]);

            header("Location: manage_products.php");
            exit();
        } catch (Exception $e) {
            $error = 'Delete error: ' . $e->getMessage();
        }
    }
}

$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
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
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            background: #ffffff;
            padding: 30px 35px;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.7s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            color: #222;
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
        }

        th {
            background: linear-gradient(135deg, #00cec9, #0984e3);
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 15px;
        }

        tr {
            transition: background-color 0.2s ease;
        }

        tr:nth-child(even) {
            background-color: #f9fbfc;
        }

        tr:hover {
            background-color: #e8f8ff;
        }

        td {
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        td img {
            width: 60px;
            height: auto;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .actions a,
        .actions button {
            padding: 6px 12px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
        }

        .actions a {
            background: #0984e3;
            color: #fff;
        }

        .actions a:hover {
            background: #00cec9;
        }

        .actions button {
            background: #ff6b6b;
            color: #fff;
            border: none;
        }

        .actions button:hover {
            background: #e84118;
        }

        .btn-back {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            font-size: 15px;
            font-weight: 600;
            background: linear-gradient(135deg, #00cec9, #0984e3);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .error {
            background: #ffeaea;
            color: #c70000;
            border: 1px solid #f5c2c7;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                display: none;
            }

            td {
                padding: 10px;
                border: none;
                position: relative;
                padding-left: 50%;
            }

            td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                top: 50%;
                transform: translateY(-50%);
                font-weight: 600;
                color: #333;
            }

            td img {
                width: 50px;
            }

            .actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h2>Manage Products</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Description</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $product) : ?>
            <tr>
                <td data-label="ID"><?= $product['id']; ?></td>
                <td data-label="Name"><?= htmlspecialchars($product['name']); ?></td>
                <td data-label="Price">$<?= number_format($product['price'], 2); ?></td>
                <td data-label="Description"><?= htmlspecialchars($product['description']); ?></td>
                <td data-label="Image">
                    <img src="../images/<?= htmlspecialchars($product['image']); ?>" alt="Product Image">
                </td>
                <td class="actions" data-label="Actions">
                    <a href="edit_product.php?id=<?= $product['id']; ?>">Edit</a>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this product?');">
                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                        <button type="submit" name="delete_product">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>

</body>
</html>
