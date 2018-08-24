<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6PoolAssignment */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ipv6 Pool Assignments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv6-pool-assignment-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'pool_id',
            'service_instance_id',
            'subnet',
            'usable_ips',
            'start_ip',
            'end_ip',
            'ip_count',
            'device_id',
            'is_full',
            'created_date',
            'updated_date',
            'is_active',
        ],
    ]) ?>

</div>
