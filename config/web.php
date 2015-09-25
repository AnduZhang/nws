<?php
use kartik\mpdf\Pdf;
$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'gii' => 'yii\gii\Module',

        'user' => [
            'class' => 'dektrium\user\Module',
            'controllerMap' => [
                'security' => 'app\controllers\user\SecurityController',
                'registration' => 'app\controllers\user\RegistrationController',
                'settings' => 'app\controllers\user\SettingsController'

            ],

            'modelMap' => [
                'User' => 'app\models\User',
                'LoginForm' => 'app\models\LoginForm',
                'SettingsForm' => 'app\models\SettingsForm',
                'RegistrationForm' => 'app\models\RegistrationForm',
            ],
            'enableUnconfirmedLogin' => true,
            'enableConfirmation' => false,
            'rememberFor'=>6000,
            'mailer' => [
                'sender'                => 'no-reply@myhost.com',
                'welcomeSubject'        => 'Welcome subject',
                'confirmationSubject'   => 'Confirmation subject',
                'reconfirmationSubject' => 'Email change subject',
                'recoverySubject'       => 'Recovery subject',
            ],
        ],
        // ...
    ],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => ['@app/views' => 'themes/nres'],
                'baseUrl'   => 'themes/nres'
            ]
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'YGip3RoJQzpW5UvqoaW7w8TvbGDkbAqB',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'CAPParser' => [
            'class' => 'app\components\CAPParser',
        ],
        'CSVParser' => [
            'class' => 'app\components\CSVParser',
        ],
        'LSRParser' => [
            'class' => 'app\components\LSRParser',
        ],
        'PointInPolygon' => [
            'class' => 'app\components\PointInPolygon',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,

//            'transport' => [
//                'class' => 'Swift_SmtpTransport',
//                'host' => 'smtp.gmail.com',
//                'username' => 'bits.bone@gmail.com',
//                'password' => 'ZXasqw!@',
//                'port' => '587',
//                'encryption' => 'tls',
//            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['alerts'],
                    'logFile' => '@app/runtime/logs/parsealerts.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['lsr'],
                    'logFile' => '@app/runtime/logs/parselsr.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['cap'],
                    'logFile' => '@app/runtime/logs/parsecap.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => ['_POST'],
                    'levels' => ['info'],
                    'categories' => ['alertExport'],
                    'logFile' => '@app/runtime/logs/alertExport.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],

            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => ['registered'],
        ],
        'urlManager' => [
            'enablePrettyUrl' => false,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [

            ],
            // ...
        ],
        'response' => [
            'formatters' => [
                'pdf' => [
                    'class' => 'robregonm\pdf\PdfResponseFormatter',
                ],
            ]
        ],
        'pdf' => [
            'class' => Pdf::classname(),
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_FILE,
            // refer settings section for all configuration options
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
