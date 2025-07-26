<?php
// Handle both relative and absolute paths
$envPath = $_ENV['DB_PATH'] ?? 'runtime/database.sqlite';
if (strpos($envPath, '/') === 0) {
    // Absolute path
    $dbPath = $envPath;
} else {
    // Relative path - resolve from app root
    $dbPath = __DIR__ . '/../' . $envPath;
}

// Ensure the path is absolute and normalized
$dbPath = realpath($dbPath) ?: $dbPath;

$r = [
    'class' => 'yii\db\Connection',
    'dsn' => "sqlite:$dbPath",
    'charset' => 'utf8',
];

if (isset($_ENV["ENABLE_SCHEMA_CACHE"]) && $_ENV["ENABLE_SCHEMA_CACHE"] === 'true') {
    $r = array_merge($r,[
        // Schema cache options (for production environment)
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 60,
        'schemaCache' => 'cache',
    ]);
}

return $r;