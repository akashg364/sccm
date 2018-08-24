<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

$this->registerJs("
  var getTemplateUrl = '".Url::to(["service-model/get-service-model-template"])."';
");
$this->registerJsFile(Yii::$app->request->baseUrl."/js/service-model.js",['depends' => [yii\web\JqueryAsset::className()]]);
?>
<h1>Service Model - <?php echo $serviceModel["name"];?></h1>
  <?php $form = ActiveForm::begin([
  'id'=>'service-model-template-form',
  ]); ?>
<div class="js-form-msg" style="display: none;">
    
</div>  
<div class="popup-wheat-bg">
  <div class="row">
    <div class="col-lg-4  col-md-4">
        <?php echo Html::activeHiddenInput($model,'service_model_id')?>
        <?php
        echo $form->field($model, 'device_role_id')->widget(Select2::classname(), [
        'model' => $model,
        'data'=>$deviceRolesArray,
        'initValueText' => @$id,
        'options' => ['placeholder' => "Select Device Role", 'multiple' => false,'class'=>"js-device-role-id"],
        ])->label("Device Role");
        ?>

        <?php
        echo $form->field($model, 'reference_id')->widget(DepDrop::classname(), [
        'model'   => $model,
        //'data'    =>$deviceRolesArray,
        'pluginOptions' => [
                    'depends' => [
                        Html::getInputId($model, 'device_role_id'),
                        Html::getInputId($model, 'service_model_id')
                  ], 
                    'placeholder' => 'Select...',
                    'url' => Url::to(['/service-model/get-related-device-roles'])
                ],
        //'initValueText' => @$id,
        'options' => ['placeholder' => "Select Related Device Role", 'multiple' => false],
        ])->label("Related Device Role");
        ?>
        <?= $form->field($model, 'template_version')->textInput() ?>
        <?= $form->field($model, 'cli_nip')->textarea(['rows' => 4,'cols'=>50]) ?>
      </div>

    <div class="col-lg-4  col-md-4">
      <?= $form->field($model, 'nso_payload')->textarea(['rows' => 14,'cols'=>50
      //'ondrop'=>'drop(event)'
      ]) ?>
      </div>
    <div class="col-lg-2 col-md-2">
      <div class="var-wrapper">
        <div class="form-group">
          <label class="clearfix">User Variables </label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="Drag & Drop below values in to Nos Payload"><i class="fa fa-fw fa-info-circle"></i></span>
          <div class="js-variables-wrapper variable-div">
            <?php
              if(isset($variables["user"])){ 
                foreach($variables["user"] as $variable_id=>$variable){
                echo "<span class='dragdrop' draggable='true' resource-manager-id='{$variable_id}'>{{$variable}}</span>";
              }}?>
            
          </div>
        </div>
        <div class="form-group" style="display: none;">
          <label class="clearfix">User Variables </label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="Drag & Drop below values in to Nos Payload"><i class="fa fa-fw fa-info-circle"></i></span>
          <div class="js-variables-wrapper variable-div js-ref-user-variables">
            
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-2 col-md-2">
      <div class="var-wrapper">
        <div class="form-group">
          <label>System Variables</label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="Drag & Drop below values in to Nos Payload"><i class="fa fa-fw fa-info-circle"></i></span>
          <div class="js-variables-wrapper variable-div">
            <?php 
            if(isset($variables["system"])){ 
              foreach($variables["system"] as $variable_id=>$variable){
                  echo "<span class='dragdrop' draggable='true' resource-manager-id='{$variable_id}'>{{$variable}}</span>";
                }
            }
            ?>
          </div>
         
        </div>
        <div class="form-group" style="display: none;">
          <label>System Variables</label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="Drag & Drop below values in to Nos Payload"><i class="fa fa-fw fa-info-circle"></i></span>
          <div class="js-variables-wrapper variable-div js-ref-system-variables">
          </div>
        </div>
      </div>
      <?= $form->field($model, 'resource_manager_id')->hiddenInput()->label(false) ?>
    </div>
  </div>
</div>
<div class="modal-footer">
  <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
      <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
</div>
<?php ActiveForm::end(); ?>