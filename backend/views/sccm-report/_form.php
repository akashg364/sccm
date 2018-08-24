<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Services;
use backend\models\SubServices;
use backend\models\Topology;


$servicesList = [""=>"Select"]+Services::getServiceList();
$subServiceList = [""=>"Select"]+SubServices::getSubServiceList();
$topologyList = [""=>"Select"]+Topology::getTopologyList();
  
  
  $mngdUnmngdList = [""=>"Select", "1"=>"yes", "2"=>"No"];
  $terminationPoint = [""=>"Select", "1"=>"css"];
  $dualHomed = [""=>"Select", "1"=>"Dual", "2"=>"Homed"];
  $withMds = [""=>"Select", "1"=>"Yes", "2"=>"No"];
  $withEds = [""=>"Select", "1"=>"Yes", "2"=>"No"];
  
  
  
  $taggedType = [""=>"Select", "1"=>"Yes", "2"=>"No"];
  $singleDualPe = [""=>"Select", "1"=>"Single", "2"=>"Dual", "3"=>"Both"];
  $routProtocol = [""=>"Select", "1"=>"BGP"];
  $confUploaded = [""=>"Select", "1"=>"Yes", "2"=>"No"];

/* @var $this yii\web\View */
/* @var $model app\models\SccmReport */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="">

    <?php $form = ActiveForm::begin(); ?>
    <div class="popup-wheat-bg">
    <?= $form->field($model, 'service_type')->dropDownList($servicesList) ?>
   <!--  <?= $form->field($model, 'service_type')->textInput() ?> -->
   
    <?= $form->field($model, 'sub_service_type')->dropDownList($subServiceList) ?>

    <!-- <?= $form->field($model, 'sub_service_type')->textInput() ?> -->

    <?= $form->field($model, 'managed_unmanaged')->dropDownList($mngdUnmngdList) ?>
    <!-- <?= $form->field($model, 'managed_unmanaged')->textInput() ?> -->

    <?= $form->field($model, 'termination_point')->dropDownList($terminationPoint) ?>
    <!-- <?= $form->field($model, 'termination_point')->textInput() ?> -->

    <?= $form->field($model, 'spur_dual_homed')->dropDownList($dualHomed) ?>
    <!-- <?= $form->field($model, 'spur_dual_homed')->textInput() ?> -->

    <?= $form->field($model, 'with_mds')->dropDownList($withMds) ?>
    <!-- <?= $form->field($model, 'with_mds')->textInput() ?> -->

    <?= $form->field($model, 'with_eds')->dropDownList($withEds) ?>
    <!-- <?= $form->field($model, 'with_eds')->textInput() ?> -->

    <?= $form->field($model, 'tagged_type')->dropDownList($taggedType) ?>
    <!-- <?= $form->field($model, 'tagged_type')->textInput() ?> -->

    <?= $form->field($model, 'single_dual_pe')->dropDownList($singleDualPe) ?>
    <!-- <?= $form->field($model, 'single_dual_pe')->textInput() ?> -->

    <?= $form->field($model, 'routing_protocol')->dropDownList($routProtocol) ?>
    <!-- <?= $form->field($model, 'routing_protocol')->textInput() ?> -->

    <!-- <?= $form->field($model, 'concat_data')->textInput(['maxlength' => true]) ?> -->

    <!-- <?= $form->field($model, 'conf_uploaded')->dropDownList($confUploaded) ?> -->
   <!-- <?= $form->field($model, 'conf_uploaded')->textInput() ?> -->

    <!-- <?= $form->field($model, 'created_by')->textInput() ?> -->

    <!-- <?= $form->field($model, 'created_on')->textInput() ?> -->

    <!-- <?= $form->field($model, 'payload')->textInput(['maxlength' => true]) ?> -->

    <!-- <?= $form->field($model, 'dryrun')->textInput(['maxlength' => true]) ?> -->

    <!-- <?= $form->field($model, 'l2_document')->textInput(['maxlength' => true]) ?> -->
</div>
    <div class="modal-footer">
        
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-swim', "data-dismiss" => "modal"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php 

$this->registerJs('   
//isAjaxSubmitForm = true;
');