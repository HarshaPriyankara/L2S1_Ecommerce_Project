<?php
require_once __DIR__ . '/security.php';

function ayurora_delivery_options() {
    return [
        'standard' => ['label' => 'Standard Delivery', 'description' => 'Islandwide delivery within 3-5 business days.', 'fee' => 350],
        'express' => ['label' => 'Express Delivery', 'description' => 'Priority delivery within 1-2 business days.', 'fee' => 750],
        'pickup' => ['label' => 'Store Pickup', 'description' => 'Collect from AYURORA store when ready.', 'fee' => 0],
    ];
}

function ayurora_payment_options() {
    return [
        'card' => ['label' => 'Card Payment', 'description' => 'Pay securely using your Visa or Mastercard details.'],
        'cod' => ['label' => 'Cash on Delivery', 'description' => 'Confirm your order now and pay when it arrives.'],
        'bank_transfer' => ['label' => 'Bank Transfer', 'description' => 'Place the order and submit your bank transfer reference.'],
    ];
}

function ayurora_payment_page($payment_method) {
    $pages = [
        'card' => 'payment_card.php',
        'cod' => 'payment_cod.php',
        'bank_transfer' => 'payment_bank_transfer.php',
    ];

    return $pages[$payment_method] ?? 'checkout.php';
}

function ayurora_ensure_order_columns($conn) {
    $order_columns = [
        'shipping_address' => "ALTER TABLE orders ADD COLUMN shipping_address TEXT NULL AFTER status",
        'phone' => "ALTER TABLE orders ADD COLUMN phone VARCHAR(30) NULL AFTER shipping_address",
        'delivery_notes' => "ALTER TABLE orders ADD COLUMN delivery_notes TEXT NULL AFTER phone",
        'delivery_method' => "ALTER TABLE orders ADD COLUMN delivery_method VARCHAR(50) NULL AFTER delivery_notes",
        'delivery_fee' => "ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(10, 2) NOT NULL DEFAULT 0 AFTER delivery_method",
        'payment_method' => "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL AFTER delivery_fee",
        'payment_reference' => "ALTER TABLE orders ADD COLUMN payment_reference VARCHAR(120) NULL AFTER payment_method",
        'payment_status' => "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER payment_reference",
    ];

    foreach ($order_columns as $column => $alter_sql) {
        $column_check = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
        if ($column_check && $column_check->num_rows === 0) {
            $conn->query($alter_sql);
        }
    }
}

function ayurora_load_cart_summary($conn, $cart_items, $delivery_method, &$stock_error = '') {
    $delivery_options = ayurora_delivery_options();
    if (!isset($delivery_options[$delivery_method])) {
        $delivery_method = 'standard';
    }

    $summary = [
        'products' => [],
        'items_total' => 0,
        'delivery_method' => $delivery_method,
        'delivery_fee' => (float) $delivery_options[$delivery_method]['fee'],
        'total' => 0,
    ];

    $ids = array_map('intval', array_keys($cart_items));
    $ids = array_filter($ids, function ($id) {
        return $id > 0;
    });

    if (empty($ids)) {
        return $summary;
    }

    $id_list = implode(',', $ids);
    $product_result = $conn->query("SELECT * FROM products WHERE id IN ($id_list) AND is_deleted = 0");

    while ($product = $product_result->fetch_assoc()) {
        $product_id = (int) $product['id'];
        $quantity = max(1, (int) ($cart_items[$product_id] ?? 1));
        $available_stock = (int) ($product['stock_quantity'] ?? 0);

        if ($available_stock <= 0) {
            $stock_error = htmlspecialchars($product['name']) . ' is out of stock.';
        } elseif ($quantity > $available_stock) {
            $stock_error = htmlspecialchars($product['name']) . ' only has ' . $available_stock . ' item(s) available.';
            $quantity = $available_stock;
        }

        $subtotal = (float) $product['price'] * $quantity;
        $product['quantity'] = $quantity;
        $product['subtotal'] = $subtotal;
        $summary['products'][] = $product;
        $summary['items_total'] += $subtotal;
    }

    $summary['total'] = $summary['items_total'] + $summary['delivery_fee'];
    return $summary;
}

function ayurora_create_order_from_checkout($conn, $payment_status, $order_status, $payment_reference, &$message) {
    if (empty($_SESSION['checkout_details']) || empty($_SESSION['cart'])) {
        header('Location: checkout.php');
        exit();
    }

    ayurora_ensure_order_columns($conn);

    $checkout = $_SESSION['checkout_details'];
    $stock_error = '';
    $summary = ayurora_load_cart_summary($conn, $_SESSION['cart'], $checkout['delivery_method'] ?? 'standard', $stock_error);

    if ($stock_error !== '') {
        $message = 'Please update your cart. ' . $stock_error;
        return null;
    }

    if (empty($summary['products'])) {
        unset($_SESSION['cart'], $_SESSION['checkout_details']);
        header('Location: index.php#products');
        exit();
    }

    $user_id = (int) $_SESSION['user_id'];
    $shipping_address = $checkout['shipping_address'];
    $phone = $checkout['phone'];
    $delivery_notes = $checkout['delivery_notes'];
    $delivery_method = $summary['delivery_method'];
    $delivery_fee = $summary['delivery_fee'];
    $payment_method = $checkout['payment_method'];
    $total = $summary['total'];

    $conn->begin_transaction();
    $order_stmt = $conn->prepare('INSERT INTO orders (user_id, total_price, status, shipping_address, phone, delivery_notes, delivery_method, delivery_fee, payment_method, payment_reference, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $order_stmt->bind_param('idsssssdsss', $user_id, $total, $order_status, $shipping_address, $phone, $delivery_notes, $delivery_method, $delivery_fee, $payment_method, $payment_reference, $payment_status);

    if ($order_stmt->execute()) {
        $order_id = $conn->insert_id;
        $item_stmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stock_stmt = $conn->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?');
        $stock_update_ok = true;

        foreach ($summary['products'] as $product) {
            $product_id = (int) $product['id'];
            $quantity = (int) $product['quantity'];
            $price = (float) $product['price'];
            $item_stmt->bind_param('iiid', $order_id, $product_id, $quantity, $price);
            if (!$item_stmt->execute()) {
                $stock_update_ok = false;
                break;
            }

            $stock_stmt->bind_param('iii', $quantity, $product_id, $quantity);
            if (!$stock_stmt->execute() || $stock_stmt->affected_rows !== 1) {
                $stock_update_ok = false;
                break;
            }
        }

        $item_stmt->close();
        $stock_stmt->close();
        $order_stmt->close();

        if ($stock_update_ok) {
            $conn->commit();
            unset($_SESSION['cart'], $_SESSION['checkout_details']);
            return $order_id;
        }

        $conn->rollback();
        $message = 'Stock changed while placing your order. Please review your cart and try again.';
        return null;
    }

    $conn->rollback();
    $message = 'Error placing order: ' . $conn->error;
    $order_stmt->close();
    return null;
}
?>
