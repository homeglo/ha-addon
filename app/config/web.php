<?php

date_default_timezone_set('America/New_York');

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'gui' => [
            'class' => app\modules\gui\GuiModule::class
        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\HgUser',
            'enableAutoLogin' => false,
            // DISABLED: User authentication for local Home Assistant setup
        ],
        'session' => [
            'class' => 'yii\web\DbSession',
            'timeout'=> 60 * 60 * 24 * 30 //1 month
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'eHmld8OgQ_8GVtOZ2AxARojaFflmQOzu',
            // Handle Home Assistant ingress
            'baseUrl' => !empty($_SERVER['HTTP_X_INGRESS_PATH']) ? $_SERVER['HTTP_X_INGRESS_PATH'] : '',
            'scriptUrl' => !empty($_SERVER['HTTP_X_INGRESS_PATH']) ? $_SERVER['HTTP_X_INGRESS_PATH'] : '',
        ],
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'exportInterval'=>1,
                    'levels' => ['info','trace','error','warning'],
                    'logVars' => [],
                    'logTable'=>'log',
                    'except' => [
                        'yii\db\*',
                        'yii\web\*',
                        'yii\base\*',
                        'yii\debug\*'
                    ]
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // Home Assistant API endpoints
                'api/ha/test' => 'home-assistant/test-connection',
                'api/ha/sync/devices' => 'home-assistant/sync-devices',
                'api/ha/sync/location' => 'home-assistant/sync-location',
                'api/ha/sync/all' => 'home-assistant/sync-all',
                'api/ha/status' => 'home-assistant/status',
                
                //'/<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                //'/<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
            ],
        ],
        'db' => $db
    ],
    'params' => $params,
];

if (1==1) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return $config;
