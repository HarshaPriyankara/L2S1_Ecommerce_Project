<?php
include 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if ($user_id <= 0) {
    header('Location: admin_users.php');
    exit();
}

$user_stmt = $conn->prepare('SELECT id, name, email, role, is_active, created_at FROM users WHERE id = ? LIMIT 1');
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if (!$user) {
    header('Location: admin_users.php');
    exit();
}

$orders = [];
$order_stmt = $conn->prepare(
    'SELECT id, total_price, status, shipping_address, phone, delivery_notes, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC'
);
$order_stmt->bind_param('i', $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

while ($order = $order_result->fetch_assoc()) {
    $order['items'] = [];
    $orders[(int) $order['id']] = $order;
}

$order_stmt->close();

if (!empty($orders)) {
    $item_stmt = $conn->prepare(
        'SELECT oi.quantity, oi.price, p.name, p.image
         FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?
         ORDER BY oi.id ASC'
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

<div class="order-history-page">
    <div class="order-history-header">
        <div>
            <span class="eyebrow">Admin user orders</span>
            <h2 class="section-title"><?php echo htmlspecialchars($user['name']); ?></h2>
            <p class="admin-user-detail-line">
                <?php echo htmlspecialchars($user['email']); ?> &middot;
                <?php echo htmlspecialchars(ucfirst($user['role'])); ?> &middot;
                <?php echo (int) $user['is_active'] === 1 ? 'Active' : 'Inactive'; ?>
            </p>
        </div>
        <a href="admin_users.php" class="btn btn-outline">Back to Users</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h3>No orders for this user</h3>
            <p>This customer has not placed any orders yet.</p>
        </div>
    <?php else: ?>
        <div class="order-history-list">
            <?php foreach ($orders as $order): ?>
                <article class="order-card">
                    <div class="order-card-header">
                        <div>
                            <span class="order-number">Order #<?php echo (int) $order['id']; ?></span>
                            <span class="order-date">
                                <?php echo date('F j, Y - g:i A', strtotime($order['created_at'])); ?>
                            </span>
                        </div>
                        <div class="order-card-summary">
                            <span class="status-badge status-<?php echo htmlspecialchars(strtolower($order['status'])); ?>">
                                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                            </span>
                            <strong>LKR <?php echo number_format((float) $order['total_price'], 2); ?></strong>
                        </div>
                    </div>

                    <div class="admin-delivery-details">
                        <div>
                            <span>Shipping Address</span>
                            <strong><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?: 'Not provided')); ?></strong>
                        </div>
                        <div>
                            <span>Phone</span>
                            <strong><?php echo htmlspecialchars($order['phone'] ?: 'Not provided'); ?></strong>
                        </div>
                        <div>
                            <span>Delivery Notes</span>
                            <strong><?php echo nl2br(htmlspecialchars($order['delivery_notes'] ?: 'No notes')); ?></strong>
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
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
