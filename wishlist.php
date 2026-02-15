<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];


if (isset($_GET['remove'])) {
    $item_id = $_GET['remove'];
    $sql = "DELETE FROM wishlist WHERE id = $item_id AND user_id = $user_id";
    $conn->query($sql);
    echo "<script>window.location.href='wishlist.php';</script>";
    exit();
}

$sql = "SELECT w.id as wishlist_id, p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = $user_id ORDER BY w.created_at DESC";
$result = $conn->query($sql);
?>

<div class="container">
    <h2 class="section-title">My Wishlist</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-container">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="cart-item-img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <a href="product_details.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit; font-weight: bold;">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </a>
                            </div>
                        </td>
                        <td>LKR <?php echo number_format($row['price'], 2); ?></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <form action="cart.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.9rem;">
                                        Add to Cart
                                    </button>
                                </form>
                                <a href="wishlist.php?remove=<?php echo $row['wishlist_id']; ?>" class="btn-outline" style="border-color: var(--danger); color: var(--danger); padding: 0.3rem 0.8rem; border-radius: 5px; font-size: 0.9rem;">
                                    Remove
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Your wishlist is empty. <a href="index.php">Browse products</a></div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
