<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$error = '';
$success = '';
// Default values for form
$product = [
    'id' => 0,
    'name' => '',
    'price' => '',
    'description' => '',
    'image' => ''
];

// If editing, load product
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    if ($edit_id > 0) {
        try {
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$edit_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $product = $row;
            } else {
                $error = 'Product not found.';
            }
        } catch (Exception $e) {
            $error = 'Error loading product: ' . $e->getMessage();
        }
    }
}

// Handle form submit for add or update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save_product']))) {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($name === '' || $price === '') {
        $error = 'Please provide product name and price.';
    } elseif (!is_numeric($price)) {
        $error = 'Price must be a number.';
    } else {
        // Handle image upload if provided
        $uploaded_filename = '';
        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array(strtolower($ext), $allowed)) {
                    $error = 'Invalid image type. Allowed: ' . implode(', ', $allowed);
                } else {
                    $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = __DIR__ . '/../images/' . $newName;
                    if (!is_dir(dirname($dest))) {
                        mkdir(dirname($dest), 0755, true);
                    }
                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        $error = 'Failed to move uploaded image.';
                    } else {
                        $uploaded_filename = $newName;
                    }
                }
            } else {
                $error = 'Image upload error.';
            }
        }

        if ($error === '') {
            try {
                if ($product_id > 0) {
                    // UPDATE
                    if ($uploaded_filename !== '') {
                        // get old image to delete
                        $stmtOld = $conn->prepare("SELECT image FROM products WHERE id = ?");
                        $stmtOld->execute([$product_id]);
                        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);
                        if ($old && !empty($old['image'])) {
                            $oldPath = __DIR__ . '/../images/' . $old['image'];
                            if (is_file($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE id = ?");
                        $stmt->execute([$name, $price, $description, $uploaded_filename, $product_id]);
                    } else {
                        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ? WHERE id = ?");
                        $stmt->execute([$name, $price, $description, $product_id]);
                    }
                    $success = 'Product updated successfully.';
                    header("Location: manage_products.php");
                    exit();
                } else {
                    // INSERT
                    $img = $uploaded_filename ?: '';
                    $stmt = $conn->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $price, $description, $img]);
                    $success = 'Product added successfully.';
                    header("Location: manage_products.php");
                    exit();
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['id'] ? 'Edit Product' : 'Add Product' ?></title>
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
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .box {
            width: 100%;
            max-width: 700px;
            background: #ffffff;
            border-radius: 16px;
            padding: 35px 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.7s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            font-size: 26px;
            color: #222;
            font-weight: 600;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #444;
            margin: 10px 0 6px;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
            background-color: #fafafa;
        }

        input:focus,
        textarea:focus {
            border-color: #00cec9;
            box-shadow: 0 0 0 3px rgba(0, 206, 201, 0.2);
            background-color: #fff;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            background: linear-gradient(135deg, #00cec9, #0984e3);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .msg {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 500;
        }

        .error {
            background: #ffeaea;
            color: #c70000;
            border: 1px solid #f5c2c7;
        }

        .success {
            background: #e7f9ed;
            color: #187a2f;
            border: 1px solid #b8f1c5;
        }

        .preview {
            display: block;
            max-width: 180px;
            margin-top: 10px;
            border-radius: 10px;
            border: 1px solid #eee;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .actions {
            margin-top: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .actions a {
            color: #444;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .actions a:hover {
            color: #0984e3;
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .box {
                padding: 25px 20px;
            }

            h2 {
                font-size: 22px;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="box">
        <h2><?= $product['id'] ? 'Edit Product' : 'Add Product' ?></h2>

        <?php if ($error): ?>
            <div class="msg error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="msg success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

            <label for="name">Product Name</label>
            <input id="name" name="name" type="text" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label for="price">Price</label>
            <input id="price" name="price" type="number" step="0.01" value="<?= htmlspecialchars($product['price']) ?>" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Write a short product description..."><?= htmlspecialchars($product['description']) ?></textarea>

            <label for="image">Image <?= $product['image'] ? '(leave blank to keep current)' : '' ?></label>
            <input id="image" name="image" type="file" accept="image/*">
            
            <?php if (!empty($product['image']) && is_file(__DIR__ . '/../images/' . $product['image'])): ?>
                <img class="preview" src="../images/<?= htmlspecialchars($product['image']) ?>" alt="current image">
            <?php endif; ?>

            <div class="actions">
                <button type="submit" name="save_product"><?= $product['id'] ? 'Update Product' : 'Add Product' ?></button>
                <a href="manage_products.php">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
