<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use backend\models\VariablesMaster;
use backend\models\Customer;
use yii\helpers\Json;
use yii\helpers\Url;

$variables = VariablesMaster::getSystemVariablesForForm();
$customerList = Customer::getCustomerList();
/* @var $this yii\web\View */
/* @var $model backend\models\VariablesMapping */
/* @var $form yii\widgets\ActiveForm */
$this->registerJsFile(Yii::$app->request->baseUrl."/js/variables-master.js",['depends' => [yii\web\JqueryAsset::className()]]);
?>

<div class="variables-mapping-form">

    <?php $form = ActiveForm::begin(["id"=>"variables-mapping-form"]); ?>
     <div class="popup-wheat-bg">
     	  <?php
        echo $form->field($model, 'customer_id')->widget(Select2::classname(), [
	        'model' => $model,
	        'data'=>$customerList,
	        'options' => [
	        		'placeholder' => "Select Customer", 
	        		'multiple' => false,
	        		'class'=>""
        	],
        ]);
        ?>
     	 <?php
        echo $form->field($model, 'variable_id')->widget(Select2::classname(), [
	        'model' => $model,
	        'data'=>$variables["list"],
	        'options' => [
	        		'placeholder' => "Select Variable", 
	        		'multiple' => false,
	        		'class'=>""
        	],
        ]);
        ?>
        <div class="row">
        	<div class="col-lg-6">
        		<?= $form->field($model, 'value1') ?>
        	</div>	
         	<div class="col-lg-6">
         		<?= $form->field($model, 'value2') ?>
         	</div>
        </div> 		

         <?php if(@$usedPools){ // Exhausted Pool
         	echo '<ul class="list-group">';
       			echo "<li class='list-group-item active'>Exhausted Pool : </li>";
       			foreach ($usedPools as $key => $usedPool) {
       				$str = $usedPool["value1"];
       				if($usedPool["value2"]){
       					$str.=" - ".$usedPool["value2"];
       				}
       				echo "<li class='list-group-item'>".$str."</li>";
       			}
       		echo "</ul>";
       	}?>
	   
	</div>
     <div class="modal-footer">
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php 
$this->registerJs('
  ipPoolUrl = "'.Url::to(["variables-mapping/get-pool"]).'";
	isAjaxSubmitForm = true;
	variablesMapping = '.Json::encode($variables["mapping"]).';
	variableMaster.setValueInput();
');
?>
