<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\VariablesMaster;
use backend\models\DataType;
/* @var $this yii\web\View */
/* @var $model backend\models\VariablesMaster */
/* @var $form yii\widgets\ActiveForm */
$this->registerJsFile(Yii::$app->request->baseUrl."/js/variables-master.js",['depends' => [yii\web\JqueryAsset::className()]]);
?>
<div class="variables-master-form">
    <?php $form = ActiveForm::begin(["id"=>"variables-master-form"]); ?>
    <div class="popup-wheat-bg">
        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'type')->radioList(VariablesMaster::$variableTypes) ?>
                <?= $form->field($model, 'value_type')->dropdownList([""=>"Select"]+VariablesMaster::$valueTypes) ?>
                <?= $form->field($model, 'data_type_id')->dropdownList([""=>"Select"]+DataType::getDataTypeDropdownList()) ?>
                <?= Yii::$app->yiiHelper->getActiveStatusField($form,$model); ?>
            </div>
            <div class="col-lg-6">
                <?= $form->field($model, 'variable_name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'value1_label')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'value2_label')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        
    </div>
    <div class="modal-footer">
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>