<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Customer Management');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-index">
    <?php //Pjax::begin(); ?>
<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

<!--    <p>
        //<?= Html::a(Yii::t('app', 'Create Customer'), ['create'], ['class' => 'btn btn-success showModalButton', 'title' => 'Create Customer']) ?>
    </p>-->
    <h1></h1>
    <p>
        <?php
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        if (trim($user['user_type']) == 'provideruser' || strstr($user['user_type'], 'super')) {
            echo Html::a('Create Customer', ['create'], ['class' => 'btn-add showModalButton', 'title' => 'Create Customer']);
        }
        ?>
    </p>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}",
        'columns' => [
           ['class' => 'yii\grid\SerialColumn'],
//            'id',
//            'first_name',
//            'last_name',
            // 'company_name',
            'customer_id',
            [
                'attribute' => 'company_name',
                'label' => 'Customer Name',
                'value' => function($data) {
                    if (empty($data->providerName)) {
                        return $data->company_name;
                    } 
                    else {
                        return $data->company_name;
                    }
                },
            ],
                [
                'attribute' => 'On Board Status',
                'format' => 'raw',
                'value' => [$searchModel, 'getStatusLink'],
            ],
            [
              'attribute' => 'Active Status',
              'format' => 'raw',
              'value' => [$searchModel, 'getActiveInactiveLink',],
              ], 
                ['class' => 'common\components\yii\ActionColumn', 'template' => '{view} {update}'],
        ],
    ]);
    ?>
<?php //Pjax::end();  ?>
</div>
