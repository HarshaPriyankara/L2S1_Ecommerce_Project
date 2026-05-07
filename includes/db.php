<?php
$dbConfig = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'database' => getenv('DB_NAME') ?: 'ayurveda_db',
    'port' => (int) (getenv('DB_PORT') ?: 3306),
];

$localConfigFile = __DIR__ . '/db.local.php';
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        $dbConfig = array_merge($dbConfig, $localConfig);
    }
}

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database'],
    (int) $dbConfig['port']
);

if ($conn->connect_error) {
    http_response_code(500);
    die(
        '<h2>Database connection failed</h2>' .
        '<p>Please create <code>includes/db.local.php</code> from <code>includes/db.local.example.php</code> and update your local MySQL username, password, port, and database name.</p>' .
        '<p>Current settings: host <code>' . htmlspecialchars($dbConfig['host']) . '</code>, user <code>' . htmlspecialchars($dbConfig['username']) . '</code>, database <code>' . htmlspecialchars($dbConfig['database']) . '</code>, port <code>' . (int) $dbConfig['port'] . '</code>.</p>' .
        '<p>MySQL error: <code>' . htmlspecialchars($conn->connect_error) . '</code></p>'
    );
}

$conn->set_charset('utf8mb4');
?>
