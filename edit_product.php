<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_start_secure_session();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$categories = [
    'Soaps',
    'Capsules',
    'Oils & Thailas',
    'Herbal Tea & Kwath',
    'Arishta & Syrups',
    'Powders & Churnas',
    'Creams & Balms',
    'Tablets & Vati',
    'Leheyas & Pastes',
    'Hair & Skin Care',
    'Essential Oils',
    'Health Supplements',
    'Wellness Kits',
    'Other',
];

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = '';
$error = '';

if ($product_id <= 0) {
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
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $stock_quantity = isset($_POST['stock_quantity']) ? max(0, (int) $_POST['stock_quantity']) : 0;
    $image_name = $product['image'];

    if ($name === '' || $category === '' || $description === '') {
        $error = 'Name, category, and description are required.';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than zero.';
    } elseif ($stock_quantity < 0) {
        $error = 'Stock quantity cannot be negative.';
    } elseif (!in_array($category, $categories, true)) {
        $error = 'Please choose a valid category.';
    }

    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Sorry, there was an error uploading your file.';
        } elseif ($_FILES['image']['size'] > 5000000) {
            $error = 'Sorry, your file is too large.';
        } else {
            $target_dir = 'uploads/';
            $original_name = basename($_FILES['image']['name']);
            $image_file_type = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $image_check = getimagesize($_FILES['image']['tmp_name']);

            if ($image_check === false) {
                $error = 'File is not an image.';
            } elseif (!in_array($image_file_type, $allowed_types, true)) {
                $error = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
            } else {
                $new_image_name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original_name);
                $target_file = $target_dir . $new_image_name;

                if (file_exists($target_file)) {
                    $error = 'Sorry, file already exists.';
                } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_name = $new_image_name;
                } else {
                    $error = 'Sorry, there was an error uploading your file.';
                }
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
