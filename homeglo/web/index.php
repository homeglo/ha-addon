<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', $_ENV['ENV'] ?? 'dev');

ini_set('error_reporting','E_ALL & ~E_NOTICE & ~E_DEPRECATED');
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Fix for Home Assistant ingress
if (!empty($_SERVER['HTTP_X_INGRESS_PATH'])) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['HTTP_X_INGRESS_PATH'] . '/index.php';
    
    // Keep REQUEST_URI as-is for Yii to handle properly
    // The baseUrl in config will handle the ingress path
}

require __DIR__ . '/../vendor/autoload.php';
// Only load .env if it exists (for local development)
if (file_exists(__DIR__ . '/../.env')) {
    (Dotenv\Dotenv::createImmutable(__DIR__ . '/../'))->safeLoad();
}


require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/../config/web.php';

$app = new yii\web\Application($config);
$app->run();
