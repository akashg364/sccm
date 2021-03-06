<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceMapping */
$model->setCreatedByUpdatedByUser();
$this->title = $model->service->name." - ".$model->subService->name." - ".$model->topology->name;
$this->params['breadcrumbs'][] = ['label' => 'Service Mappings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-mapping-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php // Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php /* Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) */?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
             'service.name',
             'subService.name',
            'topology.name',
            'created_by',
            'created_date',
            'updated_by',
            'updated_date',
            [
               'attribute' =>'active_status',
                'value' => getYesNo($model->active_status)
            ]
        ],
    ]) ?>

</div>
