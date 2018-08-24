<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6PoolAssignment */

$this->title = 'Update Ipv6 Pool Assignment: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Ipv6 Pool Assignments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ipv6-pool-assignment-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
