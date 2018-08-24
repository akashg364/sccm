<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\ServiceInstanceDevices;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ServiceInstanceDevicesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Service Instance Devices';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="service-instance-devices-index">

<!--    <h1><?= Html::encode($this->title) ?></h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<!--    <p>
        <?php //Html::a('Create Service Instance Devices', ['create'], ['class' => 'btn btn-success']) ?>
    </p>-->

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            //'id',
            'template_id',
            [
                'label'=>'Service',
                'format' => 'raw',
                'value' => function ($searchModel) {
                    if($searchModel->template_id > 0)
                        return ServiceInstanceDevices::getServiceModelByTemplate($searchModel->template_id);
                }
            ],
            [
               'attribute'=>'instance',
               'value'=> 'serviceInstance.service_order_id'
            ],
            [
               'attribute'=>'device',
               'value'=> 'device.hostname'
            ],
            [
               'attribute'=>'role',
               'value'=> 'deviceRole.role_name'
            ],
			[
				'attribute'=>'user_defined_data',
				'format'=>'raw',
				'value'=>function($model){
					return jsonPretty(unserialize($model->user_defined_data));
				},
				"contentOptions"=>["style"=>"vertical-align:top;"]
				
			],
			[
				'attribute'=>'system_defined_data',
				'format'=>'raw',
				'value'=>function($model){	
					return jsonPretty(unserialize($model->system_defined_data));				
				},
				"contentOptions"=>["style"=>"vertical-align:top;"]
				
			],
			[
				'attribute'=>'nso_payload',
				'format'=>'raw',
				'value'=>function($model){	
					return jsonPretty($model->nso_payload);				
				},
				"contentOptions"=>["style"=>"vertical-align:top;"]
				
			],           
            [
				'class' => 'common\components\yii\ActionColumn',
				'template' => '{view}',
				 'buttonOptions' => ['modalType' => 'modal-full'],
			],
        ],
    ]); ?>
</div>
