<?php
include 'includes/db.php';
include 'includes/header.php';

$error = '';
$message = '';
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    $token = '';
}
$reset_record = null;

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

function find_valid_reset($conn, $token) {
    if ($token === '') {
        return null;
    }

    $token_hash = hash('sha256', $token);
    $stmt = $conn->prepare("
        SELECT pr.id, pr.user_id, u.email
        FROM password_resets pr
        JOIN users u ON u.id = pr.user_id
        WHERE pr.token_hash = ?
          AND pr.expires_at > NOW()
          AND pr.used_at IS NULL
        LIMIT 1
    ");
    $stmt->bind_param('s', $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();

    return $record ?: null;
}

$reset_record = find_valid_reset($conn, $token);

if ($token === '') {
    $error = 'Password reset token is missing.';
} elseif (!$reset_record) {
    $error = 'This password reset link is invalid or expired.';
}

if (isset($_POST['reset_password']) && $reset_record) {
    ayurora_require_valid_csrf();

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $password_error = ayurora_password_error($password);

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif ($password_error !== '') {
        $error = $password_error;
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $update_stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $update_stmt->bind_param('si', $hashed_password, $reset_record['user_id']);
        $update_stmt->execute();
        $update_stmt->close();

        $mark_stmt = $conn->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
        $mark_stmt->bind_param('i', $reset_record['id']);
        $mark_stmt->execute();
        $mark_stmt->close();

        $message = 'Your password has been updated. You can now login with your new password.';
        $reset_record = null;
    }
}
?>

<div class="auth-container">
    <h2 class="section-title">Create New Password</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <a href="login.php" class="btn btn-primary" style="width: 100%;">Go to Login</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($reset_record): ?>
            <p class="auth-helper">Resetting password for <?php echo htmlspecialchars($reset_record['email']); ?></p>

            <form method="POST" action="">
                <?php echo ayurora_csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                </div>
                <button type="submit" name="reset_password" class="btn btn-primary" style="width: 100%;">Update Password</button>
            </form>
        <?php else: ?>
            <a href="forgot_password.php" class="btn btn-primary" style="width: 100%;">Request New Link</a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
