<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Device;

$deviceTypes = array_combine(Device::$deviceTypes, Device::$deviceTypes);
/* @var $this yii\web\View */
/* @var $model backend\models\Device */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="popup-wheat-bg ">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'hostname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sapid')->textInput() ?>

    <?= $form->field($model, 'loopback')->textInput() ?>
	
    <?= $form->field($model, 'ip_address')->textInput() ?>
	
    <?php echo $form->field($model, 'location')->textInput() ?>

     <?php echo $form->field($model, 'router_type')->dropDownList([""=>"Select Router Type"]+$deviceTypes) ?>
    <?php
		echo $form->field($model, 'device_type')->dropDownList(
            ['asr920' => 'ASR920', 'asr903' => 'ASR903','asr9k' => 'ASR9K', 'ecr' => 'ECR']
    ); ?>
	
	
	
	

	<?php //echo $form->field($model, 'loopback')->dropDown() ?>

    <?php //echo $form->field($model, 'created_date')->textInput() ?>

    <?php //echo $form->field($model, 'modified_date')->textInput() ?>

    <?php //echo $form->field($model, 'created_by')->textInput() ?>

    <?php //echo $form->field($model, 'modified_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
	
    <?php ActiveForm::end(); ?>

</div>
