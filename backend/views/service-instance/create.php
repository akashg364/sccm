<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\ServiceInstance */

$this->title = 'Create Service Instance';
$this->params['breadcrumbs'][] = ['label' => 'Service Instances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-instance-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
