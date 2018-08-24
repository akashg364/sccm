<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProviderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Provider Management');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="provider-index">  

<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>
    <h1></h1>
    <p>
        <?php
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        if (trim($user['user_type']) == 'superuser') {
            echo Html::a('Create Provider', ['create'], ['class' => 'btn-add showModalButton', 'title' => 'Create Provider']);
        }
        ?>
    </p>


    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
//        'layout' => "{summary}\n{items}\n{pager}",
        'layout' => "{items}",
        'columns' => [
           ['class' => 'yii\grid\SerialColumn'],
//            'id',
            'company_name',
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

</div>
