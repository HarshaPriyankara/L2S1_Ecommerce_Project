<?php
include 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to review'); window.location.href='login.php';</script>";
    exit();
}

if (isset($_POST['submit_review'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $conn->real_escape_string($_POST['comment']);

     $check = "SELECT id FROM reviews WHERE user_id = '$user_id' AND product_id = '$product_id'";
    $result = $conn->query($check);

    if ($result->num_rows > 0) {
         echo "<script>alert('You have already reviewed this product.'); window.history.back();</script>";
    } else {
        $sql = "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES ('$user_id', '$product_id', '$rating', '$comment')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Review submitted successfully!'); window.location.href='product_details.php?id=$product_id';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>
