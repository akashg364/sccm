<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6PoolAssignment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ipv6-pool-assignment-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pool_id')->textInput() ?>

    <?= $form->field($model, 'service_instance_id')->textInput() ?>

    <?= $form->field($model, 'subnet')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'usable_ips')->textInput() ?>

    <?= $form->field($model, 'start_ip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'end_ip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ip_count')->textInput() ?>

    <?= $form->field($model, 'device_id')->textInput() ?>

    <?= $form->field($model, 'is_full')->textInput() ?>

    <?= $form->field($model, 'created_date')->textInput() ?>

    <?= $form->field($model, 'updated_date')->textInput() ?>

    <?= $form->field($model, 'is_active')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
