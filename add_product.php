<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_require_admin();

// Check Admin Access
$categories = ayurora_product_categories();
$message = '';
$error = '';

if (isset($_POST['submit'])) {
    $name = ayurora_clean_text($_POST['name'] ?? '', 150);
    $category = trim($_POST['category'] ?? '');
    $description = ayurora_clean_multiline_text($_POST['description'] ?? '', 3000);
    $price = ayurora_decimal_input($_POST['price'] ?? null, 0.01, 1000000);
    $stock_quantity = ayurora_int_input($_POST['stock_quantity'] ?? null, 0, 100000);
    $unique_name = ayurora_validate_uploaded_image($_FILES['image'] ?? null, $error);

    if ($name === null) {
        $error = 'Please enter a valid product name under 150 characters.';
    } elseif (!in_array($category, $categories, true)) {
        $error = 'Please choose a valid category.';
    } elseif ($description === null) {
        $error = 'Please enter a product description under 3000 characters.';
    } elseif ($price === null) {
        $error = 'Price must be between LKR 0.01 and LKR 1,000,000.';
    } elseif ($stock_quantity === null) {
        $error = 'Stock quantity must be between 0 and 100,000.';
    }

    if (!$error) {
        $target_file = 'uploads/' . $unique_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare('INSERT INTO products (name, category, description, price, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssdis', $name, $category, $description, $price, $stock_quantity, $unique_name);

            if ($stmt->execute()) {
                $message = 'The product has been added.';
            } else {
                $error = 'Could not add product. Please try again.';
            }

            $stmt->close();
        } else {
            $error = 'Sorry, there was an error uploading your file.';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container" style="max-width: 600px;">
    <h2 class="section-title">Add New Product</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category" class="form-control" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($_POST['category'] ?? '') === $category ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label>Price (LKR)</label>
            <input type="number" step="0.01" min="0.01" max="1000000" name="price" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" min="0" max="100000" step="1" name="stock_quantity" class="form-control" value="25" required>
        </div>
        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary" style="width: 100%;">Add Product</button>
        <div style="margin-top: 1rem; text-align: center;">
            <a href="admin.php" class="btn-outline">Back to Dashboard</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
