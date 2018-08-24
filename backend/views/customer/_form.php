<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Customer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-form">
<?php \yii\widgets\Pjax::begin(['id' => 'pjax-customer-form']) ?>
<?php $form = ActiveForm::begin(['id'=>'customer-form','options' => ['data-pjax' => true ]]); ?>
    <div class="popup-wheat-bg ">
        <?= $form->field($model, 'customer_id')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>
    <?php 
        $user_type  =   $user->user_type;
        $reference_id    =   $user->reference_id;
        if($user_type   !=   'provider'){
        $providerId =   $model->provider_id;
        $selectedProvider   =   array();
        $selectedProvider[$providerId]   =   array('Selected'=>true);  
        $optionsProvided['options'] =   $selectedProvider;
        $optionsProvided['prompt'] =   'Select Provider';       
//        $role_id =   $model->role_id;
//        $selected    =   array();
//        $selected[$role_id]   =   array('Selected'=>true);    
//        $options['options']    = $selected;
//        $options['prompt']      =   'Select Role';
        //$options['multiple']    =   'multiple';
        echo $form->field($model, 'provider_id')->dropDownList($dataProvider, $optionsProvided)->label('Provider');
        }else{
            echo $form->field($model, 'provider_id')->hiddenInput(['value'=> $reference_id])->label(false);            
        }
//        echo $form->field($model, 'role_id')->dropDownList($dataRoles, $options)->label('Role');       
    ?>
    <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>
    <?= $form->field($model, 'email_id')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'mobile_number')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'address')->textarea(['maxlength' => true]) ?>
    <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'state')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'country')->textInput(['maxlength' => true]) ?> 
    <?php        
//      echo $form->field($model, 'active_status')->radioList(  [
//         '1'=>'Active',
//         '0'=>'Inactive'
//    ])->label('Active Status');
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
