<?php
include 'includes/db.php';
include 'includes/header.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$message = '';
$error = '';

function product_stock_badge($stock_quantity) {
    $stock_quantity = (int) $stock_quantity;
    $class = $stock_quantity > 0 ? 'in-stock' : 'out-stock';
    $label = $stock_quantity > 0 ? $stock_quantity . ' in stock' : 'Out of stock';

    return "<span class='stock-badge $class'>$label</span>";
}

function render_product_row($row, $is_deleted = false) {
    echo "<tr>";
    echo "<td>" . (int) $row['id'] . "</td>";
    echo "<td><img src='" . htmlspecialchars(product_image_path($row['image'])) . "' width='50' style='border-radius: 4px;'></td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
    echo "<td>LKR " . number_format((float) $row['price'], 2) . "</td>";
    echo "<td>" . product_stock_badge($row['stock_quantity']) . "</td>";
    echo "<td>";
    echo "<div class='admin-actions'>";

    if ($is_deleted) {
        echo "<a href='admin.php?restore=" . (int) $row['id'] . "#deleted-products' class='btn-outline' style='border-color: var(--success); color: var(--success); padding: 0.2rem 0.5rem; font-size: 0.9rem;' onclick='return confirm(\"Restore this product?\")'>Restore</a>";
    } else {
        echo "<a href='edit_product.php?id=" . (int) $row['id'] . "' class='btn-outline' style='padding: 0.2rem 0.5rem; font-size: 0.9rem;'>Edit</a>";
        echo "<a href='admin.php?delete=" . (int) $row['id'] . "' class='btn-outline' style='border-color: var(--danger); color: var(--danger); padding: 0.2rem 0.5rem; font-size: 0.9rem;' onclick='return confirm(\"Move this product to deleted products?\")'>Delete</a>";
    }

    echo "</div>";
    echo "</td>";
    echo "</tr>";
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare('UPDATE products SET is_deleted = 1 WHERE id = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $message = 'Product moved to deleted products.';
    } else {
        $error = 'Error deleting product.';
    }

    $stmt->close();
}

if (isset($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $stmt = $conn->prepare('UPDATE products SET is_deleted = 0 WHERE id = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $message = 'Product restored successfully.';
    } else {
        $error = 'Error restoring product.';
    }

    $stmt->close();
}

// Handle Reset Admin (Merged from fix_admin.php)
if (isset($_POST['reset_admin'])) {
    $email = 'admin@ayurora.com';
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

$summary = [
    'total_products' => 0,
    'total_users' => 0,
    'total_orders' => 0,
    'pending_orders' => 0,
    'revenue' => 0,
];

$summary_result = $conn->query("SELECT COUNT(*) AS total_products FROM products WHERE is_deleted = 0");
if ($summary_result) {
    $summary['total_products'] = (int) $summary_result->fetch_assoc()['total_products'];
}

$summary_result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
if ($summary_result) {
    $summary['total_users'] = (int) $summary_result->fetch_assoc()['total_users'];
}

$summary_result = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
if ($summary_result) {
    $summary['total_orders'] = (int) $summary_result->fetch_assoc()['total_orders'];
}

$summary_result = $conn->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE status = 'pending'");
if ($summary_result) {
    $summary['pending_orders'] = (int) $summary_result->fetch_assoc()['pending_orders'];
}

$summary_result = $conn->query("SELECT COALESCE(SUM(total_price), 0) AS revenue FROM orders WHERE status = 'completed'");
if ($summary_result) {
    $summary['revenue'] = (float) $summary_result->fetch_assoc()['revenue'];
}
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="section-title" style="margin-bottom: 0;">Admin Dashboard</h2>
        <div style="display: flex; gap: 1rem;">
            <form method="POST" action="" onsubmit="return confirm('Reset Admin Credentials to Default? (admin@ayurora.com / admin123)');">
                <button type="submit" name="reset_admin" class="btn-outline" style="border-color: #f39c12; color: #f39c12;"><i class="fas fa-tools"></i> Reset Admin</button>
            </form>
            <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Product</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-summary-grid">
        <a href="#active-products" class="admin-summary-card">
            <span class="admin-summary-icon"><i class="fas fa-boxes-stacked"></i></span>
            <div>
                <span>Total Products</span>
                <strong><?php echo number_format($summary['total_products']); ?></strong>
            </div>
        </a>
        <a href="admin_users.php" class="admin-summary-card">
            <span class="admin-summary-icon"><i class="fas fa-users"></i></span>
            <div>
                <span>Total Users</span>
                <strong><?php echo number_format($summary['total_users']); ?></strong>
            </div>
        </a>
        <a href="admin_orders.php" class="admin-summary-card">
            <span class="admin-summary-icon"><i class="fas fa-receipt"></i></span>
            <div>
                <span>Total Orders</span>
                <strong><?php echo number_format($summary['total_orders']); ?></strong>
            </div>
        </a>
        <a href="admin_orders.php" class="admin-summary-card">
            <span class="admin-summary-icon admin-summary-warning"><i class="fas fa-clock"></i></span>
            <div>
                <span>Pending Orders</span>
                <strong><?php echo number_format($summary['pending_orders']); ?></strong>
            </div>
        </a>
        <a href="admin_orders.php" class="admin-summary-card admin-summary-revenue">
            <span class="admin-summary-icon"><i class="fas fa-coins"></i></span>
            <div>
                <span>Revenue</span>
                <strong>LKR <?php echo number_format($summary['revenue'], 2); ?></strong>
            </div>
        </a>
    </div>

    <div class="admin-product-tabs">
        <a href="#active-products" class="btn btn-outline">Active Products</a>
        <a href="#deleted-products" class="btn btn-outline">Deleted Products</a>
    </div>

    <div id="active-products" class="admin-table-section">
        <div class="admin-section-heading">
            <div>
                <span class="eyebrow">Available in store</span>
                <h3>Active Products</h3>
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
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
            
                $sql = "SELECT * FROM products WHERE is_deleted = 0 ORDER BY id DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        render_product_row($row, false);
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center;'>No products found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>

    <div id="deleted-products" class="admin-table-section">
        <div class="admin-section-heading">
            <div>
                <span class="eyebrow">Hidden from store</span>
                <h3>Deleted Products</h3>
            </div>
        </div>
        <div class="table-container table-container-muted">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $deleted_sql = "SELECT * FROM products WHERE is_deleted = 1 ORDER BY id DESC";
                    $deleted_result = $conn->query($deleted_sql);

                    if ($deleted_result->num_rows > 0) {
                        while ($row = $deleted_result->fetch_assoc()) {
                            render_product_row($row, true);
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>No deleted products found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
