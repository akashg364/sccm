<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SubServicesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sub Services';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sub-services-index">

    <!--<h1><?php  //Html::encode($this->title) ?></h1>-->
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="action-btn">
        <?= Html::a('Create Sub Services', ['create'], ['class' => 'btn-add showModalButton',"title"=>"Create Sub Services","modalType"=>"modal-lg"]) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

           'ref_id',
            'name',
            // 'description',
            'created_date',

            //'modified',

            [
                'class' => 'common\components\yii\ActionColumn',
                'buttonOptions' => ['modalType' => 'modal-lg'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
