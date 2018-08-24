<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Topology */

$this->title = 'Update Topology: '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Topologies', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="topology-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
