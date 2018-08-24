<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Device */

$this->title = 'Create Device';
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
