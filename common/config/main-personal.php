<?php
ini_set('display_errors','On');
error_reporting(E_ALL);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

$config['components']['db'] = [
	'class' => 'yii\db\Connection',
	'dsn' => 'mysql:host=localhost;dbname=db_sccm',
	'username' => 'root',
	'password' => '',
	'charset' => 'utf8',
];