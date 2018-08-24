<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6Subnetting */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ipv6-subnetting-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'useable_ips')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'total_ips')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subnet')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_active')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
