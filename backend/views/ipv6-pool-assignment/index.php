<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\Ipv6PoolAssignmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ipv6 Pool Assignments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv6-pool-assignment-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Ipv6 Pool Assignment', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'pool_id',
            'service_instance_id',
            'subnet',
            'usable_ips',
            //'start_ip',
            //'end_ip',
            //'ip_count',
            //'device_id',
            //'is_full',
            //'created_date',
            //'updated_date',
            //'is_active',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
