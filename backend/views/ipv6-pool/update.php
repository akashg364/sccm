<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6Pool */

$this->title = 'Update Ipv6 Pool: ' . $model->pool . '/' . $model->subnet;
$this->params['breadcrumbs'][] = ['label' => 'Ipv6 Pools', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ipv6-pool-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
