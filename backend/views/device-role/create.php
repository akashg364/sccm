<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\DeviceRole */

$this->title = 'Create Device Role';
$this->params['breadcrumbs'][] = ['label' => 'Device Roles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-role-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
