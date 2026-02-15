<?php
include 'includes/db.php';
include 'includes/header.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $sql = "UPDATE products SET is_deleted = 1 WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product deleted successfully'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Error deleting product');</script>";
    }
}

// Handle Reset Admin (Merged from fix_admin.php)
if (isset($_POST['reset_admin'])) {
    $email = 'admin@ayurveda.com';
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $name = 'Admin User';
    $role = 'admin';

    // Check if admin exists
    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // Update existing admin
        $sql = "UPDATE users SET password = '$hashed_password', role = 'admin' WHERE email = '$email'";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Admin password reset successfully to: $password'); window.location.href='admin.php';</script>";
        } else {
            echo "<script>alert('Error updating admin: " . $conn->error . "');</script>";
        }
    } else {
        // Create new admin
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";
        if ($conn->query($sql) === TRUE) {
             echo "<script>alert('Admin user created successfully. Email: $email, Password: $password'); window.location.href='admin.php';</script>";
        } else {
             echo "<script>alert('Error creating admin: " . $conn->error . "');</script>";
        }
    }
}
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="section-title" style="margin-bottom: 0;">Admin Dashboard</h2>
        <div style="display: flex; gap: 1rem;">
            <form method="POST" action="" onsubmit="return confirm('Reset Admin Credentials to Default? (admin@ayurveda.com / admin123)');">
                <button type="submit" name="reset_admin" class="btn-outline" style="border-color: #f39c12; color: #f39c12;"><i class="fas fa-tools"></i> Reset Admin</button>
            </form>
            <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Product</a>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
            
                $sql = "SELECT * FROM products WHERE is_deleted = 0 ORDER BY id DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td><img src='uploads/" . htmlspecialchars($row['image']) . "' width='50' style='border-radius: 4px;'></td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                        echo "<td>LKR " . number_format($row['price'], 2) . "</td>";
                        echo "<td>
                                <a href='admin.php?delete=" . $row['id'] . "' class='btn-outline' style='border-color: var(--danger); color: var(--danger); padding: 0.2rem 0.5rem; font-size: 0.9rem;' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>No products found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
