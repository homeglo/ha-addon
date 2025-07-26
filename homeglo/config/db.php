<?php
// Handle both relative and absolute paths
$envPath = $_ENV['DB_PATH'] ?? '/data/database.sqlite';
if (strpos($envPath, '/') === 0) {
    // Absolute path - use as-is for SQLite
    $dbPath = $envPath;
} else {
    // Relative path - resolve from app root
    $dbPath = __DIR__ . '/../' . $envPath;
}

// For SQLite, we don't need realpath() as it can cause issues if file doesn't exist yet
// SQLite will create the file if it doesn't exist and we have write permissions

// Debug logging for database connection issues
error_log("DB Debug: DB_PATH env var = " . ($_ENV['DB_PATH'] ?? 'NOT SET'));
error_log("DB Debug: Final dbPath = $dbPath");
error_log("DB Debug: File exists? " . (file_exists($dbPath) ? 'YES' : 'NO'));
if (file_exists($dbPath)) {
    error_log("DB Debug: File permissions = " . substr(sprintf('%o', fileperms($dbPath)), -4));
    error_log("DB Debug: File readable? " . (is_readable($dbPath) ? 'YES' : 'NO'));
    error_log("DB Debug: File writable? " . (is_writable($dbPath) ? 'YES' : 'NO'));
}
error_log("DB Debug: Directory /data exists? " . (is_dir('/data') ? 'YES' : 'NO'));
if (is_dir('/data')) {
    error_log("DB Debug: Directory /data writable? " . (is_writable('/data') ? 'YES' : 'NO'));
}

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