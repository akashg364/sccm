<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\RtRange */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="rt-range-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'customer_id')->textInput() ?>

    <?= $form->field($model, 'topology')->dropDownList([ 'Hub' => 'Hub', 'Spoke' => 'Spoke', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'start_value')->textInput() ?>

    <?= $form->field($model, 'end_value')->textInput() ?>

    <?= $form->field($model, 'last_used')->textInput() ?>

    <?= $form->field($model, 'is_active')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
