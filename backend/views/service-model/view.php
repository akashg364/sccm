<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
/* @var $this yii\web\View */
/* @var $model backend\models\ServiceModel */
$this->title = "Service Model - ".$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Service Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="service-model-view popup-wheat-bg">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-lg-2 col-md-2 service_model_tbl">
        <label class="clearfix"> </label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="Drag & Drop below values in to Nos Payload"><i class="fa fa-fw fa-info-circle"></i></span>
                <div class="tabe-grid-view tabe-grid-view-service">
              <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    'payload_key',
                    'service.name',
                    'subService.name',
                    'topology.name',
                    'description:ntext',
                    'created_by',
                    'created_on',
                    'updated_by',
                    'updated_on',
                ],
                'id' => 'service_model_view_tbl',
            ]);
        ?>
           
        </div>
              </div>

              <div class="col-lg-2 col-md-2">
                <div class="var-wrapper">
                  <div class="form-group">
                    <label class="clearfix">User Variables </label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="Drag & Drop below values in to Nos Payload"><i class="fa fa-fw fa-info-circle"></i></span>
                    <div class="variable-div1 service_model_var">
                      <?php 
                        $variables = $model->serviceModelVariables;
                        foreach($variables as $v){
                            $rm = $v["variable"];
                            if($rm["type"]!="user")continue;
                        ?>
                            <span class="dragdrop">{<?php echo $rm["variable_name"]; ?>}</span>
                         <?php } ?> 
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="clearfix">System Variables </label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="Drag & Drop below values in to Nos Payload"><i class="fa fa-fw fa-info-circle"></i></span>
                    <div class="variable-div1 service_model_var">
                      <?php 
                        $variables = $model->serviceModelVariables;
                        foreach($variables as $v){
                            $rm = $v["variable"];
                            if($rm["type"]!="system")continue;
                        ?>
                            <span class="dragdrop">{<?php echo $rm["variable_name"] ?>}</span>
                         <?php } ?> 
                      
                    </div>
                  </div>
              </div>
              </div>
        <div class="col-lg-8 col-md-8">
          <ul class="nav nav-tabs">
                <?php
                foreach ($model->serviceModelTemplate as $key => $tmodel) {
                $role_name = $tmodel->deviceRole->role_name;
                $activeTab = ($key==0)?"active":"";
                ?>
                <li class="<?php echo $activeTab;?>">
                    <a data-toggle="tab" href="#<?php echo "template-".$tmodel->id;?>"><?php echo $role_name;?></a>
                </li>
                
                <?php } ?>
            </ul>
       <div class="tab-content service_model_tab">
       <?php
                foreach ($model->serviceModelTemplate as $key => $tmodel) {
                     $activeTab = ($key==0)?"fade in active":"";
                     $related_role = isset($tmodel->relatedDeviceRole->role_name)?$tmodel->relatedDeviceRole->role_name:"";
                     
                ?> 
              <div id="<?php echo "template-".$tmodel->id;?>" class="tab-pane <?php echo $activeTab;?>">
          <div class="form-group">
       <div class="row">
      <div class="col-lg-8">
        <div class="row">
          <div class="col-lg-6">
          <label>Related Device Role</label>
          <input id="" type="text" class="form-control txt-tab" placeholder="Enter Name" value="<?php echo $related_role;?>" disabled>
          </div>
          <div class="col-lg-6">
          <label>Template Version</label>
          <input id="" type="text" class="form-control txt-tab" placeholder="Enter Name" value="<?php echo $tmodel->template_version;?>" disabled>
          </div>
        </div></br>
        <div class="row">
           <div class="col-lg-6">
            <div class="form-group">
              <label class="clearfix">Cli Nip</label>
              <textarea class="form-control txt-tab" rows="11" cols="50" disabled><?php echo nl2br($tmodel->cli_nip);?></textarea>
            </div>
            </div>
            <div class="col-lg-6">
            <div class="form-group">
              <label class="clearfix">Nso Payload</label>
              <textarea class="form-control txt-tab" rows="11" cols="50" disabled><?php echo $tmodel->nso_payload;?></textarea>
            </div>
            </div>
        </div>
      </div>
        <div class="col-lg-4">
                <div class="form-group">
                  <label class="clearfix">User Variables</label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="User Variables"><i class="fa fa-fw fa-info-circle"></i></span>
                    <div class="variable-div1 txt-tab">
                      <?php 
                                $variables = $tmodel->serviceModelTemplateVariables;
                                foreach($variables as $v){
                                    $rm = $v["resourceManager"];
                                    if($rm["type"]!="user")continue;
                                ?>
                                    <span class="dragdrop">{<?php echo $rm["variable_name"] ?>}</span>
                                 <?php } ?> 

                    </div>
                </div>
                <div class="form-group">
                  <label class="clearfix">System Variables</label><span class="pull-right" data-toggle="tooltip" data-placement="top" title="System Variables"><i class="fa fa-fw fa-info-circle"></i></span>
                    <div class="variable-div1 txt-tab">
                       <?php 
                                $variables = $tmodel->serviceModelTemplateVariables;
                                foreach($variables as $v){
                                    $rm = $v["resourceManager"];
                                    if($rm["type"]!="system")continue;
                                ?>
                                    <span class="dragdrop">{<?php echo $rm["variable_name"] ?>}</span>
                                 <?php } ?> 
                     
                    </div>
                </div>
              </div>
       </div>
          
      
          </div>
              
        </div>
         <?php }?>
              </div>
      </div> 
</div>
<!-- <li class="active"><a data-toggle="tab" href="#home">Home</a></li> -->