<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Provider */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="provider-form">
<?php \yii\widgets\Pjax::begin(['id' => 'pjax-provider-form']) ?>
<?php $form = ActiveForm::begin(['id'=>'provider-form','options' => ['data-pjax' => true ]]); ?>
 <div class="popup-wheat-bg ">
    <?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>
    <?php 
//        $roleId =   $model->role_id;
//        $selected    =   array();
//        $selected[$roleId]   =   array('Selected'=>true);    
//        $options['options']    = $selected;
//        $options['prompt']      =   'Select Role';
//        //$options['multiple']    =   'multiple';
//        echo $form->field($roleSearchModel, 'role')->dropDownList($dataProvider, $options)->label('Role');       
    ?>
    <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>
    <?= $form->field($model, 'email_id')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'mobile_number')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'address')->textarea(['maxlength' => true]) ?>
    <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'state')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'country')->textInput(['maxlength' => true]) ?> 
    <?php        
    /*   echo $form->field($model, 'active_status')->radioList(  [
         '1'=>'Active',
         '0'=>'Inactive'
    ])->label('Active Status');*/
    ?>
    
   </div>   
<div class="modal-footer">
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
</div>
    <?php ActiveForm::end(); ?>
    <?php \yii\widgets\Pjax::end() ?>
</div>
