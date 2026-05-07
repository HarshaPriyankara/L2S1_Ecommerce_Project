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

$conn = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database'],
    (int) $dbConfig['port']
);

if ($conn->connect_error) {
    die('Database connection failed. Check includes/db.local.php or your MySQL settings. Error: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>
