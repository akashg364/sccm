<?php
$config = [
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'components' => [
		'authManager' => [
            'class' => 'yii\rbac\DbManager' //or 'yii\rbac\PhpManager'
        ],
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
			],
		],
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			'viewPath' => '@common/mail',
			// send all mails to a file by default. You have to set
			// 'useFileTransport' to false and configure a transport
			// for the mailer to send real emails.
			'useFileTransport' => true,
		],
		'yiiHelper'=>[
			'class'=>'common\components\yii\YiiHelper'
		],
		'inventoryApi'=>[
			'class'=>'common\components\InventoryApi'
		]
	],
];

require __DIR__ . '/main-' . strtolower(APP_MODE) . ".php";

if (in_array(APP_MODE, ["LOCAL", "STAGE"])) {
	// configuration adjustments for 'dev' environment
	$config['bootstrap'][] = 'debug';
	$config['modules']['debug'] = [
		'class' => 'yii\debug\Module',
	];

	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
		'allowedIPs' => [@$_SERVER["REMOTE_ADDR"]],
	];
}

return $config;