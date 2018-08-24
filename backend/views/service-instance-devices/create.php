<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\ServiceInstanceDevices */

$this->title = 'Create Service Instance Devices';
$this->params['breadcrumbs'][] = ['label' => 'Service Instance Devices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-instance-devices-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
