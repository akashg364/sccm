<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Role */

$this->title = Yii::t('app', 'Create Role');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Roles'), 'url' => ['/role']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="role-create">

    <h1><?= Html::encode($this->title) ?></h1>
   
    <?= $this->render('_form', [
        'model' => $model,
        'dataProvider'  => $dataProvider,
        'modelPrivilege'    => $modelPrivilege
    ]) ?>

</div>
