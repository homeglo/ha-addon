<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', $_ENV['ENV'] ?? 'dev');

ini_set('error_reporting','E_ALL & ~E_NOTICE & ~E_DEPRECATED');
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Fix for Home Assistant ingress
if (!empty($_SERVER['HTTP_X_INGRESS_PATH'])) {
    $ingressPath = $_SERVER['HTTP_X_INGRESS_PATH'];
    $originalUri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Only remove ingress path from REQUEST_URI for Yii routing
    if (strpos($originalUri, $ingressPath) === 0) {
        $_SERVER['REQUEST_URI'] = substr($originalUri, strlen($ingressPath));
        if (empty($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }
    }
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
