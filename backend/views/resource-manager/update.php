<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ResourceManager */

$this->title = 'Update Resource Manager: '.$model->variable_name;
$this->params['breadcrumbs'][] = ['label' => 'Resource Managers', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="resource-manager-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'customer_name'=>$customer_name,
    ]) ?>

</div>
