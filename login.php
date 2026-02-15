<?php
include 'includes/db.php';
include 'includes/header.php';

$error = '';

if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            
            if ($user['role'] == 'admin') {
                echo "<script>window.location.href='admin.php';</script>";
            } else {
                echo "<script>window.location.href='index.php';</script>";
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<div class="auth-container">
    <h2 class="section-title">Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>
    <p style="margin-top: 1rem; text-align: center;">Don't have an account? <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a></p>
</div>

<?php include 'includes/footer.php'; ?>
