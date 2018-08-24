<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\VariablesMasterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Variables Masters';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="variables-master-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="action-btn">
        <?= Html::a('Create Variables Master', ['create'], ['class' => 'btn btn-add showModalButton','modalType'=>'modal-lg']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'variable_name',
            'type',
            'value_type',
            'data_type_id',
            //'value1_label',
            //'value2_label',
            //'active_status',
            //'created_by',
            //'created_date',
            //'updated_by',
            //'updated_date',

             [  
                'class' => 'common\components\yii\ActionColumn',
                // 'buttonOptions' => ['modalType' => 'modal-lg'],
                "buttons"=>[
                    'update'=>function($url, $model, $key){
                        return Html::a("<span class='fas fa-edit fa-lg'></span>",$url,["class"=>"showModalButton","modalType"=>"modal-lg"]);
                    }
                ],
            ],
        ],
    ]); ?>
</div>
