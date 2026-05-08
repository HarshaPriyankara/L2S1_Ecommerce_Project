<?php
include 'includes/db.php';
include 'includes/order_status.php';
require_once 'includes/security.php';
ayurora_start_secure_session();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = ayurora_int_input($_GET['user_id'] ?? null);

if ($user_id === null) {
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

$order_columns = [
    'shipping_address' => "ALTER TABLE orders ADD COLUMN shipping_address TEXT NULL AFTER status",
    'phone' => "ALTER TABLE orders ADD COLUMN phone VARCHAR(30) NULL AFTER shipping_address",
    'delivery_notes' => "ALTER TABLE orders ADD COLUMN delivery_notes TEXT NULL AFTER phone",
    'delivery_method' => "ALTER TABLE orders ADD COLUMN delivery_method VARCHAR(50) NULL AFTER delivery_notes",
    'delivery_fee' => "ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(10, 2) NOT NULL DEFAULT 0 AFTER delivery_method",
    'payment_method' => "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL AFTER delivery_fee",
];

foreach ($order_columns as $column => $alter_sql) {
    $column_check = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
    if ($column_check && $column_check->num_rows === 0) {
        $conn->query($alter_sql);
    }
}

$delivery_labels = [
    'standard' => 'Standard Delivery',
    'express' => 'Express Delivery',
    'pickup' => 'Store Pickup',
];
$payment_labels = [
    'card' => 'Card Payment',
    'cod' => 'Cash on Delivery',
    'bank_transfer' => 'Bank Transfer',
];

$orders = [];
$order_stmt = $conn->prepare(
    'SELECT id, total_price, status, shipping_address, phone, delivery_notes, delivery_method, delivery_fee, payment_method, created_at
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

                    <?php render_order_status_tracker($order['status']); ?>

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
                        <div>
                            <span>Delivery Method</span>
                            <strong><?php echo htmlspecialchars($delivery_labels[$order['delivery_method'] ?? ''] ?? 'Not provided'); ?><br><?php echo (float) $order['delivery_fee'] > 0 ? 'LKR ' . number_format((float) $order['delivery_fee'], 2) : 'Free'; ?></strong>
                        </div>
                        <div>
                            <span>Payment Method</span>
                            <strong><?php echo htmlspecialchars($payment_labels[$order['payment_method'] ?? ''] ?? 'Not provided'); ?></strong>
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
