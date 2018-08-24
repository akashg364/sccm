<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\L2Testing */

$this->title = 'Update L2 Testing: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'L2 Testings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="l2-testing-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
