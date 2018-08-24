<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Services;
use backend\models\SubServices;
use backend\models\Topology;
/* @var $this yii\web\View */
/* @var $model backend\models\ServiceMapping */
/* @var $form yii\widgets\ActiveForm */
 $servicesList = [""=>"Select"]+Services::getServiceList();
 $subServiceList = [""=>"Select"]+SubServices::getSubServiceList();
  $topologyList = [""=>"Select"]+Topology::getTopologyList();
?>

<div class="service-mapping-form">

    
    <?php $form = ActiveForm::begin(["id"=>"service-mapping-form"]); ?>
    <div class="popup-wheat-bg">
    <?= $form->field($model, 'service_id')->dropDownList($servicesList) ?>

    <?= $form->field($model, 'sub_service_id')->dropDownList($subServiceList) ?>

    <?= $form->field($model, 'topology_id')->dropDownList($topologyList) ?>

    <?=Yii::$app->yiiHelper->getActiveStatusField($form,$model)?>
    </div>
    <div class="modal-footer">
         <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
         <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
