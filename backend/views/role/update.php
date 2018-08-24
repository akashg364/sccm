<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Role */

$this->title = Yii::t('app', 'Update Role: {nameAttribute}', [
    'nameAttribute' => $model->role,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Roles'), 'url' => ['/role']];
$this->params['breadcrumbs'][] = ['label' => $model->role, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="role-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'dataProvider'  => $dataProvider,
        'modelPrivilege'    => $modelPrivilege
    ]) ?>

</div>
