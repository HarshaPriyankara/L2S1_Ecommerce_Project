<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_start_secure_session();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['submit_review'])) {
    $user_id = (int) $_SESSION['user_id'];
    $product_id = ayurora_int_input($_POST['product_id'] ?? null);
    $rating = ayurora_int_input($_POST['rating'] ?? null, 1, 5);
    $comment = ayurora_clean_multiline_text($_POST['comment'] ?? '', 1000);

    if ($product_id === null || $rating === null || $comment === null) {
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

    $check_stmt = $conn->prepare('SELECT id FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1');
    $check_stmt->bind_param('ii', $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        $insert_stmt = $conn->prepare('INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)');
        $insert_stmt->bind_param('iiis', $user_id, $product_id, $rating, $comment);
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
