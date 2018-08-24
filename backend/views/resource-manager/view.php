<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\ResourceManager */

$model->setCreatedByUpdatedByUser();
$this->title = "Resource Pool : ".$model->parameter_name;
$this->params['breadcrumbs'][] = ['label' => 'Resource Managers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="resource-manager-view">

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
           'customer.company_name',
            'type',
            'dataType.data_type',
            'value_type',
            'variable_name',
            'variable_value',
            'created_by',
            'created_date',
            'updated_by',
            'updated_date',
        ],
    ]) ?>

</div>