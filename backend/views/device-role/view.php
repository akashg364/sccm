<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\DeviceRole */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Device Roles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-role-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php // echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php /*echo Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])  **/ ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'role_name',
            'created_by',
            'updated_by',
            'created_date',
            'updated_date',
            'is_active',
        ],
    ]) ?>

</div>
