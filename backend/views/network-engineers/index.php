<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\NetworkEngineersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Network Engineers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="network-engineers-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p><?= Html::a('Create Network Engineers', ['create'], ['class' => 'btn btn-success']) ?></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'role',
            [                    
                'label'=> 'Status',                    
                'value' => function ($data) {
                    if($data->status == 1)
                        return "Active";
                    else if($data->status == 0)
                        return "InActive";
                }
            ],
            ['class' => 'common\components\yii\ActionColumn', 'template' => '{view} {update} {delete}'],
        ],
    ]); ?>
</div>