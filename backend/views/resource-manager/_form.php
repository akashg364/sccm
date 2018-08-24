<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;
use backend\models\ResourceManager;
use backend\models\DataType;

/* @var $this yii\web\View */
/* @var $model backend\models\ResourceManager */
/* @var $form yii\widgets\ActiveForm */

//$customer_name = ($model->customer)?$model->customer->company_name:"";

$this->registerJsFile(Yii::$app->request->baseUrl."/js/resource-manager.js",['depends' => [yii\web\JqueryAsset::className()]]);
?>

<div class="">
<div class="resource-manager-form">

    <?php $form = ActiveForm::begin(['id'=>'resource-manager-form']); ?>
     <div class="popup-wheat-bg">
        <?php //echo  $form->errorSummary($model); ?>

         <?php
            $select2PlugingOptions = [
                'allowClear' => true,
                'tags' => true,
                'minimumInputLength' => 3,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                ],
                'ajax' => [
                    'url'      =>Url::to(['customer/select2-autocomplte']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(data) { return data.text; }'),
                'templateSelection' => new JsExpression('function (data) { return data.text; }'),
            ];
            echo $form->field($model, 'customer_id')->widget(Select2::classname(), [
                'model' => $model,
                'data'=>@$customer_name,
                'options' => [
                    'placeholder' => "Select Customer",
                ],
              //  'initValueText' => @$customer_name,
                'pluginOptions' => $select2PlugingOptions,
                'attribute' => 'customer_id',
            ]);
            ?>
        <?php // $form->field($model, 'customer_id')->textInput() ?>

         <?= $form->field($model, 'type')->radioList(ResourceManager::$parameterTypes) ?>
        <?php //$form->field($model, 'type')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'data_type_id')->dropdownList([""=>"Select"]+DataType::getDataTypeDropdownList()) ?>

        <?= $form->field($model, 'value_type')->dropdownList([""=>"Select"]+ResourceManager::$valueTypes) ?>

        <?= $form->field($model, 'variable_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'parameter_min_value')->textInput(['maxlength' => true]) ?>
        <div style="display: none;" class="js-parameter_max_value">
            <?= $form->field($model, 'parameter_max_value')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="modal-footer ">
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
</div>

