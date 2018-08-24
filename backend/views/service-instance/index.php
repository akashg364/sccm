<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\ServiceInstanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Service Instances';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-instance-index">

<!--    <h1><?= Html::encode($this->title) ?></h1>-->
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class="action-btn">
        <?= Html::a('Create Service Instance', ['create'], ['class' => 'btn-add showModalButton',"title"=>"Service Instance Create"]) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'service_order_id',
            [
               'attribute'=>'company_name',
               'value'=> 'customer.company_name'
            ],
            [
               'attribute'=>'service_model',
               'value'=> 'serviceModel.name'
            ],			
            [
                'label'=>'Deployment Status',
                'format' => 'raw',
                'value' => function ($model) {
                    if($model->id > 0){
                        return '<span style="color:'.$model->serviceStatusColorArr[$model->status].'">'.$model->serviceStatusArr[$model->status].'</span>';
                    }
                }
            ],
            //'user_defined_data:ntext',
            //'system_defined_data:ntext',
            //'nso_payload:ntext',
            //'scheduled_status',
            //'scheduled_date',
            //'is_active',

            [
				'class' => 'common\components\yii\ActionColumn',
				'template' => '{view} &nbsp;&nbsp; {delete_service}',
				 'buttonOptions' => ['modalType' => 'modal-full'],
				 'buttons'=> [
						'delete_service' => function($url,$model,$key){
							return Html::a("Delete Service",Url::to(["service-instance/delete-service","id"=>$model->id]),[
								"class"						=> "js-delete-service",
								"data-loading-text"	=> "Deleting .....",
							]);
						}
				 ],
			],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
<?php 
	$this->registerJsFile(Yii::$app->request->baseUrl."/js/service-instance.js",['depends' => [yii\web\JqueryAsset::className()]]);
?>
