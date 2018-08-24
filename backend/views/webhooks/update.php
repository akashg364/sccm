<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Webhooks */

$this->title = 'Update Webhooks: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Webhooks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id, 'created_date' => $model->created_date]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="webhooks-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
