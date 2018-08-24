<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Customer */

$this->title = Yii::t('app', 'Create Customer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Customers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-create">
    <?= $this->render('_form', [
        'model' => $model,
//        'dataRoles'  =>  $dataRoles,
        'dataProvider'  =>  $dataProvider,
        'user'  =>  $user
//        'roleSearchModel'   =>  $roleSearchModel
    ]) ?>

</div>
