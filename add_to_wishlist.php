<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_require_login();

if (isset($_POST['add_to_wishlist'])) {
    ayurora_require_valid_csrf();

    $user_id = (int) $_SESSION['user_id'];
    $product_id = ayurora_int_input($_POST['product_id'] ?? null);

    if ($product_id === null) {
        header('Location: index.php#products');
        exit();
    }

    $product_stmt = $conn->prepare('SELECT id FROM products WHERE id = ? AND is_deleted = 0 LIMIT 1');
    $product_stmt->bind_param('i', $product_id);
    $product_stmt->execute();
    $product_exists = $product_stmt->get_result()->num_rows === 1;
    $product_stmt->close();

    if (!$product_exists) {
        header('Location: index.php#products');
        exit();
    }

    $check_stmt = $conn->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1');
    $check_stmt->bind_param('ii', $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows == 0) {
        $insert_stmt = $conn->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)');
        $insert_stmt->bind_param('ii', $user_id, $product_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $check_stmt->close();
    header('Location: product_details.php?id=' . $product_id);
    exit();
}

header('Location: index.php#products');
exit();
?>
