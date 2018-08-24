<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ServiceInstanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Service Status';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-instance-index">

<!--    <h1><?= Html::encode($this->title) ?></h1>-->
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

    <p class="action-btn">
    </p>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'service_order_id',
            [
                'attribute' => 'company_name',
                'value' => 'customer.company_name'
            ],
            [
                'attribute' => 'service_model',
                'value' => 'serviceModel.name'
            ],
            'device_list',
            [
                'label' => 'Deployment Status',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->id > 0) {
                        return '<span style="color:' . $model->serviceStatusColorArr[$model->status] . '">' . $model->serviceStatusArr[$model->status] . '</span>';
                    }
                }
            ],
            [
                'label' => 'Ansible Status',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->id > 0) {
                        return '<span style="color:' . $model->serviceStatusColorArr[$model->ansible_status] . '">' . $model->serviceStatusArr[$model->ansible_status] . '</span>';
                    }
                }
            ],
            //'user_defined_data:ntext',
            //'system_defined_data:ntext',
            //'nso_payload:ntext',
            //'scheduled_status',
            //'scheduled_date',
            //'is_active',
            		
            array(
                
                'label' => 'Compare Config',
                'format' => 'raw',
                'value' => function ($data) {

                    return (isset($data['id'])) ? Html::a('Compare', array('compare/compare-config', 'id' => $data['id']),  array('target' => '_blank', 'data-pjax'=> '0')) : 'view';
                }, 
            ),            
        ],
    ]);
    ?>
    <?php Pjax::end(); ?>
</div>   
<?php
$this->registerJsFile(Yii::$app->request->baseUrl . "/js/service-instance.js", ['depends' => [yii\web\JqueryAsset::className()]]);
?>
