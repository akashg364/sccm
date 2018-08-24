<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceInstanceDevices */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="service-instance-devices-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'service_instance_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'template_id')->textInput() ?>

    <?= $form->field($model, 'device_id')->textInput() ?>

    <?= $form->field($model, 'user_defined_data')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'system_defined_data')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'nso_payload')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'created_date')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <?= $form->field($model, 'updated_date')->textInput() ?>

    <?= $form->field($model, 'is_active')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
