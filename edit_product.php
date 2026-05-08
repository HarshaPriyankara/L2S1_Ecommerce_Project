<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_require_admin();

$categories = ayurora_product_categories();

$product_id = ayurora_int_input($_GET['id'] ?? null);
$message = '';
$error = '';

if ($product_id === null) {
    header('Location: admin.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM products WHERE id = ? AND is_deleted = 0 LIMIT 1');
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: admin.php');
    exit();
}

if (isset($_POST['update_product'])) {
    $name = ayurora_clean_text($_POST['name'] ?? '', 150);
    $category = trim($_POST['category'] ?? '');
    $description = ayurora_clean_multiline_text($_POST['description'] ?? '', 3000);
    $price = ayurora_decimal_input($_POST['price'] ?? null, 0.01, 1000000);
    $stock_quantity = ayurora_int_input($_POST['stock_quantity'] ?? null, 0, 100000);
    $image_name = $product['image'];

    if ($name === null) {
        $error = 'Please enter a valid product name under 150 characters.';
    } elseif ($description === null) {
        $error = 'Please enter a product description under 3000 characters.';
    } elseif ($price === null) {
        $error = 'Price must be between LKR 0.01 and LKR 1,000,000.';
    } elseif ($stock_quantity === null) {
        $error = 'Stock quantity must be between 0 and 100,000.';
    } elseif (!in_array($category, $categories, true)) {
        $error = 'Please choose a valid category.';
    }

    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $new_image_name = ayurora_validate_uploaded_image($_FILES['image'], $error);

        if (!$error) {
            $target_file = 'uploads/' . $new_image_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_name = $new_image_name;
            } else {
                $error = 'Sorry, there was an error uploading your file.';
            }
        }
    }

    if (!$error) {
        $update_stmt = $conn->prepare('UPDATE products SET name = ?, category = ?, description = ?, price = ?, stock_quantity = ?, image = ? WHERE id = ?');
        $update_stmt->bind_param('sssdisi', $name, $category, $description, $price, $stock_quantity, $image_name, $product_id);

        if ($update_stmt->execute()) {
            $message = 'Product updated successfully.';
            $product['name'] = $name;
            $product['category'] = $category;
            $product['description'] = $description;
            $product['price'] = $price;
            $product['stock_quantity'] = $stock_quantity;
            $product['image'] = $image_name;
        } else {
            $error = 'Could not update product. Please try again.';
        }

        $update_stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="auth-container" style="max-width: 680px;">
    <h2 class="section-title">Edit Product</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="edit_product.php?id=<?php echo (int) $product_id; ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Category</label>
            <select name="category" class="form-control" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $product['category'] === $category ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Price (LKR)</label>
            <input type="number" step="0.01" min="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>

        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" step="1" min="0" name="stock_quantity" class="form-control" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
        </div>

        <div class="form-group">
            <label>Current Image</label>
            <div class="edit-product-image">
                <img src="<?php echo htmlspecialchars(product_image_path($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <span><?php echo htmlspecialchars($product['image'] ?: 'No image selected'); ?></span>
            </div>
        </div>

        <div class="form-group">
            <label>Replace Image</label>
            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
            <p class="auth-helper" style="text-align: left; margin: 0.5rem 0 0;">Leave this empty to keep the current image.</p>
        </div>

        <button type="submit" name="update_product" class="btn btn-primary" style="width: 100%;">Update Product</button>

        <div style="margin-top: 1rem; text-align: center;">
            <a href="admin.php" class="btn-outline">Back to Dashboard</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
