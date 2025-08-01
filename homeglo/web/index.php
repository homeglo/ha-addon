<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', $_ENV['ENV'] ?? 'dev');

ini_set('error_reporting','E_ALL & ~E_NOTICE & ~E_DEPRECATED');
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Fix for Home Assistant ingress - ensure SCRIPT_NAME is set properly
if (!empty($_SERVER['HTTP_X_INGRESS_PATH'])) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['HTTP_X_INGRESS_PATH'] . '/index.php';
    $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
    
    // Fix REQUEST_URI to remove ingress path for Yii routing
    $originalUri = $_SERVER['REQUEST_URI'] ?? '';
    $ingressPath = $_SERVER['HTTP_X_INGRESS_PATH'];
    
    // Remove ingress path from request URI
    if (strpos($originalUri, $ingressPath) === 0) {
        $_SERVER['REQUEST_URI'] = substr($originalUri, strlen($ingressPath));
    }
    
    // Remove /index.php if present
    $_SERVER['REQUEST_URI'] = preg_replace('/^\/index\.php/', '', $_SERVER['REQUEST_URI']);
    
    // If empty, set to /
    if (empty($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = '/';
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

$app->on(yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // Manually set pathInfo from REQUEST_URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $requestUri = strtok($requestUri, '?'); // Remove query string
    $requestUri = ltrim($requestUri, '/');
    
    if (!empty($requestUri) && $requestUri !== 'index.php') {
        Yii::$app->request->setPathInfo($requestUri);
    }
});

$app->run();
