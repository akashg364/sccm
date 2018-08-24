<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\ServiceModel;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ServiceModelSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Service Models';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="service-model-index">

    <h1><?php // echo Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus" area-hiden="true"></i> Create Service Model', ['create'], ['class' => 'btn-add showModalButton', 'title' => 'Create service model','modalType'=>"modal-lg"]) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        // 'layout'=> "{summary}\n<div class='tabe-grid-view'>{items}</div>\n{pager}",
        // 'tableOptions'=>[
        //         "class"=>"table-style tableBodyScroll"
        // ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

          //  'id',
            'name',
            [
                'attribute'=>'service_name', 
                'value'=>'service.name',
            ],
             [
                'attribute'=>'sub_service_name', 
                'value'=>'subService.name',
            ],
             [
                'attribute'=>'topology_name', 
                'value'=> 'topology.name',
            ],
            [
                'attribute' => 'Delete Action',
                'format' => 'raw',
                'value' => [$searchModel, 'getDeleteActionLink',],
            ],
            //'description:ntext',
            //'created_by',
            //'created_on',
            //'updated_by',
            //'updated_on',
            [
                'class' => 'common\components\yii\ActionColumn',
                'buttonOptions' => ['modalType' => 'modal-lg'],
                'visibleButtons' => [
                    'delete' => function ($model, $key, $index) {
                        return $model->is_deleted === 0 ? true : false;
                    },
                ],
                "buttons"=>[
                    'view'=>function($url, $model, $key){
                        return Html::a("<span class='fas fa-eye fa-lg'></span>",$url,["class"=>"showModalButton","modalType"=>"modal-full"]);
                    }
                ],
            ],
        ],
    ]);
    ?>
</div>
