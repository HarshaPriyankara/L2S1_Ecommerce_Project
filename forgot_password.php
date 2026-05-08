<?php
include 'includes/db.php';
include 'includes/header.php';

$message = '';
$dev_reset_link = '';

$conn->query("
    CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

if (isset($_POST['request_reset'])) {
    $email = trim($_POST['email']);
    $message = 'If an account exists for that email, a password reset link has been generated.';

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 150) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires_at = date('Y-m-d H:i:s', time() + 3600);

            $delete_stmt = $conn->prepare('DELETE FROM password_resets WHERE user_id = ? OR expires_at < NOW() OR used_at IS NOT NULL');
            $delete_stmt->bind_param('i', $user['id']);
            $delete_stmt->execute();
            $delete_stmt->close();

            $insert_stmt = $conn->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
            $insert_stmt->bind_param('iss', $user['id'], $token_hash, $expires_at);
            $insert_stmt->execute();
            $insert_stmt->close();

            $dev_reset_link = 'reset_password.php?token=' . urlencode($token);
        }

        $stmt->close();
    }
}
?>

<div class="auth-container">
    <h2 class="section-title">Reset Password</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($dev_reset_link): ?>
        <div class="reset-dev-box">
            <strong>Development reset link</strong>
            <p>Email sending is not configured, so use this link to continue:</p>
            <a href="<?php echo htmlspecialchars($dev_reset_link); ?>"><?php echo htmlspecialchars($dev_reset_link); ?></a>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" name="request_reset" class="btn btn-primary" style="width: 100%;">Send Reset Link</button>
    </form>

    <p style="margin-top: 1rem; text-align: center;">
        Remember your password?
        <a href="login.php" class="auth-link">Login</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
