<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'CAPParser' => [
            'class' => 'app\components\CAPParser',
        ],
        'LSRParser' => [
            'class' => 'app\components\LSRParser',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'exportInterval' => 1,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => ['_POST'],
                    'levels' => ['info'],
                    'categories' => ['cron'],
                    'logFile' => '@app/runtime/logs/cron.log',
                    'exportInterval' => 1,
                ],
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        'db' => $db,
    ],
    'params' => $params,
];
