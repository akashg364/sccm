<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'eEbDG7xzoMNTPPapjLyJ-PoPQUbzFb5P',
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

$config['components']['db'] = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=db_sccm',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];

return $config;
