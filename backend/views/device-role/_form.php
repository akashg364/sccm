<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\DeviceRole */
/* @var $form yii\widgets\ActiveForm */
?>

<!-- <div class="device-role-form"> -->
<div class="popup-wheat-bg ">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'role_name')->textInput(['maxlength' => true]) ?>

    <?php // echo $form->field($model, 'created_by')->textInput() ?>

    <?php // echo $form->field($model, 'modified_by')->textInput() ?>

    <?php // echo $form->field($model, 'created_date')->textInput() ?>

    <?php // echo $form->field($model, 'modified_date')->textInput() ?>

    <?php //echo $form->field($model, 'is_active')->dropDownList(['0', '1']); ?>
	
	<?php
      //echo $form->field($model, 'is_active')->dropDownList(['1' => '1', '0' => '0']);
	  echo $form->field($model, 'is_active')->dropDownList(['1' => 'Active', '0' => 'In Active']);
    ?>
	
	

    <!-- <div class="form-group"> -->
	<div class="modal-footer">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
