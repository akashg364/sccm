<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Provider */

$this->title = Yii::t('app', 'Update Provider: {nameAttribute}', [
    'nameAttribute' => $model->company_name,
]);$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Providers'), 'url' => ['/provider']];
$this->params['breadcrumbs'][] = ['label' => $model->company_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="provider-update">
    <?= $this->render('_form', [
        'model' => $model,
        'dataProvider'  =>  $dataProvider,
//        'roleSearchModel'   =>  $roleSearchModel
    ]) ?>

</div>
