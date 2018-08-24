<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \mdm\admin\models\form\Signup */

$this->title = Yii::t('rbac-admin', 'Create New User');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-signup">
     <h1><?= Html::encode($this->title) ?></h1> 
    <?php \yii\widgets\Pjax::begin(['id' => 'pjax-form-signup']) ?>
    <p>Please fill out the following fields to create a new user:</p>
    <?= Html::errorSummary($model)?>
    <div class="">
        <div class="">
            <?php $form = ActiveForm::begin(['id' => 'form-signup','options' => ['data-pjax' => true ]]); ?>
            <div class="popup-wheat-bg ">
             <?php
                $reference_id   =  $user->reference_id;
                $user_type  =   $user->user_type;
                //unset($roles['superadmin']);
                switch ($user_type) {
                    case (strstr($user_type, 'super')):
                     $arrUserType =array('customeruser' => 'CustomerUser', 'provideradmin' => 'ProviderAdmin','provideruser'=>'ProviderUser','superuser' =>  'SuperUser');
                echo $form->field($model, 'user_type')->dropDownList($arrUserType,
                        ['prompt'=>'Choose a User Type',
			  'onchange'=>'
                                var usertype    =   $(this).val().toLowerCase();
				$.post( "'.Yii::$app->urlManager->createUrl('user/lists?id=').'"+$(this).val(), function( data ) { 
                                    if(usertype.indexOf("super") != -1){
                                        $( ".field-signup-provider" ).hide(500);
                                        $( ".field-signup-customer" ).hide(500);
                                    }else if(usertype.indexOf("provider") != -1){                                       
                                        $( ".field-signup-provider" ).show(500);
                                        $( ".field-signup-customer" ).hide(500);
                                    }else if(usertype.indexOf("customer") != -1){
                                        $( ".field-signup-provider" ).show(500);
                                        $( ".field-signup-customer" ).show(500);
                                    }
				});
			'])->label('User Type'); ?>
            <?php                 
            echo $form->field($model, 'provider')->dropDownList($provider,
                     ['prompt'=>'Select Provider',
			  'onchange'=>'
				$.post( "'.Yii::$app->urlManager->createUrl('user/customers?id=').'"+$(this).val(), function( data ) { 
                                    if(data !="" && $("#signup-user_type").val() == "customeruser"){                                    
                                        $( "select#signup-customer" ).html(data);
                                        $( "select#signup-customer" ).show(500);
                                    }else{
                                        $( ".field-signup-customer" ).hide(500);
                                    }
				});
			'])->label('Provider'); ?>
            <?php
            echo $form->field($model, 'customer')->dropDownList(['select'=>'Select Customer']);
                    break;
                    case (strstr($user_type, 'provider')):
                        echo $form->field($model, 'user_type')->hiddenInput(['value'=> 'provideruser'])->label(false);
                        echo $form->field($model, 'reference_id')->hiddenInput(['value'=> $reference_id])->label(false);
                        break;
                    case (strstr($user_type, 'customer')):
                        echo $form->field($model, 'user_type')->hiddenInput(['value'=> 'customeruser'])->label(false);
                        echo $form->field($model, 'reference_id')->hiddenInput(['value'=> $reference_id])->label(false);
                        //echo $form->field($model, 'customer')->dropDownList(['select'=>'Select Customer'])
                }// switch ends here ?>
                <?= $form->field($model, 'username') ?>
                <?= $form->field($model, 'email') ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
            <?php        
                echo $form->field($model, 'status')->radioList(  [
                  '10'=>'Active',
                  '0'=>'Inactive'
             ])->label('Active Status');
           ?>
            </div>
            <div class="modal-footer">
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('rbac-admin', 'Create User'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>
            </div>    
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?php \yii\widgets\Pjax::end() ?>
</div>
