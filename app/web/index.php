<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', $_ENV['ENV'] ?? 'dev');

ini_set('error_reporting','E_ALL & ~E_NOTICE & ~E_DEPRECATED');

require __DIR__ . '/../vendor/autoload.php';
(Dotenv\Dotenv::createImmutable(__DIR__ . '/../'))->safeLoad();


require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
