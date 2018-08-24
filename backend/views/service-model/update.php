<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceModel */

$this->title = 'Update Service Model: '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Service Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="service-model-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
         'sub_service_id'=>$sub_service_id,
          'topology_id'=>$topology_id,
    ]) ?>

</div>
