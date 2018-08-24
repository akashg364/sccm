<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\L2TestingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'L2 Testings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="l2-testing-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create L2 Testing', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
               'attribute' => 'Service Name',
               'value' => 'service.name'
            ],
            [
               'attribute' => 'Ref_id',
               'value' => 'subService.ref_id'
            ],
            [
               'attribute' => 'Sub Service Name',
               'value' => 'subService.name'
            ],
            [
               'attribute' => 'Engineer',
               'value' => 'networkEngineer.name'
            ],
            'bangalore_lab_status',
            //'bangalore_datetime',
            'reliance_lab_status',
            //'reliance_datetime',
            //'status',

	    ['class' => 'common\components\yii\ActionColumn', 'template' => '{view} {update} {delete}'],
        ],
    ]); ?>
</div>
