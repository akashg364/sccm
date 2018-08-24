<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ResourceManagerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Resource Managers';
$this->params['breadcrumbs'][] = $this->title;

?>
 <?php ///\yii\helpers\Html::button('Add Resource Manager', ['value' => Url::to(["resource-manager/create"]), 'title' => 'Add Resource Manager', 'class' => 'showModalButton btn btn-success']); ?>
<div class="resource-manager-index">
    <h1></h1>
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

     <?php  //if($user->user_type ==  'admin'){ 
            if(strstr($user->user_type, 'super')){
     ?>
    <div class="action-btn">
        <?= Html::a('Create Resource Manager', ['create'], ['class' => 'btn-add showModalButton',"title"=>"Resource Manager Create"]) ?>
    </p>
     <?php } ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            //'id',
             [
               'attribute'=>'company_name',
               'value'=> 'customer.company_name'
             ],
            'type',
             [
               'attribute'=>'data_type',
               'value'=> 'dataType.data_type'
             ],
            'value_type',
            'variable_name',
            'variable_value',
            [
                'attribute' => 'Accept/Reject',
                'format' => 'raw',
                'value' => [$searchModel, 'getStatusLink'],
            ],
            [
                'attribute' => 'Active/Inactive',
                'format' => 'raw',
                'value' => [$searchModel, 'getActiveInactiveLink',],
            ],
            //'created_by',
            //'created_on',
            //'updated_by',
            //'updated_on',

            ['class' => 'common\components\yii\ActionColumn','template' => '{view} {update}'],
        ],
    ]); ?>
</div>
