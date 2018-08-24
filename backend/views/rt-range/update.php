<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\RtRange */

$this->title = 'Update Rt Range: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Rt Ranges', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="rt-range-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
