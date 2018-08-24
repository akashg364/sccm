<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\SubServices;
use backend\models\Services;
use backend\models\NetworkEngineers;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\L2Testing */
/* @var $form yii\widgets\ActiveForm */

$serviceList = new Services();
$service_list = $serviceList->getServiceList();

$subServiceList = new SubServices();
$sub_service_list = $subServiceList->getSubserviceList();

$engineerList = new NetworkEngineers();
$engineer_list = $engineerList->getEngineerList();

?>

<div class="l2-testing-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'service_id')->dropDownList($service_list, ['prompt' => ''])->label('Service'); ?>

    <?= $form->field($model, 'sub_service_id')->dropDownList($sub_service_list, ['prompt' => ''])->label('Sub Service'); ?>

    <?= $form->field($model, 'network_engineer')->dropDownList($engineer_list, ['prompt' => ''])->label('Network Engineer'); ?>

    <?= $form->field($model, 'bangalore_lab_status')->dropDownList(['pending' => 'Pending', 'completed' => 'Completed']) ?>

    <?= $form->field($model, 'bangalore_datetime')->textInput(["readonly" => true,"class" => "form-control form_datetime", "value" => date('Y-m-d h:i:s')]); ?>

    <?= $form->field($model, 'reliance_lab_status')->dropDownList(['pending' => 'Pending', 'completed' => 'Completed']) ?>

    <?= $form->field($model, 'reliance_datetime')->textInput(["readonly" => true,"class" => "form-control form_datetime", "value" => date('Y-m-d h:i:s')]); ?>

    <?= $form->field($model, 'status')->dropDownList(['1' => 'Active', '0' => 'InActive']); ?>

    <div class="form-group"><?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?></div>
    
    <?php ActiveForm::end(); ?>
    
</div>