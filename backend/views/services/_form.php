<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Services */
/* @var $form yii\widgets\ActiveForm */
?>

<div class=""> 
    <?php $form = ActiveForm::begin(["id"=>"service-form"]); ?>
    <div class="popup-wheat-bg">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'description')->textarea(['rows' => '6']) ?>

        <?= $form->field($model, 'ref_id')->textInput(['maxlength' => true]) ?>

        <?php echo Yii::$app->yiiHelper->getActiveStatusField($form,$model);?>
    </div>
    
    <div class="modal-footer ">
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-swim', "data-dismiss" => "modal"]) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php 

$this->registerJs('   
//isAjaxSubmitForm = true;
');