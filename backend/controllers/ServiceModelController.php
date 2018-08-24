<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Json;
use backend\models\ServiceModel;
use backend\models\ServiceModelSearch;
use backend\models\Ipv6PoolAssignment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\ServiceModelTemplate;
use backend\models\ServiceModelVariables;
use backend\models\ServiceModelTemplateVariables;
use common\models\User;
use common\components\PayLoadFrontend;

/**
 * ServiceModelController implements the CRUD actions for ServiceModel model.
 */
class ServiceModelController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ServiceModel models.
     * @return mixed
     * 
     *  $fetch = Ipv6PoolAssignment::getIpv6PoolAssignment(45, 1, 1, 125, [
     *               28 => "ipv6-wan-address",
     *               32 => "nei-ce-ip-ipv6"
     *           ]);
     *   echo "<pre>";
     *   print_r($fetch);
     *   die();
     */
    public function actionIndex() {

        try{

        $searchModel = new ServiceModelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Displays a single ServiceModel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        
        try{

        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
        $model = ServiceModel::find()->alias("sm")
                ->joinWith([
                    "service",
                    "subService",
                    "topology",
                    "serviceModelVariables",
                    "serviceModelVariables.variable"=>function($q){return $q->alias("v");},
                    "serviceModelTemplate","serviceModelTemplate.deviceRole",
                    "serviceModelTemplate","serviceModelTemplate.relatedDeviceRole"=>function($q){return $q->alias("rdr");},
                    "serviceModelTemplate.serviceModelTemplateVariables",
                    "serviceModelTemplate.serviceModelTemplateVariables.resourceManager"])
                ->where(["sm.id"=>$id])
              //  ->asArray()
                ->one();
                dpe($model);
        return $this->$renderMethod('view', [
                    'model' => $model,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }


    public function saveServiceModelVariables($model){

        if($model->id){ // While updating remove all vairables 
            ServiceModelVariables::deleteAll(["service_model_id"=>$model->id]);
        }

        $variables = array_merge($model->user_variables,$model->system_variables);
        $rows = [];
        foreach ($variables as $key => $resource_manager_id) {
            $rows[] = [
                    'service_model_id'=>$model->id,
                    'resource_manager_id'=>$resource_manager_id
            ];
        }
        $serviceModelVariables = new ServiceModelVariables();
        $result = $serviceModelVariables->batchInsert($rows);
        return $result;
    }
    /**
     * Creates a new ServiceModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new ServiceModel();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                //save service model data and get last inserted Id to give reference in service model template 
                if($model->save()){

                    //Save Device role in service template 
                    $serviceTemplateModel = new ServiceModelTemplate();
                    $rows = [];
                    foreach ($model->device_role_id as $device_role_id) {
                        $rows[] = [
                                'device_role_id' => $device_role_id,
                                'service_model_id' => $model->id,
                                'template_version' => 1,
                                'created_by'=>Yii::$app->user->id,
                            ];
                    }
                    $result = $serviceTemplateModel->batchInsert($rows);
                    if(!$result){
                         $transaction->rollBack();
                          return Json::encode([
                            "success"=>false,
                            "errors"=>"Error in saving device roles",
                        ]);
                    } 

                    // Save Service Model Variables
                    $result = $this->saveServiceModelVariables($model);
                    if($result){
                        $transaction->commit();
                        return Json::encode([
                            "success"=>true,
                            "data"=>[
                                    "service_model_id" => $model->id
                            ]
                        ]);
                    }else{
                        $transaction->rollBack();
                        return Json::encode([
                            "success"=>false,
                            "errors"=>"Errors in saving variables",
                        ]);
                    }

                }
            }catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
       
            return $this->redirect(['index']);
        }
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
           
        return $this->$renderMethod('create', [
                    'model' => $model,
                    'sub_service_id'=>[],
                    'topology_id'=>[]
       ]);
        
    }

    /**
     * Updates an existing ServiceModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        //Set Device Roel ids
        if($model->serviceModelTemplate){
            $deviceRoles = $model->getServiceModelTemplate()->asArray()->all();
            $model->device_role_id = array_column( $deviceRoles,'device_role_id');
        }
        //Set Syste/User defined Variables
        if($model->serviceModelVariables){
            $variables = $model->getServiceModelVariables()->asArray()->all();
            $variables = array_column($variables,'resource_manager_id');
            $model->system_variables = $variables;
             $model->user_variables = $variables;
        }
        $sub_service_id = [];
        if($model->subService){
            $sub_service_id = [$model->subService->id=>$model->subService->name];
        }
        if($model->topology){
            $topology_id = [$model->topology->id=>$model->topology->name];
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if($model->save()){

                $res = $this->saveServiceModelVariables($model);
                if($res){
                     return Json::encode([
                        "success"=>true,
                            "data"=>[
                                    "service_model_id" => $model->id
                            ]
                        ]);

                }else{
                     return Json::encode([
                        "success"=>false,
                        "errors"=>"Error while saving variables"
                        ]);  
                }

                
            }else{
                 return Json::encode([
                "success"=>false,
                "errors"=>$model->errors,
                ]); 
            }
           
            return $this->redirect(['index', 'id' => $model->id]);
        }
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
        return $this->$renderMethod('update', [
                    'model' => $model,
                    'sub_service_id'=>$sub_service_id,
                    'topology_id'=>$topology_id
        ]);
    }


    public function actionServiceTemplate(){

        $request= Yii::$app->request;
        //$template_id = $request->post("template_id");
        $service_model_id = $request->post("service_model_id");

        $model = new ServiceModelTemplate();
        $model->service_model_id = $service_model_id;
               
        if(!empty($request->post("ServiceModelTemplate"))){
            $post = $request->post("ServiceModelTemplate");
            $payload = '';
            $payload = isset($post["nso_payload"]) ? $post["nso_payload"] : '';
            
            if(!empty($payload)) {
                $all_variables = PayloadFrontend::findVariables($payload);
                $master_variables = isset($all_variables['master']) ? $all_variables['master'] : array();
                $master_variables = array_filter($master_variables);                
            }
            if(!empty($master_variables) && is_array($master_variables)) {
                $resourceManagerIdArray = [];
                $variable_names = "'" . implode ( "', '", $master_variables ) . "'";
              
                $sql = 'select id from tbl_variables_master where variable_name in ('.$variable_names.')';
                $resourceManagerIdArray = Yii::$app->db->createCommand($sql)->queryColumn();
                
            }
           
            $model = ServiceModelTemplate::find()
            ->where([
                "device_role_id"=>$post["device_role_id"],
                "service_model_id"=>$post["service_model_id"]
            ])
            ->one();

            if(isset($model->id) && !empty($model->id) && $model->load(Yii::$app->request->post())){
                $service_model_id = $model->service_model_id;
                if($model->save()){
                    if(!empty($model->resource_manager_id)){

                        // Save Service Model Template Variables 
                        ServiceModelTemplateVariables::deleteAll(["template_id"=>$model->id]);
                        $rows = [];
                        //$resourceManagerIdArray = explode(",", $model->resource_manager_id);
                       
                        foreach($resourceManagerIdArray as $id){
                             $reference_template_id = 0;
                            if(strpos($id,"_")!==false){
                                $idArr = explode("_",$id);
                                $reference_template_id = $idArr[0];
                                $id = $idArr[1];
                            }
                            $rows[] = [
                                    'template_id'=>$model->id,
                                    'resource_manager_id'=>$id,
                                    'reference_template_id'=> $reference_template_id,
                            ];
                        }
                        $res = Yii::$app->db->createCommand()
                        ->batchInsert(ServiceModelTemplateVariables::tableName(), ["template_id","resource_manager_id","reference_template_id"], $rows)
                        ->execute();
                        if($res){
                            return Json::encode([
                                "success"=>true,
                                "data" => [],
                            ]);
                        }
                    }
                    return $this->redirect(["service-model/index"]);
                }else{
                    pe($model->attributes,$model->errors);
                }
            }           
        }

       
       
        if($service_model_id){
            $serviceModel = ServiceModel::find()
                            ->where(["id"=>$service_model_id])
                            ->with(['serviceModelVariables','serviceModelVariables.variable'])
                            ->asArray()
                            ->one();
            $variables = [];
            foreach ($serviceModel["serviceModelVariables"] as $key => $value) {
                $v = $value["variable"];
                $variables[$v["type"]][$v["id"]] = $v["variable_name"];
            }
        }

        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";

        return $this->$renderMethod('template-form', [
                    'model'         => $model,
                    'serviceModel'  => $serviceModel,
                    'deviceRolesArray'=> ServiceModelTemplate::getTemplateDeviceRoleList($service_model_id),
                    'variables'     => $variables
                ]);
    }


    

    /**
     * Deletes an existing ServiceModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        // $this->findModel($id)->delete();
        try{

        $user = User::find()->where(['id' => Yii:: $app->user->id])->one();
        $model = $this->findModel($id);
        if ((trim($user['user_type']) == 'admin' || empty($user['user_type']))) {
            $model->is_deleted = 2;
            $model->delete_action_taken = Yii:: $app->user->id;
            $model->updated_on = date('Y-m-d H:i:s');
        } else {
            $model->is_deleted = 1;
            $model->delete_action_taken = Yii:: $app->user->id;
            $model->updated_on = date('Y-m-d H:i:s');
        }
        $model->save(false);
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Finds the ServiceModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ServiceModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = ServiceModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 
     * @param int $id
     * @param int $status
     * @return index
     * this function is update active_status field for accept/reject
     * in the time of insert 0->default,1->waiting for deletation,2->deleted
     */
    public function actionUpdatestatus($id, $status) {
        try{

        $model = $this->findModel($id);
        $model->is_deleted = $status;
        $model->delete_action_taken = Yii:: $app->user->id;
        $model->updated_on = date('Y-m-d H:i:s');
        $model->save(false);
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    public function actionGetServiceModelTemplate(){
        $device_role_id = Yii::$app->request->post("device_role_id");
        $service_model_id = Yii::$app->request->post("service_model_id");
        $data = [];
        if($device_role_id){
             $data = ServiceModelTemplate::find()
                ->joinWith(["deviceRole","serviceModelTemplateVariables","serviceModelTemplateVariables.resourceManager"])
                ->where(["device_role_id"=>$device_role_id,'service_model_id'=>$service_model_id])
                ->asArray()
                ->one();
             $resource_manager_id = "";
             if($data && isset($data["serviceModelTemplateVariables"])){
                 $templateVariables = $data["serviceModelTemplateVariables"];
                 $resource_manager_id = implode(",",array_column($templateVariables, 'resource_manager_id'));
                 $data["resourceManagerArray"] = array_column($templateVariables, 'resource_manager_id');

                 $variables = [];
                 foreach ($templateVariables as $key => $variable) {
                    $rm = $variable["resourceManager"];
                    $variables[$rm["type"]][] = [
                           "id"=>$rm["id"],
                           "name"=>$rm["variable_name"],
                    ];
                 }
                 $data["variables"] = $variables;
             }   
             $data["resource_manager_id"] = $resource_manager_id;
             $data["role_name"] = $data["deviceRole"]["role_name"];
             unset($data["serviceModelTemplateVariables"],$data["deviceRole"]);
            
        }
        return Json::encode($data);
    }


    public function actionGetRelatedDeviceRoles(){

        $params  = Yii::$app->request->post("depdrop_all_params");
        $device_role_id = $params["servicemodeltemplate-device_role_id"];
        $service_model_id = $params["servicemodeltemplate-service_model_id"];
        $roles = ServiceModelTemplate::getTemplateDeviceRoleList($service_model_id,$device_role_id);
        $ret = [];
        foreach($roles as $role_id=>$role){
            $ret[] = ["id"=>$role_id,'name'=>$role];
        }
        return Json::encode(["output"=>$ret]); 
    }
}
