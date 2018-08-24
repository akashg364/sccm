<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\ResourceManager;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\depdrop\DepDrop;
use backend\models\DeviceRole;
use backend\models\Services;
use yii\helpers\Url;
use backend\models\VariablesMaster;

//$variables = ResourceManager::getUserSystemVariablesList();
$deviceRoles = DeviceRole::getDeviceRolesList();
$service = Services::getServiceList();
$variables = VariablesMaster::getVariablesList();

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceModel */
/* @var $form yii\widgets\ActiveForm */
?>
<style type="text/css">/* remove X from locked tag */
.locked-tag .select2-selection__choice__remove{
  display: none!important;
}

/* I suggest to hide  all selected tags from drop down list */
.select2-results__option[aria-selected="true"]{
  display: none;
}

</style>
<div class="service-model-form">
<?php $form = ActiveForm::begin([
        'id'=>'service-model-form',
    ]); ?>
    <div class="popup-wheat-bg">
        <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'payload_key')->textInput() ?>
            
            <?php
                echo $form->field($model, 'service_id')->widget(Select2::classname(), [
                'model' => $model,
                'data' => $service,
                'initValueText' => @$id,
                'options' => ['placeholder' => "Select Service", 'multiple' => false],
            ])->label("Service");            
            ?>

            <?php
            echo $form->field($model, 'sub_service_id')->widget(DepDrop::classname(), [
                 'data'=>@$sub_service_id,
                'pluginOptions' => [
                    'depends' => [Html::getInputId($model, 'service_id')], // the id for cat attribute
                    'placeholder' => 'Select...',
                    'url' => Url::to(['/service-mapping/getsubservice'])
                ]
            ]);

            echo $form->field($model, 'topology_id')->widget(DepDrop::classname(), [
                'data'=>@$topology_id,
                'pluginOptions' => [
                    'depends' => [
                        Html::getInputId($model, 'service_id'), // the id for cat attribute
                        Html::getInputId($model, 'sub_service_id'), // the id for subcat attribute
                    ],
                    'placeholder' => 'Select...',
                    'url' => Url::to(['/service-mapping/gettopology']),
                    'initialize' => true
                ]
            ]);
            ?>


           
        </div>
        <div class="col-lg-6">
            <?php
  
            echo $form->field($model, 'device_role_id')->widget(Select2::classname(), [
                'model' => $model,
                'data' => $deviceRoles,
                'initValueText' => @$device_role,
                'options' => ['placeholder' => "Select Device Role", 'multiple' => true],
                // 'pluginOptions'=> [
                //     "templateSelection"=> new JsExpression('function (tag, container){
                //                             console.log("test");
                //                             var $option = $("#servicemodel-device_role_id option[value=\'"+tag.id+"\']");
                //                             //if ($option.attr("locked")){
                //                                 $(container).addClass("locked-tag");
                //                                 tag.locked = true; 
                //                             //}
                //                         return tag.text;
                //         }')]
            ])->label("Device Role");
            ?>

            <?php
            echo $form->field($model, 'user_variables')->widget(Select2::classname(), [
                'model' => $model,
                'data' => $variables["user"],
                'initValueText' => @$user_variables,
                'options' => ['placeholder' => "Select User Defined Variables", 'multiple' => true],
            ])->label("User Defined Variables");
            ?>
            <?php
            echo $form->field($model, 'system_variables')->widget(Select2::classname(), [
                'model' => $model,
                'data' => $variables["system"],
                'initValueText' => @$system_variables,
                'options' => ['placeholder' => "Select System Defined Variables", 'multiple' => true],
            ])->label("System Defined Variables");
            ?>

             <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
        </div> 
        </div>
    </div>   
    <div class="modal-footer">
            <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
            <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
$serviceTemplateUrl = Url::to(['service-model/service-template']);

$this->registerJs(' 
$("form#service-model-form").on("beforeSubmit", function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = form.serialize();

    response = commonJs.callAjax(form.attr("action"),form.attr("method"),formData);
    if(response.success){
        $("#modal").find("#modalContent").html(faLoader);
        templateRes = commonJs.callAjax("'.$serviceTemplateUrl.'","POST",response.data,"text");
        loadMoal(templateRes,"","modal-full");
        return false;
    }else{
        console.log("Error on saving service model");
    }
    return false;

}).on("submit", function(e){
    e.preventDefault();
    e.stopPropagation();
});
');
?>
