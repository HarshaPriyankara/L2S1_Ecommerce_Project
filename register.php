<?php
include 'includes/db.php';
include 'includes/header.php';

$message = '';
$error = '';

if (isset($_POST['register'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
         
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            $check_result->close();  
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', 'customer')";

            try {
                if ($conn->query($sql) === TRUE) {
                    $message = "Registration successful! <a href='login.php'>Login here</a>";
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="auth-container">
    <h2 class="section-title">Create Account</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
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
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary" style="width: 100%;">Register</button>
    </form>
    <p style="margin-top: 1rem; text-align: center;">Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login</a></p>
</div>

<?php include 'includes/footer.php'; ?>
