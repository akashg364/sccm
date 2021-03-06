<?php
require(__DIR__ . '/../../common/config/app.mode.php');
require(__DIR__ . '/../../common/components/Functions.php'); //Common Helper Function
$mainConfiguration = require(__DIR__ . '/../../common/config/main.php'); 

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    $mainConfiguration,
    require(__DIR__ . '/../config/main.php')
);

(new yii\web\Application($config))->run();
