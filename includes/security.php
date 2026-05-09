<?php
function ayurora_send_security_headers() {
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

function ayurora_start_secure_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ayurora_send_security_headers();

    $is_secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $is_secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function ayurora_csrf_token() {
    ayurora_start_secure_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function ayurora_csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(ayurora_csrf_token()) . '">';
}

function ayurora_verify_csrf() {
    ayurora_start_secure_session();

    $token = $_POST['csrf_token'] ?? '';
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function ayurora_require_valid_csrf() {
    if (!ayurora_verify_csrf()) {
        http_response_code(403);
        exit('Invalid request token.');
    }
}

function ayurora_require_login($redirect = 'login.php') {
    ayurora_start_secure_session();

    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $redirect);
        exit();
    }
}

function ayurora_require_admin($redirect = 'login.php') {
    ayurora_start_secure_session();

    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . $redirect);
        exit();
    }
}

function ayurora_password_error($password) {
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must include at least one uppercase letter.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must include at least one lowercase letter.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must include at least one number.';
    }

    return '';
}

function ayurora_product_categories() {
    return [
        'Soaps',
        'Capsules',
        'Oils & Thailas',
        'Herbal Tea & Kwath',
        'Arishta & Syrups',
        'Powders & Churnas',
        'Creams & Balms',
        'Tablets & Vati',
        'Leheyas & Pastes',
        'Hair & Skin Care',
        'Essential Oils',
        'Health Supplements',
        'Wellness Kits',
        'Other',
    ];
}

function ayurora_int_input($value, $min = 1, $max = null) {
    if (filter_var($value, FILTER_VALIDATE_INT) === false) {
        return null;
    }

    $value = (int) $value;
    if ($value < $min) {
        return null;
    }

    if ($max !== null && $value > $max) {
        return null;
    }

    return $value;
}

function ayurora_clean_text($value, $max_length) {
    $value = trim((string) $value);
    $value = preg_replace('/\s+/', ' ', $value);

    if ($value === '' || strlen($value) > $max_length) {
        return null;
    }

    return $value;
}

function ayurora_clean_multiline_text($value, $max_length) {
    $value = trim((string) $value);
    $value = preg_replace("/\r\n|\r/", "\n", $value);

    if ($value === '' || strlen($value) > $max_length) {
        return null;
    }

    return $value;
}

function ayurora_decimal_input($value, $min = 0.01, $max = 1000000) {
    if (!is_numeric($value)) {
        return null;
    }

    $value = round((float) $value, 2);
    if ($value < $min || $value > $max) {
        return null;
    }

    return $value;
}

function ayurora_validate_uploaded_image($file, &$error) {
    if (!isset($file) || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $error = 'Please choose a product image.';
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Sorry, there was an error uploading your file.';
        return null;
    }

    if ($file['size'] > 5000000) {
        $error = 'Sorry, your file is too large.';
        return null;
    }

    $original_name = basename((string) $file['name']);
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($extension, $allowed_extensions, true) || getimagesize($file['tmp_name']) === false) {
        $error = 'Sorry, only valid JPG, JPEG, PNG & GIF image files are allowed.';
        return null;
    }

    $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $original_name);
    return time() . '_' . bin2hex(random_bytes(4)) . '_' . $safe_name;
}
?>
