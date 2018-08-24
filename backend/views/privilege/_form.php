<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Privilege */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="privilege-form">

    <?php $form = ActiveForm::begin(); ?>   

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
   <?php        
       echo $form->field($model, 'active_status')->radioList(  [
         '1'=>'Active',
         '0'=>'Inactive'
    ])->label('Active Status');
    ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>