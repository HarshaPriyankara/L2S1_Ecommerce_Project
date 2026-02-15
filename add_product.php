<?php
include 'includes/db.php';
include 'includes/header.php';

// Check Admin Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$message = '';
$error = '';

if (isset($_POST['submit'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $_POST['price'];
    
    // Image Upload
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    // Generate unique name to prevent overwrite
    $unique_name = time() . '_' . $image_name;
    $target_file = $target_dir . $unique_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $error = "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $error = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (5MB limit)
    if ($_FILES["image"]["size"] > 5000000) {
        $error = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        // if everything is not ok, error is already set
    } else {
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $category = $_POST['category'];
            $sql = "INSERT INTO products (name, category, description, price, image) VALUES ('$name', '$category', '$description', '$price', '$unique_name')";
            
            if ($conn->query($sql) === TRUE) {
                $message = "The product has been added.";
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<div class="auth-container" style="max-width: 600px;">
    <h2 class="section-title">Add New Product</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category" class="form-control" required>
                <option value="Soaps">Soaps</option>
                <option value="Capsules">Capsules</option>
                <option value="Oils">Oils</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label>Price (LKR)</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary" style="width: 100%;">Add Product</button>
        <div style="margin-top: 1rem; text-align: center;">
            <a href="admin.php" class="btn-outline">Back to Dashboard</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
