<?php
include 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';
$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT id, name, email, password, role FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '') {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $email_stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        $email_stmt->bind_param('si', $email, $user_id);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();

        if ($email_result->num_rows > 0) {
            $error = 'That email is already used by another account.';
        }

        $email_stmt->close();
    }

    $password_to_save = $user['password'];

    if (!$error && ($current_password !== '' || $new_password !== '' || $confirm_password !== '')) {
        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $error = 'Fill all password fields to change your password.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $password_to_save = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    if (!$error) {
        $update_stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
        $update_stmt->bind_param('sssi', $name, $email, $password_to_save, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION['name'] = $name;
            $message = 'Profile updated successfully.';

            $user['name'] = $name;
            $user['email'] = $email;
            $user['password'] = $password_to_save;
        } else {
            $error = 'Could not update profile. Please try again.';
        }

        $update_stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="auth-container profile-container">
    <h2 class="section-title">My Profile</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="profile-divider"></div>

        <h3 class="profile-subtitle">Change Password</h3>
        <p class="auth-helper">Leave these fields empty if you do not want to change your password.</p>

        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" class="form-control">
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" minlength="6">
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" minlength="6">
        </div>

        <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">Update Profile</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
