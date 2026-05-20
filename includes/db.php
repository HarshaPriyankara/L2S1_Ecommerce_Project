<?php
$dbConfig = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: 'Radee@7436',
    'database' => getenv('DB_NAME') ?: 'ayurveda_db',
    'port' => getenv('DB_PORT') ? (int) getenv('DB_PORT') : 3306,
];

$localConfigFile = __DIR__ . '/db.local.php';
$hasLocalConfig = false;
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        $dbConfig = array_merge($dbConfig, $localConfig);
        $hasLocalConfig = true;
    }
}

mysqli_report(MYSQLI_REPORT_OFF);

$portsToTry = [$dbConfig['port']];
if (!$hasLocalConfig && !getenv('DB_PORT') && (int) $dbConfig['port'] === 3306) {
    $portsToTry[] = 3308;
}

$conn = null;
$lastError = '';
$attemptedPorts = [];

foreach ($portsToTry as $port) {
    $attemptedPorts[] = (int) $port;
    $conn = @new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database'],
        (int) $port
    );

    if (!$conn->connect_error) {
        $dbConfig['port'] = (int) $port;
        break;
    }

    $lastError = $conn->connect_error;
}

if ($conn->connect_error) {
    http_response_code(500);
    die(
        '<h2>Database connection failed</h2>' .
        '<p>Please create <code>includes/db.local.php</code> from <code>includes/db.local.example.php</code> and update your local MySQL username, password, port, and database name.</p>' .
        '<p>Current settings: host <code>' . htmlspecialchars($dbConfig['host']) . '</code>, user <code>' . htmlspecialchars($dbConfig['username']) . '</code>, database <code>' . htmlspecialchars($dbConfig['database']) . '</code>, tried port(s) <code>' . htmlspecialchars(implode(', ', $attemptedPorts)) . '</code>.</p>' .
        '<p>MySQL error: <code>' . htmlspecialchars($lastError) . '</code></p>'
    );
}

$conn->set_charset('utf8mb4');

$stockColumnCheck = $conn->query("SHOW COLUMNS FROM products LIKE 'stock_quantity'");
if ($stockColumnCheck && $stockColumnCheck->num_rows === 0) {
    $conn->query('ALTER TABLE products ADD COLUMN stock_quantity INT NOT NULL DEFAULT 25 AFTER price');
}

$isActiveColumnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'");
if ($isActiveColumnCheck && $isActiveColumnCheck->num_rows === 0) {
    $conn->query('ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role');
}

function product_image_path($filename) {
    $filename = basename((string) $filename);
    $path = __DIR__ . '/../uploads/' . $filename;

    if ($filename !== '' && is_file($path)) {
        return 'uploads/' . rawurlencode($filename);
    }

    return 'assets/images/ayurora-logo-small.png';
}
?>
