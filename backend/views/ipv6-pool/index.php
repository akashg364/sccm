<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\Ipv6PoolSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ipv6 Pools';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv6-pool-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php Pjax::begin(); ?>
    <p class="action-btn">
        <?= Html::a('Create Ipv6 Pool', ['create'], ['class' => 'btn-add showModalButton', "title" => "Create Ipv6 Pool"]) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'pool',
            'subnet',
            ['class' => 'common\components\yii\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>