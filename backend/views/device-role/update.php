<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\DeviceRole */

$this->title = "Update Device Role: {$model->role_name}";
$this->params['breadcrumbs'][] = ['label' => 'Device Roles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="device-role-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
