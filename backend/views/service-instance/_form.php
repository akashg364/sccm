<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\ServiceModel;
use backend\models\Customer;
use backend\models\ResourceManager;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\models\Topology;

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceInstance */
/* @var $form yii\widgets\ActiveForm */

$smodel = new ServiceModel();
$smodel_list = $smodel->getServiceModelAll();

$customers = new Customer();
$customer_list = $customers->getCustomers();

$topo   = new Topology();
$topology_list  = $topo->getTopologyAll();
?>

<div class="service-instance-form">
<div class="js-form-msg"></div>
<div class="js-service-instance-form">
<?php $form = ActiveForm::begin(['id'=>'service-instance-form']); ?>
    <div class="popup-wheat-bg">
        <?= $form->field($model, 'form_error')->hiddenInput()->label(false) ?>

        <?= $form->field($model, 'service_order_id', ['enableAjaxValidation' => false])->textInput() ?>
    
        <?= $form->field($model, 'customer_id')->dropDownList(ArrayHelper::map($customer_list, 'id', 'company_name'), ['prompt' => ''])->label('Customer'); ?>

        <?php
        $ajaxUrl = Url::to(["resource-manager/service-variables"]);
        echo $form->field($model, 'service_model_id')->dropDownList(ArrayHelper::map($smodel_list, 'id', 'name'), [
            'prompt' => 'Select service model',
        ])->label('Service Model');
        ?>

                <?php
        /*
        echo '<label>Topology</label><br>';
        echo HTML::dropDownList('ServiceInstance[topology]', 'null', [1 => 'Hub', 2 => 'Spoke'], [
            'label' => 'Topology', 'prompt' => 'Select Topology', 'class' => 'form-group field-serviceinstance-service_model_id required form-control service_topology'
        ]); 
        */
        echo $form->field($model, 'topology')->dropDownList(ArrayHelper::map($topology_list, 'name', 'name'), [
            'prompt' => 'Select Topology', 'class' => 'form-group field-serviceinstance-service_model_id required form-control service_topology'
        ])->label('Topology');  
        ?>
        <div class="hub-hostname" style="display: none">
            <?php
                echo $form->field($model, 'hub_hostname')->textInput(['class' => 'form-group field-serviceinstance-service_model_id required form-control'])
                            ->label('Hub Hostname');    
            ?>
        </div>
        <!-- <?php 
              /*$form->field($model, 'hub_hostname', ['enableAjaxValidation' => true])->textInput(['class' => 'form-control form-group field-serviceinstance-service_model_id required spoke_topology hide'])->label('Hub Hostname'); 
              */
       ?> -->
        <?php
        
        // Old Code replaced with new on 12th July 2018
//        $ajaxUrl = Url::to(["resource-manager/endpoints-tabs"]);
//        echo $form->field($model, 'endpoints')->textInput([
//            'placeholder' => 'End Points',
//            'maxlength'=>10,
//            'onchange' => '
//                    $.post("' . $ajaxUrl . '?model=' . '"+$("#serviceinstance-service_model_id").val()+"' . '&endpoints=' . '"+$(this).val(), function( data ){
//                    $("#serviceinstance-service-data").html(data);
//            });',
//        ])->label('End Point Count');
        
        /** 12th July 2018 as per lab changed by pooja**/
                $ajaxUrl = Url::to(["resource-manager/endpoints-tabs-lab"]);
        echo $form->field($model, 'endpoints')->textInput([
            'placeholder' => '1 or 2',
            'maxlength'=>1,
            'min'=>1,
            'max'=>2,
            'onchange' => '
                    $.post("' . $ajaxUrl . '?model=' . '"+$("#serviceinstance-service_model_id").val()+"' . '&endpoints=' . '"+$(this).val(), function( data ){
                    $("#serviceinstance-service-data").html(data);
            });',
        ])->label('End Point Count');
        
        
        ?>
        
        <div id="serviceinstance-service-data"></div>

        <?= $form->field($model, 'scheduled_status')->dropDownList([ 'NOW' => 'NOW', 'SCHEDULE' => 'SCHEDULE', 'NEAR FUTURE' => 'NEAR FUTURE' ]) ?>

        <?= $form->field($model, 'scheduled_date')->textInput(["readonly" => true,"class" => "form-control form_datetime", "value" => date('Y-m-d h:i:s')]); ?>

        <?= $form->field($model, 'is_active')->dropDownList(['1' => 'Active', '0' => 'InActive']); ?>

    </div>
    <div class="modal-footer ">
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim',"data-loading-text"   => "Loading..."]) ?>
		
		 <?php //Html::submitButton('Dryrun', ['class' => 'btn btn-swim js-dry-run',"name"=>"dry_run_button"]) ?>
		 
        <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
    </div>
    <?php ActiveForm::end(); ?>
	</div>
	<div class="js-dry-run-output" style="display:none;">
		 Loading... 
	</div>
</div>
<?php
  $ajaxUrl = Url::to(["resource-manager/service-variables-lab"]);
  $this->registerJs("	
    $('document').ready(function(){
        $('.field-serviceinstance-scheduled_date').hide();
        $('#serviceinstance-scheduled_status').on('change', function(){
            if(this.value == 'SCHEDULE' || this.value == 'NEAR FUTURE') {
                $('.form_datetime').datetimepicker({format: 'yyyy-mm-dd hh:ii:ss'});
                $('.field-serviceinstance-scheduled_date').show();
            } else {
                $('.field-serviceinstance-scheduled_date').hide();
            }
        });
        
        $(document).on('change','.js-device-role',function(e){
             role_id = $(this).val();
             var devices_id = this.id.split('-');
             device_id = devices_id[1];
             service_model_id = $('#serviceinstance-service_model_id').val();
             dataDiv = $(this).closest('.tab-pane').find('.nso_payload');
             $.post('{$ajaxUrl}?role='+role_id+'&device='+device_id+'&id='+service_model_id, function( data ){ 
                    dataDiv.html(data); 
            });
        });
       
    });
    
    
        $('.service_topology').on('change', function(){
        var val = $('.service_topology').val();
        if(val == 'Spoke') {
          $('div.hub-hostname').show();
        } else {
          $('div.hub-hostname').hide();
        }
    });

");

