<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

$this->title = 'CCM - Login';
?>
 <?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => [
                'class' => "form-signin"
            ]
  ]); ?>
        <?= $form->field($model, 'username')->textInput(['autofocus' => true,"class"=>"form-control signin-input","placeholder"=>"Enter Username"])->label(false) ?>

        <?= $form->field($model, 'password')->passwordInput(["class"=>"form-control signin-input","placeholder"=>"Enter Password"])->label(false) ?>
  
        <div id="remember" class="checkbox remember-div">
             <?= $form->field($model, 'rememberMe')->checkbox([
              'template' => '{input}<label for="checkbox-1" class="checkbox-custom-label">Remember me</label>{error}',
              "class"=>"checkbox-custom",
              "id"=>"checkbox-1",
        ]) ?>
        </div>

  
  <div class="row">
    <div class="col-md-12">
       <a href="request-password-reset" class="forgot-password">Forgot Password</a> 
    </div>
    <div class="col-md-12">
      <button class="btn btn-block btn-signin" type="submit">Sign in</button>
    </div>
  </div>
<?php ActiveForm::end(); ?>