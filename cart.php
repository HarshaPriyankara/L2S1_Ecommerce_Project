<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_start_secure_session();

function append_query_param($url, $key, $value) {
    $parts = parse_url($url);

    if ($parts === false || isset($parts['host'])) {
        return 'index.php';
    }

    $path = $parts['path'] ?? 'index.php';
    $query = [];

    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }

    $query[$key] = $value;
    $newUrl = $path . '?' . http_build_query($query);

    if (!empty($parts['fragment'])) {
        $newUrl .= '#' . $parts['fragment'];
    }

    return $newUrl;
}

function cart_return_url() {
    $fallback = 'index.php';
    $returnUrl = $_POST['redirect_to'] ?? $_SERVER['HTTP_REFERER'] ?? $fallback;

    if (strpos($returnUrl, '://') !== false) {
        $returnUrl = $fallback;
    }

    $path = parse_url($returnUrl, PHP_URL_PATH) ?: '';
    if (strpos($returnUrl, '#') === false && (basename($path) === 'index.php' || $path === '' || $path === '/')) {
        $returnUrl = 'index.php#products';
    }

    return append_query_param($returnUrl, 'cart_added', '1');
}

function cart_return_url_with_error($message) {
    $fallback = 'index.php#products';
    $returnUrl = $_POST['redirect_to'] ?? $_SERVER['HTTP_REFERER'] ?? $fallback;

    if (strpos($returnUrl, '://') !== false) {
        $returnUrl = $fallback;
    }

    return append_query_param($returnUrl, 'cart_error', $message);
}

function cart_item_count() {
    $count = 0;

    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $quantity) {
            $count += (int) $quantity;
        }
    }

    return $count;
}

function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = 1; // Default quantity

    $stock_stmt = $conn->prepare('SELECT stock_quantity FROM products WHERE id = ? AND is_deleted = 0 LIMIT 1');
    $stock_stmt->bind_param('i', $product_id);
    $stock_stmt->execute();
    $stock_result = $stock_stmt->get_result();
    $product = $stock_result->fetch_assoc();
    $stock_stmt->close();

    $current_quantity = isset($_SESSION['cart'][$product_id]) ? (int) $_SESSION['cart'][$product_id] : 0;
    $stock_quantity = $product ? (int) $product['stock_quantity'] : 0;

    if (!$product || $stock_quantity <= 0 || $current_quantity + $quantity > $stock_quantity) {
        $message = !$product || $stock_quantity <= 0 ? 'This product is out of stock.' : 'Only ' . $stock_quantity . ' item(s) are available.';

        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'cart_count' => cart_item_count(),
                'message' => $message,
            ]);
            exit();
        }

        header('Location: ' . cart_return_url_with_error($message));
        exit();
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } elseif ($product_id > 0) {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    if (is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'cart_count' => cart_item_count(),
            'message' => 'Added to cart',
        ]);
        exit();
    }

    header('Location: ' . cart_return_url());
    exit();
}

// Handle Remove from Cart
if (isset($_GET['remove'])) {
    $id_to_remove = $_GET['remove'];
    unset($_SESSION['cart'][$id_to_remove]);
    header('Location: cart.php');
    exit();
}

// Handle Update Quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $pid => $qty) {
        $pid = (int) $pid;
        $qty = max(0, (int) $qty);

        if ($qty == 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $stock_stmt = $conn->prepare('SELECT stock_quantity FROM products WHERE id = ? AND is_deleted = 0 LIMIT 1');
            $stock_stmt->bind_param('i', $pid);
            $stock_stmt->execute();
            $stock_row = $stock_stmt->get_result()->fetch_assoc();
            $stock_stmt->close();

            if (!$stock_row || (int) $stock_row['stock_quantity'] <= 0) {
                unset($_SESSION['cart'][$pid]);
                continue;
            }

            $qty = min($qty, (int) $stock_row['stock_quantity']);
            $_SESSION['cart'][$pid] = $qty;
        }
    }
    header('Location: cart.php');
    exit();
}

include 'includes/header.php';
?>

<div class="container">
    <h2 class="section-title">Your Shopping Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-error">Your cart is empty. <a href="index.php">Go Shop!</a></div>
    <?php else: ?>
        <form action="cart.php" method="POST">
            <div class="table-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_price = 0;
                        $cart_items = $_SESSION['cart'];
                        
                        if (count($cart_items) > 0) {
                            // Create a comma separated list of IDs for query
                            $ids = implode(',', array_keys($cart_items));
                            $sql = "SELECT * FROM products WHERE id IN ($ids) AND is_deleted = 0";
                            $result = $conn->query($sql);
                            $has_stock_issue = false;
                            
                            while ($row = $result->fetch_assoc()) {
                                $available_stock = (int) $row['stock_quantity'];
                                $qty = min((int) $cart_items[$row['id']], max(0, $available_stock));
                                if ($available_stock <= 0 || (int) $cart_items[$row['id']] > $available_stock) {
                                    $has_stock_issue = true;
                                }
                                $subtotal = $row['price'] * $qty;
                                $total_price += $subtotal;
                                ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="<?php echo htmlspecialchars(product_image_path($row['image'])); ?>" class="cart-item-img">
                                            <span><?php echo htmlspecialchars($row['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo substr(htmlspecialchars($row['description']), 0, 50) . '...'; ?></td>
                                    <td>LKR <?php echo number_format($row['price'], 2); ?></td>
                                    <td>
                                        <?php if ($available_stock <= 0): ?>
                                            <input type="hidden" name="qty[<?php echo $row['id']; ?>]" value="0">
                                        <?php endif; ?>
                                        <input type="number" name="qty[<?php echo $row['id']; ?>]" value="<?php echo $qty; ?>" min="1" max="<?php echo max(1, $available_stock); ?>" style="width: 60px; padding: 5px;" <?php echo $available_stock <= 0 ? 'disabled' : ''; ?>>
                                        <span class="stock-note"><?php echo $available_stock > 0 ? $available_stock . ' available' : 'Out of stock'; ?></span>
                                    </td>
                                    <td>LKR <?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <a href="cart.php?remove=<?php echo $row['id']; ?>" class="btn-outline" style="border-color: var(--danger); color: var(--danger); padding: 0.3rem 0.8rem; border-radius: 5px;">Remove</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-total">
                Total: LKR <?php echo number_format($total_price, 2); ?>
            </div>

            <?php if (!empty($has_stock_issue)): ?>
                <div class="alert alert-error">Some cart quantities exceed available stock. Please update your cart before checkout.</div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button type="submit" name="update_cart" class="btn btn-outline">Update Cart</button>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (empty($has_stock_issue)): ?>
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                    <?php else: ?>
                        <button type="submit" name="update_cart" class="btn btn-primary">Update Before Checkout</button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Login to Checkout</a>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
