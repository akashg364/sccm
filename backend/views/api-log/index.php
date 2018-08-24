<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ApiLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Api Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-log-index">

    <h1></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'api_url',
            'request_method',
            'request:ntext',
            'response:ntext',
            //'log_details:ntext',
            //'log_file_path',
            //'service_template_id',
            'created_date',
            //'created_by',
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
