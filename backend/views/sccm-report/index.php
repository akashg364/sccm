<?php
namespace backend\controllers\SccmReportController;
use Yii;
use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\SccmReportModel;
use backend\models\Services;
use backend\models\SubServices;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sccm Reports';
$this->params['breadcrumbs'][] = $this->title;

 
//echo $_SERVER['DOCUMENT_ROOT'];


?>

<div class="sccm-report-index">

  <h1><?php // Html::encode($this->title) ?></h1>

    <p>
        
        <?= Html::a('<i class="fa fa-plus" area-hiden="true"></i> Create Sccm Report', ['create'], ['class' => 'btn-add showModalButton', 'title' => 'Create Sccm Report']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            
            [
                'label'=>'Service Type',
                'format' => 'raw',
                'value' => function ($searchModel) {
                    $serviceType_id = $searchModel->service_type;
                    $serviceModel = new Services();
                    return $serviceModel->getServiceName($serviceType_id);
                }
            ],
            [
                'label'=>'Sub Service Type',
                'format' => 'raw',
                'value' => function ($searchModel) {
                    $subservice_id = $searchModel->sub_service_type;
                    $serviceModel = new SubServices();
                    return $serviceModel->getSubServiceRef($subservice_id);
                }
            ],
            [
                'label'=>'Managed/Unmanaged',
                'format' => 'raw',
                'value' => function ($searchModel) {
                    $mngdunmngd = $searchModel->managed_unmanaged;
                    return $mngdunmngd = ($mngdunmngd=='1')?'Managed':'Unmanaged';
                }
            ],
            
            [
                'label'=>'CPE Termination Point',
                'format' => 'raw',
                'value' => function ($searchModel) {
                    $termination_point = $searchModel->termination_point;
                    return $termination_point = ($termination_point=='1')?'CSS':'';
                }
            ],
            [
                    'label'=>'Spur/ Dual Homed',
                    'format' => 'raw',
                    'value' => function ($searchModel) {
                    $dual_homed = $searchModel->spur_dual_homed;
                    return $dual_homed = ($dual_homed=='1')?'Spur':'Dual Homed';

                }
            ],
            [
                    'label'=>'With Mds',
                    'format' => 'raw',
                    'value' => function ($searchModel) {
                    $with_mds = $searchModel->with_mds;
                    return $with_mds = ($with_mds=='1')?'No':'Yes';

                }
            ],
            [
                    'label'=>'With Eds',
                    'format' => 'raw',
                    'value' => function ($searchModel) {
                    $with_eds = $searchModel->with_eds;
                    return $with_eds = ($with_eds=='1')?'No':'Yes';

                }
            ],
            [
                'label'=>'Tagged Type',
                'format' => 'raw',
                'value' => function ($searchModel) {
                $tagged_type = $searchModel->tagged_type;
                return $tagged_type = ($tagged_type=='1')?'Tagged':'Untagged';
                }
            ],
            [
                'label'=>'PE Type Single/Dual PE',
                'format' => 'raw',
                'value' => function ($searchModel) {
                $with_eds = $searchModel->single_dual_pe;
                return $termination_point = ($with_eds=='1')?'Tagged':'Untagged';
                }
            ],
            [
                'label'=>'Routing Protocol',
                'format' => 'raw',
                'value' => function ($searchModel) {
                $routing_protocol = $searchModel->routing_protocol;
                return $routing_protocol = ($routing_protocol=='1')?'BGP':'Untagged';
                }
            ],
            
            [
                'label'=>'Concatenated Data',
                'format' => 'raw',
                'value' => function ($searchModel) {
                $concatenated = '';
                $serviceModel = new Services();
                $id =$searchModel->sub_service_type;
                $serviceModel = new SubServices();
                $subservice =  $serviceModel->getSubServiceRef($id);
                $concatenated.= $subservice.'-';
                $mngdunmngd = $searchModel->managed_unmanaged;
                $mngdunmngd = ($mngdunmngd=='1')?'Managed':'Unmanaged';
                $concatenated.= $mngdunmngd.'-';
                $termination_point = $searchModel->termination_point;
                $termination_point = ($termination_point=='1')?'CSS':'';
                $concatenated.= $termination_point.'-';
                $dual_homed = $searchModel->spur_dual_homed;
                $dual_homed = ($dual_homed=='1')?'Spur':'Dual Homed';
                $concatenated.= $dual_homed.'-';
                $with_eds = $searchModel->single_dual_pe;
                $with_eds = ($with_eds=='1')?'Tagged':'Untagged';
                $concatenated.= $with_eds.'-';
                    return $concatenated;
                }
            ],
            
            [
                'label'=>'Configuration Uploaded',
                'format' => 'raw',
                'value' => function ($searchModel) {
                $conf_uploaded = $searchModel->conf_uploaded;
                $id = $searchModel->id;
                $routing_protocol_str = '';
                 $userType = array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
                 
                if($conf_uploaded=='1'){
                   
                    if($userType[0]=="Superadmin")
                    {
                    $linkModal =  Html::a('<i class="fa fa-upload" area-hiden="true"></i>', ['configurations?id='.$id], ['class' => 'btn btn-info showModalOnUpload', 'data-ref'=>$searchModel->id, 'title' => 'Upload Configurations', 'style'=>'color:#fff']);
                    $routing_protocol_str = $linkModal;
                    }
                    else
                    {
                    $routing_protocol_str = "Files Not Uploaded";  
                    //'<a href="javascript:void(0)" class="btn-add showModalButton" style="color:#fff;"><i class="fa fa-upload fa-fw"></i></a>';
                    }
                    
                }
                else {
                     if($userType[0]=="Superadmin")
                    {
                        if($searchModel->all_in_one_file!="" OR $searchModel->css_file!="" OR $searchModel->ag1_file!="" OR $searchModel->ag2_file!="")
                        {
                        $routing_protocol_str = 'Uploaded Files <br>';
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->all_in_one_file.'&type=configurations">'.$searchModel->all_in_one_file.'</a><br>';
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->css_file.'&type=configurations">'.$searchModel->css_file.'</a><br>';
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->ag1_file.'&type=configurations">'.$searchModel->ag1_file.'</a><br>';
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->ag2_file.'&type=configurations">'.$searchModel->ag2_file.'</a><br>';
                        }
                        
                        $routing_protocol_str.= Html::a('<i class="fa fa-upload" area-hiden="true"></i>', ['configurations?id='.$id], ['class' => 'btn btn-info showModalOnUpload', 'data-ref'=>$searchModel->id, 'title' => 'Upload Configurations', 'style'=>'color:#fff']);
                    }
                    else
                    {
                        if($searchModel->all_in_one_file!="" OR $searchModel->css_file!="" OR $searchModel->ag1_file!="" OR $searchModel->ag2_file!="")
                        {
                        $routing_protocol_str = 'Uploaded Files <br>';
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->all_in_one_file.'&type=configurations">'.$searchModel->all_in_one_file.'</a><br>';
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->css_file.'&type=configurations">'.$searchModel->css_file.'</a><br>';
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->ag1_file.'&type=configurations">'.$searchModel->ag1_file.'</a><br>';
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->ag2_file.'&type=configurations">'.$searchModel->ag2_file.'</a><br>';
                        }
                    }
                    
                }
                return $routing_protocol_str;
                }
            ],
//            'created_by',
            'created_on',
            
                    [
                'label'=>'Payload',
                'format' => 'raw',
                'value' => function ($searchModel) {
                //$payload = $searchModel->payload;
                $routing_protocol_str = "";
                $userType = array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
                $id = $searchModel->id;
                if($searchModel->payload!=""){
                     if($userType[0]=="Superadmin")
                    {
                    $linkModal =  Html::a('<i class="fa fa-upload" area-hiden="true"></i>', ['payload?id='.$id], ['class' => 'btn btn-info showModalOnUpload', 'data-ref'=>$searchModel->id, 'title' => 'Upload payload', 'style'=>'color:#fff']);
                    $routing_protocol_str = $linkModal;
                    $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->payload.'&type=payload">'.$searchModel->payload.'</a><br>';
                    }
                    else
                    {
                    $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->payload.'&type=payload">'.$searchModel->payload.'</a><br>';
                    //'<a href="javascript:void(0)" class="btn-add showModalButton" style="color:#fff;"><i class="fa fa-upload fa-fw"></i></a>';
                    }
                }
                else
                {
                    $linkModal =  Html::a('<i class="fa fa-upload" area-hiden="true"></i>', ['payload?id='.$id], ['class' => 'btn btn-info showModalOnUpload', 'data-ref'=>$searchModel->id, 'title' => 'Upload Configurations', 'style'=>'color:#fff']);
                    $routing_protocol_str = $linkModal;
                   
                }
               
                    
                   
                return $routing_protocol_str;
                }
                ],
                [
                    'label'=>'Dryrun',
                    'format' => 'raw',
                    'value' => function ($searchModel) {
                    //$dryrun = $searchModel->dryrun;
                    $userType = array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
                     $id = $searchModel->id;
                        $routing_protocol_str = "";
                    if($searchModel->dryrun!=""){
                        
                        if($userType[0]=="Superadmin")
                        {
                        $linkModal =  Html::a('<i class="fa fa-upload" area-hiden="true"></i>', ['dryrun?id='.$id], ['class' => 'btn btn-info showModalOnUpload', 'data-ref'=>$searchModel->id, 'title' => 'Upload dryrun', 'style'=>'color:#fff']);
                        $routing_protocol_str = $linkModal;
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->dryrun.'&type=dryrun">'.$searchModel->dryrun.'</a><br>';
                        }
                        else
                        {
                        $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->dryrun.'&type=dryrun">'.$searchModel->dryrun.'</a><br>';
                        }
                        
                    }
                    else {
                   
                        $linkModal =  Html::a('<i class="fa fa-upload" area-hiden="true"></i>', ['dryrun?id='.$id], ['class' => 'btn btn-info showModalOnUpload', 'data-ref'=>$searchModel->id, 'title' => 'Upload Configurations', 'style'=>'color:#fff']);
                        $routing_protocol_str.= $linkModal;
                    
                        
                    }
                   
                    
                    return $routing_protocol_str;
                    }
                ],
                    [
                'label'=>'L2document',
                'format' => 'raw',
                'value' => function ($searchModel) {
                        $userType = array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
                        $id = $searchModel->id;
                        $routing_protocol_str = "";
                        if($searchModel->l2_document!=""){

                           if($userType[0]=="Superadmin")
                           {
                               $linkModal =  Html::a('<i class="fa fa-upload" area-hiden="true"></i>', ['l2document?id='.$id], ['class' => 'btn btn-info showModalOnUpload', 'data-ref'=>$searchModel->id, 'title' => 'Upload l2document', 'style'=>'color:#fff']);
                               $routing_protocol_str = $linkModal;
                               $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->l2_document.'&type=dryrun">'.$searchModel->l2_document.'</a><br>';
                           }
                           else
                           {
                               $routing_protocol_str.= '<a href="'.Yii::$app->request->baseUrl.'/sccm-report/download?id='.$id.'&name='.$searchModel->l2_document.'&type=dryrun">'.$searchModel->l2_document.'</a><br>';
                           }

                         }
                         else 
                         {
                            $linkModal =  Html::a('<i class="fa fa-upload" area-hiden="true"></i>', ['l2document?id='.$id], ['class' => 'btn btn-info showModalOnUpload', 'data-ref'=>$searchModel->id, 'title' => 'Upload l2document', 'style'=>'color:#fff']);
                            $routing_protocol_str.= $linkModal;
                         }
                
                
                return $routing_protocol_str;
               
                }
            ],

            
            
            [
                'class' => 'common\components\yii\ActionColumn',
                'buttonOptions' => ['modalType' => 'modal-lg'],
                
                "buttons"=>[
                    'view'=>function($url, $model, $key){
                        return Html::a("<span class='fas fa-eye fa-lg'></span>",$url,["class"=>"showModalButton","modalType"=>"modal-full"]);
                    }
                ],
            ],
        ],
    ]); ?>
</div>
