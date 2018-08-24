<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ServicesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Services';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="services-index">

   <!-- <h1><?= Html::encode($this->title) ?></h1>-->
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="action-btn">
        <?= Html::a("Create Services", ['create'], ['class' => 'btn-add showModalButton',"title"=>"Create Services"]) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
           // 'id',
            'name',
            'description',
            'created_date',
            'updated_date',

            ['class' => 'common\components\yii\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
