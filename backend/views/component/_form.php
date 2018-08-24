<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Component;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\depdrop\DepDrop;


$componentBlockList = Component::getComponantBlockList();
/* @var $this yii\web\View */
/* @var $model backend\models\Component */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="">
    <?php $form = ActiveForm::begin(["id" => "component-form"]); ?>
    <div class="popup-wheat-bg">

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'system_name')->textInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'component_blocks')->widget(Select2::classname(), [
                'model' => $model,
                'data' => $componentBlockList,
                'initValueText' => @$component_blocks,
                'options' => ['placeholder' => "Select Component Sub Blocks", 'multiple' => true],
            ])->label("Component Sub Blocks");
        ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
        <?= $form->field($model, 'blocks')->textarea(['rows' => 6]) ?>
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
