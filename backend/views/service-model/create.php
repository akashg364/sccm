<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\ServiceModel */

$this->title = 'Create Service Model';
$this->params['breadcrumbs'][] = ['label' => 'Service Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-model-create">

    <h1><?php // echo  Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'sub_service_id'=>$sub_service_id,
          'topology_id'=>$topology_id,
    ]) ?>

</div>
