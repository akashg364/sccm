<?php

 $host = getenv('localhost');
 $dbname = getenv('sccm');

return [
    'class' => 'yii\db\Connection',
    'dsn' => "mysql:host=localhost;dbname=db_sccm",
    'username' =>  'root', //getenv('root'),
    'password' => '',
    'charset' => 'utf8',
];
// $host = getenv('MYSQL_HOST');
// $dbname = getenv('MYSQL_DATABASE');
//
//return [
//    'class' => 'yii\db\Connection',
//    'dsn' => "mysql:host={$host};dbname={$dbname}",
//    'username' => getenv('MYSQL_USER'),
//    'password' => getenv('MYSQL_PASSWORD'),
//    'charset' => 'utf8',
//];