<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\Ipv4PoolSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ipv4 Pools';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv4-pool-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php Pjax::begin(); ?>
    <p class="action-btn">
        <?= Html::a('Create Ipv4 Pool', ['create'], ['class' => 'btn-add showModalButton', "title" => "Create Ipv4 Pool"]) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'pool',
            ['class' => 'common\components\yii\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>