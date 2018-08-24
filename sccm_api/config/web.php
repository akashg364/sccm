<?php

/**
 * @SWG\Swagger(
 *   info={
 *     "title"="REST API",
 *     "version"="0.0.1"
 *   },
 *   host=API_HOST,
 *   basePath="/api"
 * )
 *
 * @SWG\SecurityScheme(
 *   securityDefinition="jwt",
 *   description="add 'Bearer ' before jwt token",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization"
 * )
 */

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');
$db_cnaap = require(__DIR__ . '/db_cnaap.php');
$rules = require(__DIR__ . '/rules.php');

$config = [
    'id' => 'rest-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],

    /* waktu aplikasi */
    'timeZone' => 'Asia/Jakarta',

    /* module */
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/versiSatu',
            'class' => 'app\modules\versiSatu\v1',
        ],
    ],

    'components' => [
        'request' => [
            'cookieValidationKey' => 'BVWfyckvqYTzdr6YQcluvhXWLxAcGpwr',
            /* Enable JSON Input: */
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            /* Enable JSON Output: */
            'class' => 'yii\web\Response',
            'format' => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && is_array($response->data)) {
                    /* delete code param */
                    if (array_key_exists('code', $response->data)) {
                        unset($response->data['code']);
                    }

                    /* change status to statusCode */
                    if (array_key_exists('status', $response->data)) {
                        $response->data['statusCode'] = $response->data['status'];
                        unset($response->data['status']);
                    }
                }
            },
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            /* send all mails to a file by default. You have to set */
            /* 'useFileTransport' to false and configure a transport */
            /* for the mailer to send real emails. */
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'db_cnaap'=>$db_cnaap,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'docs' => 'site/docs',
                [
                    'pattern' => 'resource',
                    'route' => 'site/resource',
                    'suffix' => '.json'
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'controller' => ['v1'],
                    'extraPatterns' => $rules,
                ],
            ],
        ],
		
		'inventoryApi'=>[
			'class'=>'common\components\InventoryApi'
		]
		
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    /* configuration adjustments for 'dev' environment */
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        /* uncomment the following to add your IP if you are not connecting from localhost. */
         'allowedIPs' => ['127.0.0.1', '::1'], 
    ];

    /*$config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        /* uncomment the following to add your IP if you are not connecting from localhost. */
        /* 'allowedIPs' => ['127.0.0.1', '::1'], * /
    ];*/
}

return $config;
