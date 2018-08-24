<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\DataTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="data-type-index">

    <h1><?php // echo Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
<?php  if($user->user_type ==  'admin'){ ?>
    <p>
        <?= Html::a('Create Data Type', ['create'], ['class' => 'btn-add showModalButton']) ?>
    </p>
    <?php } ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],

            'id',
            'data_type',
            // 'created_by',
            // 'modified_by',
            'created_date',
            // 'modified_date',
            [
               'attribute'=>'is_active',
               'value'=> function($model){
                    return getYesNo($model->is_active);
                } 
            ],
            [
                //'class' => 'yii\grid\ActionColumn',
				'class' => 'common\components\yii\ActionColumn',
				
                'template'=> '{update} {delete}'
            ],
        ],
    ]); ?>
</div>
