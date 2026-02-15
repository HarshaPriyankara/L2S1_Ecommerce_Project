<?php
include 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to add to wishlist'); window.location.href='login.php';</script>";
    exit();
}

if (isset($_POST['add_to_wishlist'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];

    
    $check = "SELECT id FROM wishlist WHERE user_id = '$user_id' AND product_id = '$product_id'";
    $result = $conn->query($check);

    if ($result->num_rows == 0) {
        $sql = "INSERT INTO wishlist (user_id, product_id) VALUES ('$user_id', '$product_id')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Added to wishlist!'); window.history.back();</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "<script>alert('Item already in wishlist!'); window.history.back();</script>";
    }
}
?>
