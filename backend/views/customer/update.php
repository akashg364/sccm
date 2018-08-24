<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Customer */

$this->title = Yii::t('app', 'Update Customer: {nameAttribute}', [
    'nameAttribute' => $model->company_name,
]);$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Customers'), 'url' => ['/customer']];
$this->params['breadcrumbs'][] = ['label' => $model->company_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="customer-update">
    <?= $this->render('_form', [
        'model' => $model,
//        'dataRoles'  =>  $dataRoles,
        'dataProvider'  =>  $dataProvider,
        'user' => $user
//        'roleSearchModel'   =>  $roleSearchModel
    ]) ?>

</div>