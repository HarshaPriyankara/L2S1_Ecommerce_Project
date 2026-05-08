<?php
include 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
$message = '';
$error = '';

// Keep existing local databases compatible with the new processing status.
$conn->query("ALTER TABLE orders MODIFY status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending'");

if (isset($_POST['update_status'])) {
    $order_id = (int) ($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($order_id <= 0 || !in_array($status, $allowed_statuses, true)) {
        $error = 'Please choose a valid order status.';
    } else {
        $update_stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $update_stmt->bind_param('si', $status, $order_id);

        if ($update_stmt->execute()) {
            $message = 'Order status updated successfully.';
        } else {
            $error = 'Could not update order status. Please try again.';
        }

        $update_stmt->close();
    }
}

$orders = [];
$order_sql = "
    SELECT o.id, o.total_price, o.status, o.created_at, u.name AS customer_name, u.email AS customer_email
    FROM orders o
    INNER JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC, o.id DESC
";
$order_result = $conn->query($order_sql);

while ($order = $order_result->fetch_assoc()) {
    $order['items'] = [];
    $orders[(int) $order['id']] = $order;
}

if (!empty($orders)) {
    $item_stmt = $conn->prepare(
        "SELECT oi.quantity, oi.price, p.name, p.image
         FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?
         ORDER BY oi.id ASC"
    );

    foreach (array_keys($orders) as $order_id) {
        $item_stmt->bind_param('i', $order_id);
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();

        while ($item = $item_result->fetch_assoc()) {
            $orders[$order_id]['items'][] = $item;
        }
    }

    $item_stmt->close();
}

include 'includes/header.php';
?>

<div class="admin-orders-page">
    <div class="order-history-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h2 class="section-title">Order Management</h2>
        </div>
        <a href="admin.php" class="btn btn-outline">Product Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No customer orders yet</h3>
            <p>New checkout orders will appear here for admin review.</p>
        </div>
    <?php else: ?>
        <div class="order-history-list">
            <?php foreach ($orders as $order): ?>
                <article class="order-card admin-order-card">
                    <div class="order-card-header">
                        <div>
                            <span class="order-number">Order #<?php echo (int) $order['id']; ?></span>
                            <span class="order-date">
                                <?php echo date('F j, Y - g:i A', strtotime($order['created_at'])); ?>
                            </span>
                            <span class="customer-line">
                                <?php echo htmlspecialchars($order['customer_name']); ?> &middot;
                                <?php echo htmlspecialchars($order['customer_email']); ?>
                            </span>
                        </div>
                        <div class="order-card-summary">
                            <span class="status-badge status-<?php echo htmlspecialchars(strtolower($order['status'])); ?>">
                                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                            </span>
                            <strong>LKR <?php echo number_format((float) $order['total_price'], 2); ?></strong>
                        </div>
                    </div>

                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                            <?php
                            $product_name = $item['name'] ?: 'Product no longer available';
                            $item_total = (float) $item['price'] * (int) $item['quantity'];
                            ?>
                            <div class="order-item">
                                <img src="<?php echo htmlspecialchars(product_image_path($item['image'] ?? '')); ?>" alt="<?php echo htmlspecialchars($product_name); ?>">
                                <div class="order-item-details">
                                    <h3><?php echo htmlspecialchars($product_name); ?></h3>
                                    <span>Qty <?php echo (int) $item['quantity']; ?> x LKR <?php echo number_format((float) $item['price'], 2); ?></span>
                                </div>
                                <strong>LKR <?php echo number_format($item_total, 2); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form method="POST" action="admin_orders.php" class="order-status-form">
                        <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                        <label for="status-<?php echo (int) $order['id']; ?>">Update Status</label>
                        <select id="status-<?php echo (int) $order['id']; ?>" name="status" class="form-control">
                            <?php foreach ($allowed_statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary">Save</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
