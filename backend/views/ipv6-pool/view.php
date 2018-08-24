<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6Pool */

$this->title = $model->pool . '/' . $model->subnet;
$this->params['breadcrumbs'][] = ['label' => 'Ipv6 Pools', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv6-pool-view">
    <div class="popup-wheat-bg">
        <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php //Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php /*Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'pool',
            'subnet',
            'created_by',
            'created_date',
            'updated_by',
            'updated_date',
            [
                'attribute' => 'is_full',
                'label' => 'Pool Status',
                'format' => 'raw',
                'value' => function ($data) {
                    if($data->is_full == 0)
                        return "Available";
                    else if($data->is_full == 1)
                        return "Full";
                }
            ],
            [
                'attribute' => 'is_active',
                'label'=>'Status',
                'format' => 'raw',
                'value' => function ($data) {
                    if($data->is_active == 1)
                        return "Active";
                    else if($data->is_active == 0)
                        return "InActive";
                }
            ],
        ],
    ]) ?>
    </div>
</div>
