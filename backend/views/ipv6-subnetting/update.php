<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6Subnetting */

$this->title = 'Update Ipv6 Subnetting: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Ipv6 Subnettings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ipv6-subnetting-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
