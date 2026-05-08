<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_start_secure_session();

$error = '';
$max_attempts = 5;
$lock_seconds = 300;

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $attempt_key = strtolower($email) ?: 'unknown';

    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    $attempt = $_SESSION['login_attempts'][$attempt_key] ?? [
        'count' => 0,
        'locked_until' => 0,
    ];

    if (!empty($attempt['locked_until']) && $attempt['locked_until'] > time()) {
        $remaining_minutes = (int) ceil(($attempt['locked_until'] - time()) / 60);
        $error = 'Too many login attempts. Please try again in ' . $remaining_minutes . ' minute(s).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, email, password, role, is_active FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && isset($user['is_active']) && (int) $user['is_active'] !== 1) {
            $error = 'This account is inactive. Please contact admin.';
        } elseif ($user && password_verify($password, $user['password'])) {
            unset($_SESSION['login_attempts'][$attempt_key]);
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'index.php'));
            exit();
        } else {
            $attempt['count'] = ((int) $attempt['count']) + 1;

            if ($attempt['count'] >= $max_attempts) {
                $attempt['locked_until'] = time() + $lock_seconds;
                $attempt['count'] = 0;
                $error = 'Too many login attempts. Please try again in 5 minutes.';
            } else {
                $error = 'Invalid email or password.';
            }

            $_SESSION['login_attempts'][$attempt_key] = $attempt;
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <h2 class="section-title">Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
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
    <p style="margin-top: 1rem; text-align: center;">
        <a href="forgot_password.php" class="auth-link">Forgot password?</a>
    </p>
    <p style="margin-top: 1rem; text-align: center;">Don't have an account? <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a></p>
</div>

<?php include 'includes/footer.php'; ?>
