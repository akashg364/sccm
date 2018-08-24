<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceInstanceDevicesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="service-instance-devices-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'service_instance_id') ?>

    <?= $form->field($model, 'template_id') ?>

    <?= $form->field($model, 'device_id') ?>

    <?= $form->field($model, 'user_defined_data') ?>

    <?php // echo $form->field($model, 'system_defined_data') ?>

    <?php // echo $form->field($model, 'nso_payload') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'created_date') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <?php // echo $form->field($model, 'updated_date') ?>

    <?php // echo $form->field($model, 'is_active') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
