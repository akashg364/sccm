<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\ComponentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Components';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="component-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->
 <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

   <div class="action-btn">
        <?= Html::a('Create Component', ['create'], ['class' => 'btn-add showModalButton']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'name',
            'system_name',
            'description:ntext',
            'blocks:ntext',
             [
                'label'=> 'Component Blocks',
                'format'=>'raw',
                'value'=>function($model){
                     $html = "<ol>";
                     if(!empty($model->componentBlocks)){
                        foreach ($model->componentBlocks as $componentBlock) {
                            $html .="<li>".$componentBlock->block_name."</li>";
                        }
                    } 
                    $html .= "</ol>";
                    return $html;
                }
            ],
            //'created_by',
            //'created_date',
            //'updated_by',
            //'updated_date',

             ['class' => 'common\components\yii\ActionColumn'],
        ],
    ]); ?>
 <?php Pjax::end(); ?>
</div>
