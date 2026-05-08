<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_start_secure_session();

$message = '';
$error = '';

if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $password_error = ayurora_password_error($password);

    if ($name === '' || strlen($name) > 100) {
        $error = 'Please enter a valid full name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $error = 'Please enter a valid email address.';
    } elseif ($password_error !== '') {
        $error = $password_error;
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $check_stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check_stmt->bind_param('s', $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = 'Email already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';
            $insert_stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $insert_stmt->bind_param('ssss', $name, $email, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                $message = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = 'Could not create account. Please try again.';
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <h2 class="section-title">Create Account</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary" style="width: 100%;">Register</button>
    </form>
    <p style="margin-top: 1rem; text-align: center;">Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login</a></p>
</div>

<?php include 'includes/footer.php'; ?>
