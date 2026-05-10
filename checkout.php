<?php
include 'includes/db.php';
require_once 'includes/checkout_helpers.php';
ayurora_require_login();

if (empty($_SESSION['cart'])) {
    header('Location: index.php#products');
    exit();
}

$message = '';
$shipping_address = trim($_POST['shipping_address'] ?? ($_SESSION['checkout_details']['shipping_address'] ?? ''));
$phone = trim($_POST['phone'] ?? ($_SESSION['checkout_details']['phone'] ?? ''));
$delivery_notes = trim($_POST['delivery_notes'] ?? ($_SESSION['checkout_details']['delivery_notes'] ?? ''));
$delivery_method = $_POST['delivery_method'] ?? ($_SESSION['checkout_details']['delivery_method'] ?? 'standard');
$payment_method = $_POST['payment_method'] ?? ($_SESSION['checkout_details']['payment_method'] ?? 'card');

$delivery_options = ayurora_delivery_options();
$payment_options = ayurora_payment_options();

if (!isset($delivery_options[$delivery_method])) {
    $delivery_method = 'standard';
}

if (!isset($payment_options[$payment_method])) {
    $payment_method = 'card';
}

ayurora_ensure_order_columns($conn);

$stock_error = '';
$summary = ayurora_load_cart_summary($conn, $_SESSION['cart'], $delivery_method, $stock_error);
$cart_products = $summary['products'];
$items_total = $summary['items_total'];
$delivery_fee = $summary['delivery_fee'];
$total = $summary['total'];

if (empty($cart_products)) {
    unset($_SESSION['cart'], $_SESSION['checkout_details']);
    header('Location: index.php#products');
    exit();
}

if (isset($_POST['continue_payment'])) {
    ayurora_require_valid_csrf();

    $clean_shipping_address = ayurora_clean_multiline_text($shipping_address, 500);
    $clean_delivery_notes = $delivery_notes === '' ? '' : ayurora_clean_multiline_text($delivery_notes, 500);
    $clean_phone = preg_replace('/[^0-9+ ]/', '', $phone);

    if ($stock_error !== '') {
        $message = 'Please update your cart. ' . $stock_error;
    } elseif ($clean_shipping_address === null || $phone === '') {
        $message = 'Shipping address and phone number are required.';
    } elseif ($clean_delivery_notes === null) {
        $message = 'Delivery notes must be under 500 characters.';
    } elseif ($clean_phone !== $phone || !preg_match('/^\+?[0-9 ]{7,20}$/', $phone)) {
        $message = 'Please enter a valid phone number.';
    } else {
        $_SESSION['checkout_details'] = [
            'shipping_address' => $clean_shipping_address,
            'phone' => $phone,
            'delivery_notes' => $clean_delivery_notes,
            'delivery_method' => $delivery_method,
            'payment_method' => $payment_method,
        ];

        header('Location: ' . ayurora_payment_page($payment_method));
        exit();
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h2 class="section-title">Checkout</h2>

    <?php if ($message): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($stock_error): ?>
        <div class="alert alert-error">
            <?php echo $stock_error; ?> <a href="cart.php" class="auth-link">Update your cart</a>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <div class="checkout-panel">
            <div class="checkout-progress">
                <span class="is-active">Cart</span>
                <span class="is-active">Checkout</span>
                <span>Payment</span>
                <span>Confirmation</span>
            </div>

            <h3>Shipping Details</h3>
            <form id="checkout-form" method="POST" action="checkout.php" data-items-total="<?php echo htmlspecialchars((string) $items_total); ?>">
                <?php echo ayurora_csrf_field(); ?>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Shipping Address</label>
                    <textarea name="shipping_address" class="form-control" rows="4" placeholder="House number, street, city, postal code" required><?php echo htmlspecialchars($shipping_address); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="+94 77 123 4567" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Delivery Notes</label>
                    <textarea name="delivery_notes" class="form-control" rows="3" placeholder="Optional delivery instructions"><?php echo htmlspecialchars($delivery_notes); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Delivery Method</label>
                    <div class="checkout-options">
                        <?php foreach ($delivery_options as $value => $option): ?>
                            <label class="checkout-option">
                                <input type="radio" name="delivery_method" value="<?php echo htmlspecialchars($value); ?>" data-label="<?php echo htmlspecialchars($option['label']); ?>" data-fee="<?php echo htmlspecialchars((string) $option['fee']); ?>" <?php echo $delivery_method === $value ? 'checked' : ''; ?>>
                                <span>
                                    <strong><?php echo htmlspecialchars($option['label']); ?></strong>
                                    <small><?php echo htmlspecialchars($option['description']); ?></small>
                                    <em><?php echo (float) $option['fee'] > 0 ? 'LKR ' . number_format((float) $option['fee'], 2) : 'Free'; ?></em>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="profile-divider"></div>
                <h3>Choose Payment Method</h3>

                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="checkout-options">
                        <?php foreach ($payment_options as $value => $option): ?>
                            <label class="checkout-option">
                                <input type="radio" name="payment_method" value="<?php echo htmlspecialchars($value); ?>" data-label="<?php echo htmlspecialchars($option['label']); ?>" <?php echo $payment_method === $value ? 'checked' : ''; ?>>
                                <span>
                                    <strong><?php echo htmlspecialchars($option['label']); ?></strong>
                                    <small><?php echo htmlspecialchars($option['description']); ?></small>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($stock_error): ?>
                    <a href="cart.php" class="btn btn-outline checkout-main-button">Review Cart Stock</a>
                <?php else: ?>
                    <button type="submit" name="continue_payment" id="place-order-button" class="btn btn-primary checkout-main-button">Continue to Payment - LKR <?php echo number_format($total, 2); ?></button>
                <?php endif; ?>
            </form>
        </div>

        <aside class="checkout-summary">
            <h3>Order Summary</h3>
            <div class="checkout-summary-items">
                <?php foreach ($cart_products as $product): ?>
                    <div class="checkout-summary-row">
                        <span><?php echo htmlspecialchars($product['name']); ?> x <?php echo (int) $product['quantity']; ?></span>
                        <span>LKR <?php echo number_format((float) $product['subtotal'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr>
            <div class="checkout-summary-meta">
                <div>
                    <span>Items</span>
                    <span>LKR <?php echo number_format($items_total, 2); ?></span>
                </div>
                <div>
                    <span id="delivery-summary-label"><?php echo htmlspecialchars($delivery_options[$delivery_method]['label']); ?></span>
                    <span id="delivery-summary-fee"><?php echo $delivery_fee > 0 ? 'LKR ' . number_format($delivery_fee, 2) : 'Free'; ?></span>
                </div>
                <div>
                    <span>Payment</span>
                    <span id="payment-summary-label"><?php echo htmlspecialchars($payment_options[$payment_method]['label']); ?></span>
                </div>
            </div>
            <div class="checkout-total-row">
                <span>Total</span>
                <span id="checkout-total">LKR <?php echo number_format($total, 2); ?></span>
            </div>
        </aside>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
