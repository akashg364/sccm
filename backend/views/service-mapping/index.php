<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ServiceMappingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Service Mappings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-mapping-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="action-btn">
        <?= Html::a('Create Service Mapping', ['create'], ['class' => 'btn-add showModalButton']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute'=>'service_name',
                'value'=> 'service.name',
            ],
           [
                'attribute'=>'sub_service_name',
                'value'=>'subService.name',
            ],
            [
                'attribute'=>'topology_name',
                'value'=>  'topology.name',
            ],
            
            // 'created_by',
            //'created_date',
            //'updated_by',
            //'updated_date',
            //'active_status',

            ['class' => 'common\components\yii\ActionColumn'],
        ],
    ]); ?>
</div>
