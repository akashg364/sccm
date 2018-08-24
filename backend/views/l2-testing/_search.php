<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\L2TestingSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="l2-testing-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'service_id') ?>

    <?= $form->field($model, 'sub_service_id') ?>

    <?= $form->field($model, 'network_engineer') ?>

    <?= $form->field($model, 'bangalore_lab_status') ?>

    <?php // echo $form->field($model, 'bangalore_datetime') ?>

    <?php // echo $form->field($model, 'reliance_lab_status') ?>

    <?php // echo $form->field($model, 'reliance_datetime') ?>

    <?php // echo $form->field($model, 'status') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
