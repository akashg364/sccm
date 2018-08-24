<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Services;
use app\models\SubServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TopologySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Topologies';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="topology-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

     <div class="action-btn">
        <?= Html::a('Create Topology', ['create'], ['class' => 'btn-add showModalButton',"title"=>"Create Topology"]) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'description',
            'created_date',
            'updated_date',
            ['class' => 'common\components\yii\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
