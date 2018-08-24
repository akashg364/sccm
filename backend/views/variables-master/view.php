<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\VariablesMaster */

$model->setCreatedByUpdatedByUser();
$this->title = "Variable - ".$model->variable_name;
$this->params['breadcrumbs'][] = ['label' => 'Variables Masters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variables-master-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php // Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php /* Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])*/ ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'type',
            'value_type',
            [
                'attribute'=>'data_type_id',
                'value'=>function($model){
                   return $model->dataType->data_type;
                 }   
            ] , 
            'variable_name',
            'value1_label',
            'value2_label',
            [
               'attribute' =>'active_status',
                'value' => getYesNo($model->active_status)
            ],
            'created_by',
            'created_date',
            'updated_by',
            'updated_date',
        ],
    ]) ?>

</div>
