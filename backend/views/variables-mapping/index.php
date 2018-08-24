<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\VariablesMappingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Variables Mappings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variables-mapping-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="action-btn">
        <?= Html::a('Create Variables Mapping', ['create'], ['class' => 'btn btn-add showModalButton']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute'=>'variable_name',
                'value'=>'variableMaster.variable_name',
            ],
            [
                'attribute'=>'company_name',
                'value'=>'customer.company_name',
            ],

            ['class' => 'common\components\yii\ActionColumn'],
        ],
    ]); ?>
</div>
