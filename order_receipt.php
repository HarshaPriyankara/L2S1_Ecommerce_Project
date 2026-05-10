<?php
include 'includes/db.php';
require_once 'includes/checkout_helpers.php';
ayurora_require_login();

$order_id = ayurora_int_input($_GET['order_id'] ?? null);
$user_id = (int) $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

if ($order_id === null) {
    header('Location: friendly_error.php?type=order');
    exit();
}

ayurora_ensure_order_columns($conn);

if ($is_admin) {
    $order_stmt = $conn->prepare(
        'SELECT o.id, o.total_price, o.status, o.shipping_address, o.phone, o.delivery_notes, o.delivery_method, o.delivery_fee, o.payment_method, o.payment_reference, o.payment_status, o.created_at, u.name AS customer_name, u.email AS customer_email
         FROM orders o
         INNER JOIN users u ON o.user_id = u.id
         WHERE o.id = ?
         LIMIT 1'
    );
    $order_stmt->bind_param('i', $order_id);
} else {
    $order_stmt = $conn->prepare(
        'SELECT o.id, o.total_price, o.status, o.shipping_address, o.phone, o.delivery_notes, o.delivery_method, o.delivery_fee, o.payment_method, o.payment_reference, o.payment_status, o.created_at, u.name AS customer_name, u.email AS customer_email
         FROM orders o
         INNER JOIN users u ON o.user_id = u.id
         WHERE o.id = ? AND o.user_id = ?
         LIMIT 1'
    );
    $order_stmt->bind_param('ii', $order_id, $user_id);
}

$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();
$order_stmt->close();

if (!$order) {
    header('Location: friendly_error.php?type=order');
    exit();
}

$items = [];
$items_total = 0;
$item_stmt = $conn->prepare(
    'SELECT oi.quantity, oi.price, p.name
     FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?
     ORDER BY oi.id ASC'
);
$item_stmt->bind_param('i', $order_id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();

while ($item = $item_result->fetch_assoc()) {
    $item['line_total'] = (float) $item['price'] * (int) $item['quantity'];
    $items_total += $item['line_total'];
    $items[] = $item;
}

$item_stmt->close();

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
$delivery_label = $delivery_labels[$order['delivery_method'] ?? ''] ?? 'Not provided';
$payment_label = $payment_labels[$order['payment_method'] ?? ''] ?? 'Not provided';
$delivery_fee = (float) ($order['delivery_fee'] ?? 0);

include 'includes/header.php';
?>

<div class="receipt-page">
    <div class="receipt-toolbar">
        <a href="order_history.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Orders</a>
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print Receipt</button>
    </div>

    <section class="receipt-sheet">
        <div class="receipt-header">
            <div>
                <span class="eyebrow">Payment Receipt</span>
                <h2>AYURORA</h2>
                <p>Premium Sri Lankan Healing</p>
            </div>
            <div class="receipt-number">
                <span>Receipt</span>
                <strong>#<?php echo (int) $order['id']; ?></strong>
            </div>
        </div>

        <div class="receipt-meta">
            <div>
                <span>Customer</span>
                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
            </div>
            <div>
                <span>Order Date</span>
                <strong><?php echo date('F j, Y', strtotime($order['created_at'])); ?></strong>
                <small><?php echo date('g:i A', strtotime($order['created_at'])); ?></small>
            </div>
            <div>
                <span>Status</span>
                <strong><?php echo htmlspecialchars(ucfirst($order['status'])); ?></strong>
            </div>
            <div>
                <span>Payment</span>
                <strong><?php echo htmlspecialchars($payment_label); ?></strong>
                <small><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_status'] ?? 'pending'))); ?></small>
                <small><?php echo htmlspecialchars($order['payment_reference'] ?: 'No reference'); ?></small>
            </div>
        </div>

        <div class="receipt-address">
            <div>
                <span>Shipping Address</span>
                <strong><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?: 'Not provided')); ?></strong>
            </div>
            <div>
                <span>Phone</span>
                <strong><?php echo htmlspecialchars($order['phone'] ?: 'Not provided'); ?></strong>
            </div>
            <div>
                <span>Delivery</span>
                <strong><?php echo htmlspecialchars($delivery_label); ?></strong>
            </div>
        </div>

        <div class="receipt-table-wrap">
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name'] ?: 'Product no longer available'); ?></td>
                            <td><?php echo (int) $item['quantity']; ?></td>
                            <td>LKR <?php echo number_format((float) $item['price'], 2); ?></td>
                            <td>LKR <?php echo number_format((float) $item['line_total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="receipt-totals">
            <div>
                <span>Items Total</span>
                <strong>LKR <?php echo number_format($items_total, 2); ?></strong>
            </div>
            <div>
                <span>Delivery Fee</span>
                <strong><?php echo $delivery_fee > 0 ? 'LKR ' . number_format($delivery_fee, 2) : 'Free'; ?></strong>
            </div>
            <div class="receipt-grand-total">
                <span>Grand Total</span>
                <strong>LKR <?php echo number_format((float) $order['total_price'], 2); ?></strong>
            </div>
        </div>

        <p class="receipt-note">Thank you for shopping with AYURORA. This receipt was generated from your confirmed order.</p>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
