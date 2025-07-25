<?php
// Immediate debug output
echo "PHP is running<br>";
echo "Ingress path: " . ($_SERVER['HTTP_X_INGRESS_PATH'] ?? 'not set') . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

// Uncomment to stop here and see debug info
// exit;

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', $_ENV['ENV'] ?? 'dev');

ini_set('error_reporting','E_ALL & ~E_NOTICE & ~E_DEPRECATED');
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Debug ingress headers
if (!empty($_SERVER['HTTP_X_INGRESS_PATH'])) {
    error_log("Ingress Path: " . $_SERVER['HTTP_X_INGRESS_PATH']);
    error_log("Request URI: " . $_SERVER['REQUEST_URI']);
}

require __DIR__ . '/../vendor/autoload.php';
// Only load .env if it exists (for local development)
if (file_exists(__DIR__ . '/../.env')) {
    (Dotenv\Dotenv::createImmutable(__DIR__ . '/../'))->safeLoad();
}


require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
