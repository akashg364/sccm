<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\NetworkEngineers */

$this->title = 'Update Network Engineers: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Network Engineers', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="network-engineers-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
