<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\User;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\DeviceRoleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Device Roles';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-role-index">

    <h1><?php // echo Html::encode($this->title) ?></h1>
    <?php  
			//echo Yii::$app->session->getFlash('error');
	?>
	
	<?php  echo Yii::$app->session->getFlash('success');
	?>
        <?php   
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        //if (trim($user['user_type']) == 'superuser' || trim($user['user_type']) == 'superadmin') { ?>
            <p>
                <?php echo Html::a('Create Device Role', ['create'], ['class' => 'btn-add showModalButton']) ?>
            </p>
       
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

           // 'id',
            'role_name',
           // 'created_by',
           // 'modified_by',
            'created_date',
            //'modified_date',
            //'is_active',

          //  ['class' => 'yii\grid\ActionColumn'],
		   ['class' => 'common\components\yii\ActionColumn'],
        ],
    ]); ?>
</div>
